<?php

declare(strict_types=1);

use App\Models\Membership;
use App\Models\School;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\SessionGuard;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;

/**
 * @return array{0:School, 1:User}
 */
function siapkanSekolahWeb(string $slug): array
{
    $school = School::create([
        'nama' => 'Sekolah '.$slug, 'slug' => $slug, 'jenjang' => 'SD',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($school->id);

    $user = User::create([
        'username' => 'web'.$slug, 'name' => 'User '.$slug,
        'phone' => '628'.random_int(1000000000, 1999999999),
    ]);
    Membership::create(['user_id' => $user->id, 'school_id' => $school->id, 'status' => 'aktif']);

    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
    $user->assignRole('superadmin');
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);

    return [$school, $user];
}

beforeEach(function (): void {
    $this->withoutVite();
});

/**
 * Simulasi login web yang sesungguhnya: user hanya tercatat di session
 * (tidak di-set langsung ke guard seperti actingAs), sehingga middleware
 * harus me-resolve user dari session yang sudah dimulai.
 *
 * @return array{0:TestResponse, 1:School}
 */
function getSebagaiUserLewatSession(User $user): mixed
{
    $key = 'login_web_'.sha1(SessionGuard::class);

    return test()->withSession([$key => $user->id])->get('/');
}

it('tenant ter-resolve dari membership pada request web nyata', function (): void {
    [$school, $user] = siapkanSekolahWeb('webresolusi');

    // SENGAJA tidak memanggil TenantContext::set — biarkan middleware bekerja.
    getSebagaiUserLewatSession($user)->assertOk();

    expect(app(TenantContext::class)->schoolId())->toBe($school->id);
});

it('menu sekolah tampil sesuai permission setelah tenant ter-resolve', function (): void {
    [$school, $user] = siapkanSekolahWeb('webmenu');

    getSebagaiUserLewatSession($user)
        ->assertOk()
        ->assertSee('Data Siswa')
        ->assertSee('Data Guru');
});
