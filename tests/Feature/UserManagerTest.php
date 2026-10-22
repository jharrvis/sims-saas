<?php

declare(strict_types=1);

use App\Livewire\Pengaturan\PenggunaForm;
use App\Livewire\Pengaturan\PenggunaIndex;
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
function siapkanSekolahPengguna(string $slug): array
{
    $school = School::create([
        'nama' => 'Sekolah '.$slug, 'slug' => $slug, 'jenjang' => 'SD',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($school->id);

    app(TenantContext::class)->set($school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

    return [$school, $school->id];
}

function buatPenggunaPeran(string $username, string $role, School $school): User
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
    [$this->school] = siapkanSekolahPengguna('um-'.uniqid());

    $this->admin = buatPenggunaPeran('adm-'.uniqid(), 'admin-sekolah', $this->school);
    $this->guru = buatPenggunaPeran('guru-'.uniqid(), 'guru-mapel', $this->school);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('admin sekolah bisa membuka halaman kelola pengguna & peran', function (): void {
    $this->actingAs($this->admin)->get(route('pengaturan.pengguna'))->assertOk();
    $this->actingAs($this->admin)->get(route('pengaturan.peran'))->assertOk();
});

it('admin sekolah dapat membuat pengguna baru beserta peran dan membership', function (): void {
    Livewire::actingAs($this->admin)->test(PenggunaForm::class)
        ->set('name', 'Budi Santoso')
        ->set('username', 'budi')
        ->set('phone', '081234567890')
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->set('roles', ['guru-mapel'])
        ->set('status', 'aktif')
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertRedirect(route('pengaturan.pengguna'));

    $user = User::where('username', 'budi')->first();

    expect($user)->not->toBeNull()
        ->and($user->phone)->toBe('6281234567890')
        ->and($user->hasRole('guru-mapel'))->toBeTrue();

    expect(Membership::where('user_id', $user->id)
        ->where('school_id', $this->school->id)
        ->where('status', 'aktif')
        ->exists())->toBeTrue();
});

it('pengguna tanpa nomor WhatsApp dan kata sandi ditolak', function (): void {
    Livewire::actingAs($this->admin)->test(PenggunaForm::class)
        ->set('name', 'Tanpa Login')
        ->set('username', 'tanpa-login')
        ->set('roles', ['guru-mapel'])
        ->call('simpan')
        ->assertHasErrors('phone');

    expect(User::where('username', 'tanpa-login')->exists())->toBeFalse();
});

it('guru mapel tidak boleh membuka kelola pengguna', function (): void {
    Livewire::actingAs($this->guru)->test(PenggunaIndex::class)->assertForbidden();
    Livewire::actingAs($this->guru)->test(PenggunaForm::class)->assertForbidden();
});

it('admin dapat mengubah peran pengguna', function (): void {
    Livewire::actingAs($this->admin)->test(PenggunaForm::class, ['user' => $this->guru])
        ->assertSet('roles', ['guru-mapel'])
        ->set('roles', ['wali-kelas'])
        ->call('simpan')
        ->assertHasNoErrors();

    expect($this->guru->fresh()->hasRole('wali-kelas'))->toBeTrue()
        ->and($this->guru->fresh()->hasRole('guru-mapel'))->toBeFalse();
});

it('admin tidak dapat mengubah pengguna dari sekolah lain', function (): void {
    [$schoolB] = siapkanSekolahPengguna('um-b-'.uniqid());
    $userB = buatPenggunaPeran('budi-b', 'guru-mapel', $schoolB);

    // Kembali ke sekolah A (konteks signup di atas berpindah ke B).
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->actingAs($this->admin)
        ->get(route('pengaturan.pengguna.edit', $userB))
        ->assertNotFound();
});

it('perubahan peran di satu sekolah tidak menghapus peran di sekolah lain', function (): void {
    [$schoolB] = siapkanSekolahPengguna('um-c-'.uniqid());

    Membership::create([
        'user_id' => $this->guru->id,
        'school_id' => $schoolB->id,
        'status' => 'aktif',
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($schoolB->id);
    $this->guru->assignRole('siswa');

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    Livewire::actingAs($this->admin)->test(PenggunaForm::class, ['user' => $this->guru])
        ->set('roles', ['wali-kelas'])
        ->call('simpan')
        ->assertHasNoErrors();

    app(PermissionRegistrar::class)->setPermissionsTeamId($schoolB->id);
    expect($this->guru->fresh()->hasRole('siswa'))->toBeTrue();

    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
    expect($this->guru->fresh()->hasRole('wali-kelas'))->toBeTrue();
});

it('admin tidak dapat menonaktifkan akun sendiri', function (): void {
    Livewire::actingAs($this->admin)->test(PenggunaForm::class, ['user' => $this->admin])
        ->set('status', 'dinonaktifkan')
        ->call('simpan')
        ->assertHasErrors('status');

    expect(Membership::where('user_id', $this->admin->id)
        ->where('school_id', $this->school->id)
        ->value('status'))->toBe('aktif');
});

it('admin terakhir tidak dapat dinonaktifkan oleh operator non-admin', function (): void {
    /** @var Role $operatorRole */
    $operatorRole = Role::create([
        'name' => 'operator-pengguna',
        'guard_name' => 'web',
        'team_id' => $this->school->id,
    ]);
    $operatorRole->syncPermissions(['pengguna.view', 'pengguna.update']);

    $operator = buatPenggunaPeran('operator', 'operator-pengguna', $this->school);

    Livewire::actingAs($operator)->test(PenggunaIndex::class)
        ->call('toggleStatus', $this->admin->id)
        ->assertSet('error', fn ($error) => is_string($error) && $error !== '');

    expect(Membership::where('user_id', $this->admin->id)
        ->where('school_id', $this->school->id)
        ->value('status'))->toBe('aktif');
});

it('admin dapat menonaktifkan pengguna lain (bukan admin terakhir)', function (): void {
    Livewire::actingAs($this->admin)->test(PenggunaIndex::class)
        ->call('toggleStatus', $this->guru->id)
        ->assertSet('error', null);

    expect(Membership::where('user_id', $this->guru->id)
        ->where('school_id', $this->school->id)
        ->value('status'))->toBe('dinonaktifkan');

    // Aktifkan kembali.
    Livewire::actingAs($this->admin)->test(PenggunaIndex::class)
        ->call('toggleStatus', $this->guru->id);

    expect(Membership::where('user_id', $this->guru->id)
        ->where('school_id', $this->school->id)
        ->value('status'))->toBe('aktif');
});

it('pencarian dan filter peran pada daftar pengguna bekerja', function (): void {
    Livewire::actingAs($this->admin)->test(PenggunaIndex::class)
        ->set('search', 'guru-')
        ->assertSee($this->guru->name)
        ->assertDontSee($this->admin->name);

    Livewire::actingAs($this->admin)->test(PenggunaIndex::class)
        ->set('peran', 'guru-mapel')
        ->assertSee($this->guru->name)
        ->assertDontSee($this->admin->name);
});
