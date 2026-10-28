<?php

declare(strict_types=1);

use App\Livewire\Kesiswaan\GuardianForm;
use App\Livewire\Kesiswaan\GuardianIndex;
use App\Livewire\Kesiswaan\GuardianShow;
use App\Models\Guardian;
use App\Models\Membership;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Wali', 'slug' => 'wali-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->ayah = Guardian::create([
        'nama' => 'Budi Ayah', 'hubungan' => 'ayah', 'no_wa' => '628111111111',
    ]);
    $this->ibu = Guardian::create([
        'nama' => 'Siti Ibu', 'hubungan' => 'ibu', 'no_wa' => '628222222222',
        'pekerjaan' => 'Guru',
    ]);

    $this->siswa = Student::create([
        'nis' => '1001', 'nama' => 'Anak Satu', 'jk' => 'L',
    ]);

    $this->admin = User::create([
        'username' => 'waliadmin-'.uniqid(), 'name' => 'Admin Wali',
        'phone' => '62851'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->guruMapel = User::create([
        'username' => 'waliguru-'.uniqid(), 'name' => 'Guru Mapel',
        'phone' => '62853'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->guruMapel->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->guruMapel->assignRole('guru-mapel');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('halaman daftar wali menampilkan data dan entri menu', function (): void {
    $this->actingAs($this->admin)
        ->get('/wali')
        ->assertOk()
        ->assertSee('Data Wali')
        ->assertSee('Budi Ayah')
        ->assertSee('Siti Ibu')
        ->assertSee('Tambah Wali');
});

it('pencarian dan filter hubungan mempersempit daftar', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianIndex::class)
        ->set('search', 'Siti')
        ->assertSee('Siti Ibu')
        ->assertDontSee('Budi Ayah')
        ->set('search', '')
        ->set('hubungan', 'ayah')
        ->assertSee('Budi Ayah')
        ->assertDontSee('Siti Ibu');
});

it('form membuat wali baru dan memvalidasi nomor WA', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianForm::class)
        ->set('nama', 'Wali Baru')
        ->set('hubungan', 'wali')
        ->call('save')
        ->assertHasErrors(['no_wa'])
        ->set('no_wa', '628333333333')
        ->call('save')
        ->assertRedirect(route('wali.index'));

    $this->assertDatabaseHas('guardians', [
        'nama' => 'Wali Baru', 'hubungan' => 'wali', 'no_wa' => '628333333333',
    ]);
});

it('detail wali menautkan dan melepas siswa', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianShow::class, ['guardian' => $this->ayah])
        ->set('linkStudentId', (string) $this->siswa->id)
        ->set('linkHubungan', 'ayah')
        ->call('linkStudent')
        ->assertSee('Anak Satu');

    $this->assertDatabaseHas('guardian_student', [
        'guardian_id' => $this->ayah->id, 'student_id' => $this->siswa->id,
        'hubungan' => 'ayah',
    ]);

    Livewire::actingAs($this->admin)
        ->test(GuardianShow::class, ['guardian' => $this->ayah])
        ->call('unlinkStudent', $this->siswa->id);

    $this->assertDatabaseMissing('guardian_student', [
        'guardian_id' => $this->ayah->id, 'student_id' => $this->siswa->id,
    ]);
});

it('pengguna tanpa permission wali-murid.view tidak boleh membuka daftar', function (): void {
    $this->actingAs($this->guruMapel)
        ->get('/wali')
        ->assertForbidden();
});
