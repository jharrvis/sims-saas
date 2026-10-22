<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Livewire\Pengaturan\Keamanan;
use App\Livewire\Pengaturan\PasswordSetup;
use App\Models\MfaSecret;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->withoutVite();
});

function buatUserKeamanan(string $username, bool $isPlatform): User
{
    return User::create([
        'username' => $username,
        'name' => $username,
        'phone' => '62899'.random_int(1000000, 9999999),
        'is_platform' => $isPlatform,
    ]);
}

function konfirmasiMfa(User $user): void
{
    MfaSecret::create([
        'user_id' => $user->id,
        'secret' => 'TESTSECRET',
        'confirmed_at' => now(),
        'recovery_codes' => [],
    ]);
}

it('tamu tidak bisa membuka dashboard', function (): void {
    $this->get('/')->assertRedirect('/login');
});

it('tamu tidak bisa membuka pilih-sekolah', function (): void {
    $this->get('/pilih-sekolah')->assertRedirect('/login');
});

it('user login bisa membuka pilih-sekolah dan dashboard', function (): void {
    $user = buatUserKeamanan('biasa1', false);

    $this->actingAs($user)->get('/pilih-sekolah')->assertOk();
    $this->actingAs($user)->get('/')->assertOk();
});

it('2FA nonaktif secara default — operator platform tidak dipaksa setup', function (): void {
    $user = buatUserKeamanan('platform1', true);

    // Tanpa 2FA & tanpa flag → langsung ke panel platform, bukan dipaksa setup.
    $this->actingAs($user)->get('/')->assertRedirect(route('platform.dashboard'));
});

it('operator platform dipaksa setup 2FA bila MFA_REQUIRED_FOR_PLATFORM aktif', function (): void {
    config(['mfa.require_for_platform' => true]);

    $user = buatUserKeamanan('platformflag', true);

    $this->actingAs($user)->get('/')->assertRedirect(route('2fa.setup'));

    // Halaman setup sendiri tidak memicu redirect berulang.
    $this->actingAs($user)->get('/2fa/setup')->assertOk();
});

it('user dengan 2FA aktif tetapi sesi belum terverifikasi dipaksa ke mfa.verify', function (): void {
    $user = buatUserKeamanan('platform2', true);
    konfirmasiMfa($user);

    // Sesi baru tanpa flag mfa_verified (simulasi login via remember-me).
    $this->actingAs($user)->get('/siswa')->assertRedirect(route('mfa.verify'));

    $this->actingAs($user)->get('/mfa/verify')->assertOk();
});

it('user biasa yang mengaktifkan 2FA juga wajib verifikasi', function (): void {
    $user = buatUserKeamanan('biasa-2fa', false);
    konfirmasiMfa($user);

    $this->actingAs($user)->get('/')->assertRedirect(route('mfa.verify'));
});

it('user biasa tanpa 2FA tidak pernah dipaksa MFA', function (): void {
    $user = buatUserKeamanan('biasa2', false);

    $this->actingAs($user)->get('/')->assertOk();
    $this->actingAs($user)->get('/2fa/setup')->assertOk();
});

it('halaman keamanan akun dapat dibuka oleh pengguna login', function (): void {
    $user = buatUserKeamanan('biasa3', false);

    $this->actingAs($user)->get('/pengaturan/keamanan')->assertOk();
});

it('menonaktifkan 2FA via OTP WhatsApp menghapus secret', function (): void {
    $user = buatUserKeamanan('optout1', false);
    konfirmasiMfa($user);

    OtpCode::create([
        'phone' => $user->phone,
        'code_hash' => Hash::make('123456'),
        'keperluan' => 'mfa_disable',
        'expires_at' => now()->addMinutes(5),
    ]);

    Livewire::actingAs($user)
        ->test(Keamanan::class)
        ->set('disableStep', 2)
        ->set('otpCode', '123456')
        ->call('nonaktifkan')
        ->assertHasNoErrors()
        ->assertSet('disableStep', 0)
        ->assertSee('Aktifkan 2FA');

    expect($user->fresh()->hasMfaEnabled())->toBeFalse();
    $this->assertDatabaseMissing('mfa_secrets', ['user_id' => $user->id]);
});

it('menolak penonaktifan 2FA dengan OTP salah', function (): void {
    $user = buatUserKeamanan('optout2', false);
    konfirmasiMfa($user);

    OtpCode::create([
        'phone' => $user->phone,
        'code_hash' => Hash::make('123456'),
        'keperluan' => 'mfa_disable',
        'expires_at' => now()->addMinutes(5),
    ]);

    Livewire::actingAs($user)
        ->test(Keamanan::class)
        ->set('disableStep', 2)
        ->set('otpCode', '000000')
        ->call('nonaktifkan');

    expect($user->fresh()->hasMfaEnabled())->toBeTrue();
});

it('login username + password berhasil (cadangan D-3)', function (): void {
    $user = User::create([
        'username' => 'login-pw-1',
        'name' => 'Login Password',
        'phone' => '62899'.random_int(1000000, 9999999),
        'password' => 'rahasia123',
        'is_platform' => false,
    ]);

    Livewire::test(Login::class)
        ->set('tab', 'password')
        ->set('username', 'login-pw-1')
        ->set('password', 'rahasia123')
        ->call('loginPassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('login password salah ditolak', function (): void {
    User::create([
        'username' => 'login-pw-2',
        'name' => 'Login Password Salah',
        'phone' => '62899'.random_int(1000000, 9999999),
        'password' => 'rahasia123',
        'is_platform' => false,
    ]);

    Livewire::test(Login::class)
        ->set('tab', 'password')
        ->set('username', 'login-pw-2')
        ->set('password', 'salah-sekali')
        ->call('loginPassword')
        ->assertSet('error', 'Username atau password salah.');

    $this->assertGuest();
});

it('login password menghormati 2FA opt-in', function (): void {
    $user = User::create([
        'username' => 'login-pw-3',
        'name' => 'Login Password 2FA',
        'phone' => '62899'.random_int(1000000, 9999999),
        'password' => 'rahasia123',
        'is_platform' => false,
    ]);
    konfirmasiMfa($user);

    Livewire::test(Login::class)
        ->set('tab', 'password')
        ->set('username', 'login-pw-3')
        ->set('password', 'rahasia123')
        ->call('loginPassword')
        ->assertRedirect(route('mfa.verify'));

    expect(session('mfa_pending_user_id'))->toBe($user->id);
    $this->assertGuest();
});

it('pengguna dapat mengatur password sendiri dari halaman keamanan', function (): void {
    $user = buatUserKeamanan('pw-sendiri', false);

    Livewire::actingAs($user)
        ->test(PasswordSetup::class)
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertSee('Password berhasil disimpan');

    expect(Hash::check('rahasia123', (string) $user->fresh()->password))->toBeTrue();
});

it('password akun baru minimal 8 karakter dan harus dikonfirmasi', function (): void {
    $user = buatUserKeamanan('pw-pendek', false);

    Livewire::actingAs($user)
        ->test(PasswordSetup::class)
        ->set('password', '123')
        ->set('password_confirmation', '123')
        ->call('simpan')
        ->assertHasErrors('password');

    expect($user->fresh()->password)->toBeNull();
});
