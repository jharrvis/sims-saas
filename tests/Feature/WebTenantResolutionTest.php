<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantResolved;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\SetSpatieTeam;
use App\Models\Membership;
use App\Models\School;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\SessionGuard;
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
 * Kunci session guard web: login_web_<sha1(SessionGuard::class)>.
 * Mensimulasikan login web yang sesungguhnya — user hanya tercatat di
 * session (tidak di-set langsung ke guard seperti actingAs), sehingga
 * middleware harus me-resolve user dari session yang sudah dimulai.
 *
 * @return array<string, int>
 */
function sesiLoginWeb(User $user): array
{
    return ['login_web_'.sha1(SessionGuard::class) => $user->id];
}

it('tenant ter-resolve dari membership pada request web nyata', function (): void {
    [$school, $user] = siapkanSekolahWeb('webresolusi');

    // SENGAJA tidak memanggil TenantContext::set — biarkan middleware bekerja.
    $this->withSession(sesiLoginWeb($user))->get('/')->assertOk();

    expect(app(TenantContext::class)->schoolId())->toBe($school->id);
});

it('menu sekolah tampil sesuai permission setelah tenant ter-resolve', function (): void {
    [$school, $user] = siapkanSekolahWeb('webmenu');

    $this->withSession(sesiLoginWeb($user))
        ->get('/')
        ->assertOk()
        ->assertSee('Data Siswa')
        ->assertSee('Data Guru');
});

it('urutan middleware: resolusi tenant di grup web & api, bukan global', function (): void {
    // Regresi: sebagai global middleware, ResolveTenant berjalan sebelum
    // session dimulai sehingga $request->user() selalu null dan fallback
    // membership tidak pernah bekerja di domain pusat.
    $groups = app('router')->getMiddlewareGroups();

    expect($groups['web'])->toContain(ResolveTenant::class)
        ->and($groups['web'])->toContain(SetSpatieTeam::class)
        ->and($groups['api'])->toContain(EnsureTenantResolved::class);
});
