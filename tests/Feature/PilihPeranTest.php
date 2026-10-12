<?php

declare(strict_types=1);

use App\Livewire\Auth\PilihPeran;
use App\Models\Membership;
use App\Models\MfaSecret;
use App\Models\School;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * @return array{0:User, 1:School}
 */
function buatDualRole(string $username): array
{
    $user = User::create([
        'username' => $username,
        'name' => $username,
        'phone' => '62899'.random_int(1000000, 9999999),
        'is_platform' => true,
    ]);

    MfaSecret::create([
        'user_id' => $user->id, 'secret' => 'TESTSECRET',
        'confirmed_at' => now(), 'recovery_codes' => [],
    ]);

    $school = School::create([
        'nama' => 'SDN Dual '.$username,
        'slug' => Str::slug($username).'-'.uniqid(),
        'jenjang' => 'SD',
        'status' => 'aktif',
        'status_langganan' => 'trialing',
    ]);

    Membership::create(['user_id' => $user->id, 'school_id' => $school->id, 'status' => 'aktif']);

    return [$user, $school];
}

/**
 * @return array<string, bool>
 */
function sesiMfa(User $user): array
{
    return ['mfa_verified_'.$user->id => true];
}

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed(PermissionSeeder::class);
});

it('ruteSetelahLogin: peran ganda ke pilih-peran, murni ke tujuannya', function (): void {
    [$dual] = buatDualRole('dual1');
    $murniPlatform = User::create([
        'username' => 'murniplat', 'name' => 'Murni', 'phone' => '628990001111', 'is_platform' => true,
    ]);
    $murniSekolah = User::create([
        'username' => 'murnisek', 'name' => 'Murni', 'phone' => '628990002222', 'is_platform' => false,
    ]);

    expect($dual->ruteSetelahLogin())->toBe('pilih-peran')
        ->and($murniPlatform->ruteSetelahLogin())->toBe('platform.dashboard')
        ->and($murniSekolah->ruteSetelahLogin())->toBe('dashboard');
});

it('akun peran ganda melihat layar pilih peran', function (): void {
    [$user, $school] = buatDualRole('dual2');

    $this->actingAs($user)
        ->withSession(sesiMfa($user))
        ->get(route('pilih-peran'))
        ->assertOk()
        ->assertSee('Operator Platform')
        ->assertSee($school->nama);
});

it('akun murni dialihkan dari pilih-peran', function (): void {
    $murniPlatform = User::create([
        'username' => 'murniplat2', 'name' => 'Murni', 'phone' => '628990003333', 'is_platform' => true,
    ]);
    MfaSecret::create([
        'user_id' => $murniPlatform->id, 'secret' => 'TESTSECRET',
        'confirmed_at' => now(), 'recovery_codes' => [],
    ]);

    $this->actingAs($murniPlatform)
        ->withSession(sesiMfa($murniPlatform))
        ->get(route('pilih-peran'))
        ->assertRedirect(route('platform.dashboard'));
});

it('keSekolah mengatur tenant lalu ke dashboard', function (): void {
    [$user, $school] = buatDualRole('dual3');

    Livewire::actingAs($user)
        ->test(PilihPeran::class)
        ->call('keSekolah', $school->id)
        ->assertRedirect(route('dashboard'));

    expect(session('active_school_id'))->toBe($school->id);
});

it('keSekolah menolak sekolah yang bukan anggotanya', function (): void {
    [$user] = buatDualRole('dual4');
    $asing = School::create([
        'nama' => 'SDN Asing', 'slug' => 'sdn-asing-'.uniqid(), 'jenjang' => 'SD',
        'status' => 'aktif', 'status_langganan' => 'trialing',
    ]);

    Livewire::actingAs($user)
        ->test(PilihPeran::class)
        ->call('keSekolah', $asing->id)
        ->assertRedirect(route('pilih-peran'));
});
