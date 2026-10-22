<?php

declare(strict_types=1);

use App\Livewire\Pengaturan\PeranForm;
use App\Livewire\Pengaturan\PeranIndex;
use App\Models\Membership;
use App\Models\School;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * @return array{0: School, 1: int}
 */
function siapkanSekolahPeran(string $slug): array
{
    $school = School::create([
        'nama' => 'Sekolah '.$slug, 'slug' => $slug, 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($school->id);

    app(TenantContext::class)->set($school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

    return [$school, $school->id];
}

function buatPenggunaPeranSekolah(string $username, string $role, School $school): User
{
    $user = User::create([
        'username' => $username,
        'name' => 'User '.$username,
        'phone' => '628'.random_int(1000000000, 1999999999),
    ]);

    Membership::create([
        'user_id' => $user->id,
        'school_id' => $school->id,
        'status' => 'aktif',
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
    $user->assignRole($role);

    return $user;
}

beforeEach(function (): void {
    [$this->school] = siapkanSekolahPeran('rm-'.uniqid());

    $this->admin = buatPenggunaPeranSekolah('adm-'.uniqid(), 'admin-sekolah', $this->school);
    $this->guru = buatPenggunaPeranSekolah('guru-'.uniqid(), 'guru-mapel', $this->school);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('admin dapat membuat peran kustom dengan hak akses terpilih', function (): void {
    Livewire::actingAs($this->admin)->test(PeranForm::class)
        ->set('name', 'operator-ppdb')
        ->set('permissions', ['ppdb.periode.view', 'ppdb.pendaftar.view', 'ppdb.pendaftar.verify'])
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertRedirect(route('pengaturan.peran'));

    /** @var Role|null $role */
    $role = Role::where('name', 'operator-ppdb')
        ->where('team_id', $this->school->id)
        ->where('guard_name', 'web')
        ->first();

    expect($role)->not->toBeNull()
        ->and($role->permissions->pluck('name')->all())->toContain('ppdb.pendaftar.verify')
        ->and($role->permissions)->toHaveCount(3);
});

it('peran kustom hanya dibuat pada tim sekolah aktif', function (): void {
    Livewire::actingAs($this->admin)->test(PeranForm::class)
        ->set('name', 'operator-khusus')
        ->set('permissions', ['siswa.view'])
        ->call('simpan')
        ->assertHasNoErrors();

    [$schoolB] = siapkanSekolahPeran('rm-b-'.uniqid());

    expect(Role::where('name', 'operator-khusus')->where('team_id', $this->school->id)->exists())->toBeTrue()
        ->and(Role::where('name', 'operator-khusus')->where('team_id', $schoolB->id)->exists())->toBeFalse();
});

it('admin dapat mengubah hak akses peran bawaan (selain superadmin)', function (): void {
    /** @var Role $role */
    $role = Role::where('name', 'guru-mapel')
        ->where('team_id', $this->school->id)
        ->firstOrFail();

    $jumlahAwal = $role->permissions()->count();

    Livewire::actingAs($this->admin)->test(PeranForm::class, ['role' => $role])
        ->assertSet('sistem', true)
        ->set('permissions', ['siswa.view', 'nilai.input'])
        ->call('simpan')
        ->assertHasNoErrors();

    $role->refresh();

    expect($role->permissions()->count())->toBe(2)
        ->and($role->permissions()->count())->not->toBe($jumlahAwal);
});

it('peran superadmin tidak dapat diubah lewat UI', function (): void {
    /** @var Role $superadmin */
    $superadmin = Role::where('name', 'superadmin')
        ->where('team_id', $this->school->id)
        ->firstOrFail();

    $jumlahAwal = $superadmin->permissions()->count();

    Livewire::actingAs($this->admin)->test(PeranForm::class, ['role' => $superadmin])
        ->assertSet('sistem', true)
        ->assertSee('bersifat tetap');

    // Simpan ditolak (403) — hak akses superadmin tidak berubah.
    Livewire::actingAs($this->admin)->test(PeranForm::class, ['role' => $superadmin])
        ->set('permissions', ['dashboard.view'])
        ->call('simpan');

    expect($superadmin->permissions()->count())->toBe($jumlahAwal);
});

it('peran bawaan tidak dapat dihapus', function (): void {
    /** @var Role $role */
    $role = Role::where('name', 'tata-usaha')
        ->where('team_id', $this->school->id)
        ->firstOrFail();

    Livewire::actingAs($this->admin)->test(PeranIndex::class)
        ->call('hapus', $role->id)
        ->assertSet('error', 'Peran bawaan sistem tidak dapat dihapus.');

    expect(Role::find($role->id))->not->toBeNull();
});

it('peran yang masih dipakai pengguna tidak dapat dihapus', function (): void {
    /** @var Role $role */
    $role = Role::create([
        'name' => 'operator-sementara',
        'guard_name' => 'web',
        'team_id' => $this->school->id,
    ]);
    $role->syncPermissions(['siswa.view']);

    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
    $this->guru->assignRole('operator-sementara');

    Livewire::actingAs($this->admin)->test(PeranIndex::class)
        ->call('hapus', $role->id)
        ->assertSet('error', fn ($error) => is_string($error) && str_contains($error, 'masih dipakai'));

    expect(Role::find($role->id))->not->toBeNull();
});

it('peran kustom yang tidak dipakai dapat dihapus', function (): void {
    /** @var Role $role */
    $role = Role::create([
        'name' => 'operator-lama',
        'guard_name' => 'web',
        'team_id' => $this->school->id,
    ]);

    Livewire::actingAs($this->admin)->test(PeranIndex::class)
        ->call('hapus', $role->id)
        ->assertSet('error', null);

    expect(Role::find($role->id))->toBeNull();
});

it('guru mapel tidak boleh membuka kelola peran', function (): void {
    Livewire::actingAs($this->guru)->test(PeranIndex::class)->assertForbidden();
    Livewire::actingAs($this->guru)->test(PeranForm::class)->assertForbidden();
});

it('admin tidak dapat mengubah peran dari sekolah lain', function (): void {
    [$schoolB] = siapkanSekolahPeran('rm-c-'.uniqid());

    /** @var Role $roleB */
    $roleB = Role::where('name', 'guru-mapel')
        ->where('team_id', $schoolB->id)
        ->firstOrFail();

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->actingAs($this->admin)
        ->get(route('pengaturan.peran.edit', $roleB))
        ->assertNotFound();
});

it('nama peran kustom harus unik dalam satu sekolah', function (): void {
    Role::create([
        'name' => 'operator-unik',
        'guard_name' => 'web',
        'team_id' => $this->school->id,
    ]);

    Livewire::actingAs($this->admin)->test(PeranForm::class)
        ->set('name', 'operator-unik')
        ->set('permissions', [])
        ->call('simpan')
        ->assertHasErrors('name');
});
