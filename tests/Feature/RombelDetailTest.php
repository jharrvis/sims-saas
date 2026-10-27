<?php

declare(strict_types=1);

use App\Livewire\Akademik\RombelDetail;
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
        'nama' => 'Sekolah Detail', 'slug' => 'detail-'.uniqid(), 'jenjang' => 'SMP',
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
        'academic_year_id' => $this->ta->id, 'tingkat' => '9', 'nama' => 'IX A',
        'kapasitas' => 32, 'wali_kelas_id' => $this->wali->id,
    ]);

    $this->anggota = Student::create(['nis' => '1001', 'nisn' => '111', 'nama' => 'Anggota Satu', 'jk' => 'L', 'status' => 'aktif', 'angkatan' => 2026]);
    $this->anggota->rombels()->attach($this->rombel->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);

    $this->admin = User::create([
        'username' => 'detailadmin-'.uniqid(), 'name' => 'Admin Detail',
        'phone' => '62885'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->viewer = User::create([
        'username' => 'detailviewer-'.uniqid(), 'name' => 'Viewer Detail',
        'phone' => '62886'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->viewer->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->viewer->assignRole('tata-usaha');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('halaman lihat kelas menampilkan info, daftar anggota, dan tombol atur hanya untuk kelas.update', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelDetail::class, ['rombel' => $this->rombel])
        ->assertSee('Kelas IX A')
        ->assertSee('Bu Wali')
        ->assertSee('1 / 32 siswa')
        ->assertSee('Anggota Satu')
        ->assertSee('Atur anggota kelas')
        ->assertSee('Ubah kelas');

    Livewire::actingAs($this->viewer)
        ->test(RombelDetail::class, ['rombel' => $this->rombel])
        ->assertSee('Kelas IX A')
        ->assertSee('Anggota Satu')
        ->assertDontSee('Atur anggota kelas')
        ->assertDontSee('Ubah kelas');
});

it('pencarian anggota halaman lihat memakai nama, NIS, dan NISN', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelDetail::class, ['rombel' => $this->rombel])
        ->set('searchAnggota', '111')
        ->assertSee('Anggota Satu')
        ->set('searchAnggota', 'TidakAda')
        ->assertSee('Siswa tidak ditemukan')
        ->assertDontSee('Anggota Satu');
});

it('halaman lihat kelas kosong menampilkan empty state anggota', function (): void {
    $kosong = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '8', 'nama' => 'VIII Kosong',
        'kapasitas' => 36,
    ]);

    Livewire::actingAs($this->admin)
        ->test(RombelDetail::class, ['rombel' => $kosong])
        ->assertSee('Belum ada siswa di kelas ini');
});

it('daftar anggota halaman lihat dipaginasi 8 per halaman', function (): void {
    foreach (range('B', 'J') as $huruf) {
        $s = Student::create(['nis' => '200'.$huruf, 'nama' => 'Anggota '.$huruf, 'jk' => 'L', 'status' => 'aktif', 'angkatan' => 2026]);
        $s->rombels()->attach($this->rombel->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);
    }

    Livewire::actingAs($this->admin)
        ->test(RombelDetail::class, ['rombel' => $this->rombel])
        ->assertSee('10 siswa terdaftar')
        ->assertDontSee('Anggota J')
        ->call('gotoAnggotaPage', 2)
        ->assertSee('Anggota J')
        ->assertDontSee('Anggota B');
});

it('halaman lihat kelas menampilkan pengurus kelas', function (): void {
    ClassOfficer::create([
        'rombel_id' => $this->rombel->id,
        'student_id' => $this->anggota->id,
        'academic_year_id' => $this->ta->id,
        'jabatan' => 'ketua',
    ]);

    Livewire::actingAs($this->admin)
        ->test(RombelDetail::class, ['rombel' => $this->rombel])
        ->assertSee('Pengurus kelas')
        ->assertSee('Ketua Kelas')
        ->assertSee('Anggota Satu')
        ->assertSee('Bendahara');
});

it('rute kelola anggota hanya dapat dibuka pemegang kelas.update', function (): void {
    $this->actingAs($this->viewer)->get(route('kelas.anggota', $this->rombel))->assertForbidden();
    $this->actingAs($this->admin)->get(route('kelas.anggota', $this->rombel))->assertOk();
    $this->actingAs($this->viewer)->get(route('kelas.show', $this->rombel))->assertOk();
});

it('tabel anggota menampilkan nomor urut, NISN, dan jenis kelamin', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelDetail::class, ['rombel' => $this->rombel])
        ->assertSee('No')
        ->assertSee('NISN')
        ->assertSee('Jenis Kelamin')
        ->assertSee('111')
        ->assertSee('Laki-laki');
});

it('jumlah baris per halaman dapat diubah dan semua dapat ditampilkan', function (): void {
    foreach (range(2, 10) as $i) {
        $s = Student::create([
            'nis' => '100'.$i, 'nisn' => '11'.$i, 'nama' => 'Siswa '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            'jk' => 'P', 'status' => 'aktif', 'angkatan' => 2026,
        ]);
        $s->rombels()->attach($this->rombel->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);
    }

    $test = Livewire::actingAs($this->admin)
        ->test(RombelDetail::class, ['rombel' => $this->rombel])
        ->assertDontSee('Siswa 10')
        ->set('perPage', 0)
        ->assertSee('Siswa 10')
        ->assertSee('Menampilkan semua 10 siswa');

    expect($test->get('perPage'))->toBe(0);
});

it('klik baris membuka modal detail siswa', function (): void {
    $lengkap = Student::create([
        'nis' => '1002', 'nisn' => '222', 'nama' => 'Siswa Lengkap', 'jk' => 'P',
        'tempat_lahir' => 'Salatiga', 'tgl_lahir' => '2012-05-06', 'agama' => 'Islam',
        'alamat' => 'Jl. Merdeka 1', 'phone' => '628123456789', 'status' => 'aktif', 'angkatan' => 2026,
    ]);
    $lengkap->rombels()->attach($this->rombel->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);

    Livewire::actingAs($this->admin)
        ->test(RombelDetail::class, ['rombel' => $this->rombel])
        ->call('bukaDetail', $lengkap->id)
        ->assertSee('Siswa Lengkap')
        ->assertSee('222')
        ->assertSee('Perempuan')
        ->assertSee('Salatiga')
        ->assertSee('Islam')
        ->assertSee('Jl. Merdeka 1')
        ->call('tutupDetail')
        ->assertSet('detailSiswaId', null);
});

it('foto siswa tampil bila ada di tabel anggota', function (): void {
    $this->anggota->update(['foto_path' => 'siswa/anggota-satu.jpg']);

    Livewire::actingAs($this->admin)
        ->test(RombelDetail::class, ['rombel' => $this->rombel])
        ->assertSee('/storage/siswa/anggota-satu.jpg');
});
