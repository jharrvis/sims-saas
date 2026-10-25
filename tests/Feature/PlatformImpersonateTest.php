<?php

declare(strict_types=1);

use App\Livewire\Platform\TenantShow;
use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\MfaSecret;
use App\Models\School;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * @param  array<int, string>  $without
 */
function opImpersonate(array $without = []): User
{
    $admin = User::create([
        'username' => 'opimp'.random_int(1000, 9999),
        'name' => 'Operator',
        'phone' => '62897'.random_int(1000000, 9999999),
        'is_platform' => true,
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId(0);
    $role = Role::findOrCreate('platform-admin', 'platform');
    $role->syncPermissions(
        Permission::where('guard_name', 'platform')->whereNotIn('name', $without)->get()
    );
    $admin->assignRole($role);

    MfaSecret::create([
        'user_id' => $admin->id, 'secret' => 'TESTSECRET',
        'confirmed_at' => now(), 'recovery_codes' => [],
    ]);

    return $admin;
}

/**
 * @return array{0:School, 1:User}
 */
function sekolahDenganAdmin(string $nama): array
{
    $school = School::create([
        'nama' => $nama,
        'slug' => Str::slug($nama).'-'.uniqid(),
        'jenjang' => 'SD',
        'status' => 'aktif',
        'status_langganan' => 'trialing',
    ]);

    $admin = User::create([
        'username' => 'adm'.random_int(1000, 9999),
        'name' => 'Admin '.$nama,
        'phone' => '62896'.random_int(1000000, 9999999),
    ]);

    Membership::create(['user_id' => $admin->id, 'school_id' => $school->id, 'status' => 'aktif']);

    return [$school, $admin];
}

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed(PermissionSeeder::class);
});

it('operator berizin bisa impersonate admin sekolah', function (): void {
    $operator = opImpersonate();
    [$school, $admin] = sekolahDenganAdmin('SDN Impersonate');

    RoleSeeder::seedForSchool($school->id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
    $admin->assignRole('superadmin');
    app(PermissionRegistrar::class)->setPermissionsTeamId(0);

    Livewire::actingAs($operator)
        ->test(TenantShow::class, ['schoolId' => $school->id])
        ->call('impersonate')
        ->assertRedirect(route('dashboard'));

    expect(auth()->id())->toBe($admin->id)
        ->and(session('impersonator_id'))->toBe($operator->id)
        ->and(AuditLog::where('aksi', 'impersonate.start')->where('school_id', $school->id)->exists())->toBeTrue();
});

it('operator tanpa izin impersonate ditolak', function (): void {
    $operator = opImpersonate(['platform.tenant.impersonate']);
    [$school] = sekolahDenganAdmin('SDN Tolak');

    Livewire::actingAs($operator)
        ->test(TenantShow::class, ['schoolId' => $school->id])
        ->call('impersonate')
        ->assertForbidden();
});

it('sekolah tanpa admin tidak bisa di-impersonate', function (): void {
    $operator = opImpersonate();
    $school = School::create([
        'nama' => 'SDN Kosong', 'slug' => 'sdn-kosong-'.uniqid(), 'jenjang' => 'SD',
        'status' => 'aktif', 'status_langganan' => 'trialing',
    ]);

    Livewire::actingAs($operator)
        ->test(TenantShow::class, ['schoolId' => $school->id])
        ->call('impersonate')
        ->assertNotFound();
});

it('operator peran ganda bisa impersonate dirinya sebagai admin sekolah', function (): void {
    $operator = opImpersonate();
    $school = School::create([
        'nama' => 'SDN Dual Imp', 'slug' => 'sdn-dual-imp-'.uniqid(), 'jenjang' => 'SD',
        'status' => 'aktif', 'status_langganan' => 'trialing',
    ]);
    Membership::create(['user_id' => $operator->id, 'school_id' => $school->id, 'status' => 'aktif']);

    RoleSeeder::seedForSchool($school->id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
    $operator->assignRole('superadmin');
    app(PermissionRegistrar::class)->setPermissionsTeamId(0);

    Livewire::actingAs($operator)
        ->test(TenantShow::class, ['schoolId' => $school->id])
        ->call('impersonate')
        ->assertRedirect(route('dashboard'));

    expect(auth()->id())->toBe($operator->id)
        ->and(session('impersonator_id'))->toBe($operator->id);
});

it('berhenti impersonate kembali menjadi operator', function (): void {
    $operator = opImpersonate();
    [$school, $admin] = sekolahDenganAdmin('SDN Stop');

    $this->actingAs($admin)
        ->withSession(['impersonator_id' => $operator->id, 'impersonate_school_id' => $school->id])
        ->post(route('platform.impersonate.stop'))
        ->assertRedirect(route('platform.tenants.show', $school->id));

    expect(auth()->id())->toBe($operator->id)
        ->and(session()->has('impersonator_id'))->toBeFalse()
        ->and(AuditLog::where('aksi', 'impersonate.stop')->exists())->toBeTrue();
});

it('stop tanpa sesi impersonate ditolak', function (): void {
    $operator = opImpersonate();

    $this->actingAs($operator)
        ->post(route('platform.impersonate.stop'))
        ->assertForbidden();
});

it('operator tanpa membership impersonate admin meski ada member non-admin ber-id lebih kecil', function (): void {
    $operator = opImpersonate();
    expect($operator->memberships()->count())->toBe(0);

    $school = School::create([
        'nama' => 'SDN Gap Target', 'slug' => 'sdn-gap-target-'.uniqid(),
        'jenjang' => 'SD', 'status' => 'aktif', 'status_langganan' => 'trialing',
    ]);

    $staf = User::create([
        'username' => 'staf'.random_int(1000, 9999), 'name' => 'Staf Biasa',
        'phone' => '62895'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $staf->id, 'school_id' => $school->id, 'status' => 'aktif']);

    $admin = User::create([
        'username' => 'adm'.random_int(1000, 9999), 'name' => 'Admin Sekolah',
        'phone' => '62896'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $admin->id, 'school_id' => $school->id, 'status' => 'aktif']);

    RoleSeeder::seedForSchool($school->id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
    $admin->assignRole('superadmin');
    app(PermissionRegistrar::class)->setPermissionsTeamId(0);

    Livewire::actingAs($operator)
        ->test(TenantShow::class, ['schoolId' => $school->id])
        ->call('impersonate')
        ->assertRedirect(route('dashboard'));

    expect(auth()->id())->toBe($admin->id);
});

it('sekolah hanya berisi staf non-admin tidak bisa di-impersonate (perbaikan QA P1)', function (): void {
    $operator = opImpersonate();
    $school = School::create([
        'nama' => 'SDN Staf Saja', 'slug' => 'sdn-staf-saja-'.uniqid(),
        'jenjang' => 'SD', 'status' => 'aktif', 'status_langganan' => 'trialing',
    ]);
    $guru = User::create([
        'username' => 'guru'.random_int(1000, 9999), 'name' => 'Guru Mapel',
        'phone' => '62895'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $guru->id, 'school_id' => $school->id, 'status' => 'aktif']);

    RoleSeeder::seedForSchool($school->id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
    $guru->assignRole('guru-mapel');
    app(PermissionRegistrar::class)->setPermissionsTeamId(0);

    Livewire::actingAs($operator)
        ->test(TenantShow::class, ['schoolId' => $school->id])
        ->call('impersonate')
        ->assertNotFound();
});
