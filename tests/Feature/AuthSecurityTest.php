<?php

declare(strict_types=1);

use App\Models\MfaSecret;
use App\Models\User;

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

it('user platform tanpa MFA dipaksa ke halaman setup 2FA', function (): void {
    $user = buatUserKeamanan('platform1', true);

    $this->actingAs($user)->get('/')->assertRedirect(route('2fa.setup'));
    $this->actingAs($user)->get('/siswa')->assertRedirect(route('2fa.setup'));

    // Halaman setup sendiri tidak redirect loop.
    $this->actingAs($user)->get('/2fa/setup')->assertOk();
});

it('user platform dengan MFA aktif tetapi sesi belum terverifikasi dipaksa ke mfa.verify', function (): void {
    $user = buatUserKeamanan('platform2', true);
    konfirmasiMfa($user);

    // Sesi baru tanpa flag mfa_verified (simulasi login via remember-me).
    $this->actingAs($user)->get('/')->assertRedirect(route('mfa.verify'));
    $this->actingAs($user)->get('/siswa')->assertRedirect(route('mfa.verify'));

    // Halaman verifikasi bisa dibuka.
    $this->actingAs($user)->get('/mfa/verify')->assertOk();
});

it('user platform dengan sesi MFA terverifikasi diarahkan ke panel platform', function (): void {
    $user = buatUserKeamanan('platform3', true);
    konfirmasiMfa($user);

    $this->actingAs($user)
        ->withSession(['mfa_verified_'.$user->id => true])
        ->get('/')
        ->assertRedirect(route('platform.dashboard'));
});

it('user biasa tidak pernah dipaksa MFA', function (): void {
    $user = buatUserKeamanan('biasa2', false);

    $this->actingAs($user)->get('/')->assertOk();
    $this->actingAs($user)->get('/2fa/setup')->assertOk();
});
