<?php

declare(strict_types=1);

use App\Livewire\Akademik\RombelShow;
use App\Models\AcademicYear;
use App\Models\ClassOfficer;
use App\Models\Employee;
use App\Models\Membership;
use App\Models\Rombel;
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
        'nama' => 'Sekolah Pengurus', 'slug' => 'pengurus-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->ta = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);

    $this->wali = Employee::create([
        'nama' => 'Bu Wali', 'jenis' => 'guru_kelas', 'status_kepegawaian' => 'honorer',
        'status_aktif' => true,
    ]);

    $this->rombel = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '7', 'nama' => 'VII A',
        'kapasitas' => 36, 'wali_kelas_id' => $this->wali->id,
    ]);

    $this->anggota = Student::create(['nis' => '1001', 'nama' => 'Anggota Satu', 'jk' => 'L', 'status' => 'aktif', 'angkatan' => 2026]);
    $this->anggota->rombels()->attach($this->rombel->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);

    $this->anggotaDua = Student::create(['nis' => '1002', 'nama' => 'Anggota Dua', 'jk' => 'P', 'status' => 'aktif', 'angkatan' => 2026]);
    $this->anggotaDua->rombels()->attach($this->rombel->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);

    $this->calonBebas = Student::create(['nis' => '2001', 'nama' => 'Calon Bebas', 'jk' => 'L', 'status' => 'aktif', 'angkatan' => 2026]);

    $this->admin = User::create([
        'username' => 'pengurusadmin-'.uniqid(), 'name' => 'Admin Pengurus',
        'phone' => '62887'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->viewer = User::create([
        'username' => 'pengurusviewer-'.uniqid(), 'name' => 'Viewer Pengurus',
        'phone' => '62888'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->viewer->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->viewer->assignRole('tata-usaha');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('pengurus kelas dapat disimpan dari anggota kelas', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->assertSee('Pengurus kelas')
        ->set('pengurus.ketua', $this->anggota->id)
        ->set('pengurus.bendahara', $this->anggotaDua->id)
        ->call('simpanPengurus')
        ->assertSee('Pengurus kelas disimpan');

    expect(ClassOfficer::where('rombel_id', $this->rombel->id)->where('jabatan', 'ketua')->value('student_id'))->toBe($this->anggota->id);
    expect(ClassOfficer::where('rombel_id', $this->rombel->id)->where('jabatan', 'bendahara')->value('student_id'))->toBe($this->anggotaDua->id);
    expect(ClassOfficer::where('rombel_id', $this->rombel->id)->count())->toBe(2);
});

it('pengurus dari luar anggota kelas ditolak', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('pengurus.ketua', $this->calonBebas->id)
        ->call('simpanPengurus')
        ->assertHasErrors(['pengurus.ketua']);

    expect(ClassOfficer::where('rombel_id', $this->rombel->id)->count())->toBe(0);
});

it('satu siswa tidak boleh memegang dua jabatan', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('pengurus.ketua', $this->anggota->id)
        ->set('pengurus.bendahara', $this->anggota->id)
        ->call('simpanPengurus')
        ->assertHasErrors(['pengurus.ketua']);

    expect(ClassOfficer::where('rombel_id', $this->rombel->id)->count())->toBe(0);
});

it('simpan pengurus ditolak tanpa kelas.update', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('pengurus.ketua', $this->anggota->id)
        ->call('simpanPengurus')
        ->assertForbidden();

    expect(ClassOfficer::where('rombel_id', $this->rombel->id)->count())->toBe(0);
});

it('siswa yang dikeluarkan dari kelas kehilangan jabatannya', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('pengurus.ketua', $this->anggota->id)
        ->call('simpanPengurus');

    expect(ClassOfficer::where('rombel_id', $this->rombel->id)->count())->toBe(1);

    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('selectedAnggota', [$this->anggota->id])
        ->call('keluarkan')
        ->call('simpan')
        ->assertSet('pengurus.ketua', null);

    expect(ClassOfficer::where('rombel_id', $this->rombel->id)->count())->toBe(0);
});
