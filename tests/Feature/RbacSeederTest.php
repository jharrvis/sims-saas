<?php

declare(strict_types=1);

use App\Models\School;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function buatSekolahRbac(string $slug): School
{
    return School::create([
        'nama' => 'Sekolah '.$slug, 'slug' => $slug, 'jenjang' => 'SD',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
}

it('seeder membuat katalog permission sesuai Dok 05', function (): void {
    $this->seed(PermissionSeeder::class);

    expect(Permission::where('guard_name', 'web')->count())->toBeGreaterThanOrEqual(40)
        ->and(Permission::where('name', 'siswa.view')->where('guard_name', 'web')->exists())->toBeTrue()
        ->and(Permission::where('name', 'platform.tenant.suspend')->where('guard_name', 'platform')->exists())->toBeTrue();
});

it('role admin-sekolah mendapat permission sesuai matriks, guru tidak', function (): void {
    $this->seed(PermissionSeeder::class);
    $school = buatSekolahRbac('rbac-1');
    RoleSeeder::seedForSchool($school->id);

    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

    $admin = Role::findByName('admin-sekolah', 'web');
    $guru = Role::findByName('guru-mapel', 'web');

    expect($admin->hasPermissionTo('siswa.create'))->toBeTrue()
        ->and($admin->hasPermissionTo('siswa.delete'))->toBeTrue()
        ->and($guru->hasPermissionTo('siswa.view'))->toBeTrue()
        ->and($guru->hasPermissionTo('siswa.create'))->toBeFalse()
        ->and($guru->hasPermissionTo('siswa.delete'))->toBeFalse();
});

it('role dibuat per tenant (teams terisolasi)', function (): void {
    $this->seed(PermissionSeeder::class);
    $a = buatSekolahRbac('rbac-a');
    $b = buatSekolahRbac('rbac-b');

    RoleSeeder::seedForSchool($a->id);

    app(PermissionRegistrar::class)->setPermissionsTeamId($b->id);

    // Di tenant B yang belum di-seed, role admin-sekolah tidak ada (cek team_id eksplisit —
    // Spatie teams memfilter via team_id pada permission check, bukan global scope)
    expect(Role::where('name', 'admin-sekolah')->where('guard_name', 'web')->where('team_id', $b->id)->exists())->toBeFalse();
    expect(Role::where('name', 'admin-sekolah')->where('guard_name', 'web')->where('team_id', $a->id)->exists())->toBeTrue();

    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});
