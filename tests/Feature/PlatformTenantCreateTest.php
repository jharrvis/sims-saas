<?php

declare(strict_types=1);

use App\Livewire\Platform\TenantCreate;
use App\Models\MfaSecret;
use App\Models\School;
use App\Models\Subscription;
use App\Models\TenantModule;
use App\Models\User;
use App\Scopes\SchoolScope;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * @param  array<int, string>  $without
 */
function opPlatformBaru(array $without = []): User
{
    $admin = User::create([
        'username' => 'opbaru'.random_int(1000, 9999),
        'name' => 'Operator',
        'phone' => '62898'.random_int(1000000, 9999999),
        'is_platform' => true,
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId(0);
    $role = Role::findOrCreate('platform-admin', 'platform');
    $role->syncPermissions(
        Permission::where('guard_name', 'platform')->whereNotIn('name', $without)->get()
    );
    $admin->assignRole($role);

    MfaSecret::create([
        'user_id' => $admin->id,
        'secret' => 'TESTSECRET',
        'confirmed_at' => now(),
        'recovery_codes' => [],
    ]);

    return $admin;
}

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed(PermissionSeeder::class);
    $this->seed(PlanSeeder::class);
    $this->seed(ModuleSeeder::class);
});

it('operator dengan izin bisa membuka form tambah tenant', function (): void {
    $admin = opPlatformBaru();

    $this->actingAs($admin)
        ->withSession(['mfa_verified_'.$admin->id => true])
        ->get(route('platform.tenants.create'))
        ->assertOk()
        ->assertSee('Tambah Tenant');
});

it('operator tanpa izin tambah ditolak', function (): void {
    $admin = opPlatformBaru(['platform.tenant.create']);

    $this->actingAs($admin)
        ->withSession(['mfa_verified_'.$admin->id => true])
        ->get(route('platform.tenants.create'))
        ->assertForbidden();
});

it('membuat tenant lengkap: sekolah, trial, admin, modul inti', function (): void {
    $admin = opPlatformBaru();

    Livewire::actingAs($admin)
        ->test(TenantCreate::class)
        ->set('nama', 'SDN Manual')
        ->set('jenjang', 'SD')
        ->set('npsn', '20109988')
        ->set('admin_nama', 'Budi Admin')
        ->set('admin_phone', '628121119999')
        ->set('plan_slug', 'basic')
        ->call('simpan')
        ->assertRedirect(route('platform.tenants.show', School::withoutGlobalScope(SchoolScope::class)->where('npsn', '20109988')->firstOrFail()->id));

    $school = School::withoutGlobalScope(SchoolScope::class)->where('npsn', '20109988')->firstOrFail();

    expect($school->status_langganan)->toBe('trialing')
        ->and(TenantModule::withoutGlobalScope(SchoolScope::class)->where('school_id', $school->id)->where('aktif', true)->count())->toBeGreaterThan(0);

    $adminSekolah = User::where('phone', '628121119999')->firstOrFail();
    expect($adminSekolah->memberships()->withoutGlobalScope(SchoolScope::class)->where('school_id', $school->id)->exists())->toBeTrue();
    expect(Subscription::withoutGlobalScope(SchoolScope::class)->where('school_id', $school->id)->where('status', 'trial')->exists())->toBeTrue();
});

it('nomor WA admin yang sudah terdaftar tetap bisa dipakai', function (): void {
    $admin = opPlatformBaru();
    $eksis = User::create([
        'username' => 'guru'.random_int(1000, 9999),
        'name' => 'Guru Lama',
        'phone' => '628130009999',
    ]);

    Livewire::actingAs($admin)
        ->test(TenantCreate::class)
        ->set('nama', 'SMP Manual')
        ->set('jenjang', 'SMP')
        ->set('admin_nama', 'Guru Lama')
        ->set('admin_phone', '628130009999')
        ->set('plan_slug', 'basic')
        ->call('simpan')
        ->assertHasNoErrors();

    // Tidak ada user ganda; hanya tambah membership.
    expect(User::where('phone', '628130009999')->count())->toBe(1);
    $school = School::withoutGlobalScope(SchoolScope::class)->where('nama', 'SMP Manual')->firstOrFail();
    expect($eksis->memberships()->where('school_id', $school->id)->exists())->toBeTrue();
});

it('validasi menolak nomor WA berformat salah', function (): void {
    $admin = opPlatformBaru();

    Livewire::actingAs($admin)
        ->test(TenantCreate::class)
        ->set('nama', 'SDN Salah')
        ->set('jenjang', 'SD')
        ->set('admin_nama', 'X')
        ->set('admin_phone', '0812-salah')
        ->set('plan_slug', 'basic')
        ->call('simpan')
        ->assertHasErrors(['admin_phone']);
});
