<?php

declare(strict_types=1);

use App\Livewire\Kepegawaian\EmployeeForm;
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

it('peran akun di detail guru hanya dari sekolah pegawai ini (QA B-18)', function (): void {
    $user = User::create([
        'username' => 'gurulintas', 'name' => 'Guru Detail', 'phone' => '628120000099',
        'employee_id' => $this->guru->id,
    ]);
    Membership::create(['user_id' => $user->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $user->assignRole('guru-mapel');

    $sekolahLain = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    RoleSeeder::seedForSchool($sekolahLain->id);
    Membership::create(['user_id' => $user->id, 'school_id' => $sekolahLain->id, 'status' => 'aktif']);
    app(PermissionRegistrar::class)->setPermissionsTeamId($sekolahLain->id);
    $user->assignRole('tata-usaha');
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->actingAs($this->admin)
        ->get('/guru/'.$this->guru->id)
        ->assertOk()
        ->assertSee('Guru Mapel')
        ->assertDontSee('Tata Usaha');
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

it('halaman detail guru menampilkan data identitas, pendidikan, dan kepegawaian', function (): void {
    $this->guru->update([
        'nik' => '3201010101800001',
        'tempat_lahir' => 'Bandung',
        'tgl_lahir' => '1980-01-01',
        'jk' => 'L',
        'agama' => 'Islam',
        'alamat' => 'Jl. Merdeka No. 1',
        'email' => 'guru@example.com',
        'pendidikan_terakhir' => 's1',
        'jurusan' => 'Pendidikan Matematika',
        'perguruan_tinggi' => 'Universitas Pendidikan Indonesia',
        'tahun_lulus' => 2005,
        'tmt' => '2006-07-01',
        'golongan' => 'III/a',
        'sertifikasi' => 'sudah',
        'no_sertifikat' => '123/UN40/2009',
    ]);

    $this->actingAs($this->admin)
        ->get('/guru/'.$this->guru->id)
        ->assertOk()
        // NIK tampil tersamar di halaman detail (QA F-01).
        ->assertSee('••••0001')
        ->assertDontSee('3201010101800001')
        ->assertSee('Bandung')
        ->assertSee('Laki-laki')
        ->assertSee('Islam')
        ->assertSee('guru@example.com')
        ->assertSee('S1')
        ->assertSee('Pendidikan Matematika')
        ->assertSee('Universitas Pendidikan Indonesia')
        ->assertSee('2005')
        ->assertSee('III/a')
        ->assertSee('Sudah Sertifikasi')
        ->assertSee('123/UN40/2009');
});

it('form guru menampilkan dropdown agama, pendidikan, golongan, dan sertifikasi', function (): void {
    Livewire::test(EmployeeForm::class)
        ->assertSee('Konghucu')
        ->assertSee('Pendidikan Terakhir')
        ->assertSee('Golongan / Pangkat')
        ->assertSee('Sertifikasi');
});

it('form guru menolak agama, pendidikan, golongan, dan sertifikasi yang tidak valid', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EmployeeForm::class)
        ->set('nama', 'Guru Validasi')
        ->set('agama', 'Atheis')
        ->set('pendidikan_terakhir', 'd8')
        ->set('golongan', 'V/a')
        ->set('sertifikasi', 'maybe')
        ->call('save')
        ->assertHasErrors(['agama', 'pendidikan_terakhir', 'golongan', 'sertifikasi']);
});

it('form guru menyimpan data identitas, pendidikan, dan kepegawaian', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EmployeeForm::class)
        ->set('nama', 'Guru Lengkap')
        ->set('nik', '3201010101800002')
        ->set('jk', 'P')
        ->set('agama', 'Kristen')
        ->set('email', 'lengkap@example.com')
        ->set('phone', '628120000009')
        ->set('pendidikan_terakhir', 's2')
        ->set('jurusan', 'Manajemen Pendidikan')
        ->set('perguruan_tinggi', 'UPI')
        ->set('tahun_lulus', '2010')
        ->set('tmt', '2011-07-01')
        ->set('golongan', 'III/b')
        ->set('sertifikasi', 'proses')
        ->call('save');

    $guru = Employee::where('nama', 'Guru Lengkap')->first();

    expect($guru)->not->toBeNull()
        ->and($guru->nik)->toBe('3201010101800002')
        ->and($guru->jk)->toBe('P')
        ->and($guru->agama)->toBe('Kristen')
        ->and($guru->email)->toBe('lengkap@example.com')
        ->and($guru->phone)->toBe('628120000009')
        ->and($guru->pendidikan_terakhir)->toBe('s2')
        ->and($guru->jurusan)->toBe('Manajemen Pendidikan')
        ->and($guru->tahun_lulus)->toBe(2010)
        ->and($guru->golongan)->toBe('III/b')
        ->and($guru->sertifikasi)->toBe('proses');
});
