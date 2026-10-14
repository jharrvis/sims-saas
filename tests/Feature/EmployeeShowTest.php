<?php

declare(strict_types=1);

use App\Livewire\Kepegawaian\EmployeeIndex;
use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\Membership;
use App\Models\Rombel;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Guru', 'slug' => 'guru-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'guruadmin', 'name' => 'Guru Admin', 'phone' => '628590000020',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $ta = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);

    $this->guru = Employee::create([
        'nip_nuptk' => '12345', 'nama' => 'Guru Detail', 'jenis' => 'guru_mapel',
        'status_kepegawaian' => 'honorer', 'jabatan' => 'Wali Kelas VII A',
        'phone' => '628120000001', 'status_aktif' => true,
    ]);

    $this->rombel = Rombel::create([
        'nama' => 'VII A', 'academic_year_id' => $ta->id, 'tingkat' => '7',
        'wali_kelas_id' => $this->guru->id,
    ]);

    $mapel = Subject::create([
        'kode' => 'MTK', 'nama' => 'Matematika', 'kelompok' => 'wajib', 'kkm_default' => 75,
    ]);

    Schedule::create([
        'rombel_id' => $this->rombel->id, 'academic_year_id' => $ta->id,
        'hari' => 'senin', 'jam_ke' => 1, 'subject_id' => $mapel->id,
        'employee_id' => $this->guru->id,
    ]);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('halaman detail guru menampilkan biodata, wali kelas, dan jadwal mengajar', function (): void {
    $this->actingAs($this->admin)
        ->get('/guru/'.$this->guru->id)
        ->assertOk()
        ->assertSee('Guru Detail')
        ->assertSee('12345')
        ->assertSee('628120000001')
        ->assertSee('VII A')
        ->assertSee('Matematika')
        ->assertSee('Jam ke-1');
});

it('daftar guru memakai ikon x-icon + tombol detail tetap ada setelah pencarian di-clear', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->set('search', 'Guru Detail')
        ->set('search', '')
        ->assertSeeHtml(route('guru.show', $this->guru))
        ->assertSeeHtml('<svg');
});
