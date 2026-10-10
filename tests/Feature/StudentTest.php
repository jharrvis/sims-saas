<?php

declare(strict_types=1);

use App\Models\Membership;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * @return array{0: School, 1: int}
 */
function setupSekolahDenganRole(string $slug): array
{
    $school = School::create([
        'nama' => 'Sekolah '.$slug, 'slug' => $slug, 'jenjang' => 'SD',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($school->id);

    return [$school, $school->id];
}

function buatUserDenganRole(string $username, string $role, School $school): User
{
    $user = User::create([
        'username' => $username, 'name' => 'User '.$username,
        'phone' => '628'.random_int(1000000000, 1999999999),
    ]);
    Membership::create(['user_id' => $user->id, 'school_id' => $school->id, 'status' => 'aktif']);

    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
    $user->assignRole($role);
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);

    return $user;
}

beforeEach(function (): void {
    [$this->school] = setupSekolahDenganRole('sis-'.uniqid());
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('admin-sekolah bisa CRUD siswa, guru-mapel hanya view', function (): void {
    $admin = buatUserDenganRole('adm1', 'admin-sekolah', $this->school);
    $guru = buatUserDenganRole('gr1', 'guru-mapel', $this->school);

    // Admin: create OK
    $this->actingAs($admin)->postJson('/api/v1/siswa', [
        'nis' => '1001', 'nama' => 'Budi Santoso', 'jk' => 'L',
    ])->assertCreated();

    // Guru: create ditolak
    $this->actingAs($guru)->postJson('/api/v1/siswa', [
        'nis' => '1002', 'nama' => 'Ani', 'jk' => 'P',
    ])->assertForbidden();

    // Guru: view OK
    $this->actingAs($guru)->getJson('/api/v1/siswa')->assertOk();

    // Admin: delete OK (soft delete)
    $siswa = Student::where('nis', '1001')->first();
    $this->actingAs($admin)->deleteJson('/api/v1/siswa/'.$siswa->id)->assertOk();
    expect(Student::withTrashed()->find($siswa->id)->trashed())->toBeTrue();
});

it('data siswa terisolasi antar tenant (negatif)', function (): void {
    [$schoolB] = setupSekolahDenganRole('sis-b-'.uniqid());

    $adminA = buatUserDenganRole('admA', 'admin-sekolah', $this->school);
    $adminB = buatUserDenganRole('admB2', 'admin-sekolah', $schoolB);

    // Buat siswa di tenant A
    app(TenantContext::class)->set($this->school);
    $siswa = Student::create(['nis' => '2001', 'nama' => 'Siswa A', 'jk' => 'L']);

    // User tenant B (konteks di-resolve middleware dari membershipnya)
    // mencoba akses siswa tenant A → tidak ditemukan (404 via route binding)
    $this->actingAs($adminB)->getJson('/api/v1/siswa/'.$siswa->id)->assertNotFound();

    // List di tenant B → kosong
    $this->actingAs($adminB)->getJson('/api/v1/siswa')
        ->assertOk()
        ->assertJsonPath('total', 0);

    // User tenant A tetap bisa akses
    $this->actingAs($adminA)->getJson('/api/v1/siswa/'.$siswa->id)->assertOk();
});

it('NIS unik per sekolah, boleh sama antar sekolah', function (): void {
    [$schoolB] = setupSekolahDenganRole('sis-c-'.uniqid());
    $adminA = buatUserDenganRole('admB', 'admin-sekolah', $this->school);

    app(TenantContext::class)->set($this->school);
    Student::create(['nis' => '3001', 'nama' => 'Siswa A', 'jk' => 'L']);

    // NIS sama di tenant A → 422
    $this->actingAs($adminA)->postJson('/api/v1/siswa', [
        'nis' => '3001', 'nama' => 'Duplikat', 'jk' => 'L',
    ])->assertStatus(422);

    // NIS sama di tenant B → OK
    app(TenantContext::class)->set($schoolB);
    $adminB = buatUserDenganRole('admC', 'admin-sekolah', $schoolB);
    $this->actingAs($adminB)->postJson('/api/v1/siswa', [
        'nis' => '3001', 'nama' => 'Siswa B', 'jk' => 'P',
    ])->assertCreated();
});
