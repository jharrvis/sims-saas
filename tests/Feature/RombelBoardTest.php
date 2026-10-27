<?php

declare(strict_types=1);

use App\Livewire\Akademik\RombelShow;
use App\Livewire\Kesiswaan\StudentForm;
use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\Membership;
use App\Models\Rombel;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Board', 'slug' => 'board-'.uniqid(), 'jenjang' => 'SMP',
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

    $this->rombelLain = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '7', 'nama' => 'VII B',
        'kapasitas' => 36, 'wali_kelas_id' => $this->wali->id,
    ]);

    $this->anggota = Student::create(['nis' => '1001', 'nisn' => '111', 'nama' => 'Anggota Satu', 'jk' => 'L', 'status' => 'aktif', 'angkatan' => 2026]);
    $this->anggota->rombels()->attach($this->rombel->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);

    $this->calonBebas = Student::create(['nis' => '2001', 'nisn' => '222', 'nama' => 'Calon Bebas', 'jk' => 'P', 'status' => 'aktif', 'angkatan' => 2026]);
    $this->calonPindah = Student::create(['nis' => '2002', 'nisn' => '333', 'nama' => 'Calon Pindah', 'jk' => 'L', 'status' => 'aktif', 'angkatan' => 2025]);
    $this->calonPindah->rombels()->attach($this->rombelLain->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);

    $this->admin = User::create([
        'username' => 'boardadmin-'.uniqid(), 'name' => 'Admin Board',
        'phone' => '62883'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->viewer = User::create([
        'username' => 'boardviewer-'.uniqid(), 'name' => 'Viewer Board',
        'phone' => '62884'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->viewer->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->viewer->assignRole('tata-usaha');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('papan dua kolom tampil untuk admin dan hanya anggota untuk viewer', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->assertSee('Calon Siswa')
        ->assertSee('Anggota Kelas')
        ->assertSee('Pindahkan yang dipilih')
        ->assertSee('Keluarkan yang dipilih')
        ->assertSee('Belum ada kelas')
        ->assertSee('Dari VII B');

    Livewire::actingAs($this->viewer)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->assertSee('Anggota Kelas')
        ->assertDontSee('Calon Siswa')
        ->assertDontSee('Pindahkan yang dipilih');
});

it('pencarian calon dan anggota memakai nama, NIS, dan NISN', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('searchSiswa', '222')
        ->assertSee('Calon Bebas')
        ->assertDontSee('Calon Pindah')
        ->set('searchSiswa', '2002')
        ->assertSee('Calon Pindah')
        ->set('searchAnggota', '111')
        ->assertSee('Anggota Satu');
});

it('filter asal memisahkan siswa tanpa kelas dan siswa dari kelas lain', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('filterAsal', 'tanpa-kelas')
        ->assertSee('Calon Bebas')
        ->assertDontSee('Calon Pindah')
        ->set('filterAsal', 'rombel:'.$this->rombelLain->id)
        ->assertSee('Calon Pindah')
        ->assertDontSee('Calon Bebas');
});

it('sortir calon berdasarkan angkatan menaruh angkatan lama lebih dulu', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('sortCalon', 'angkatan')
        ->assertSeeInOrder(['Calon Pindah', 'Calon Bebas']);
});

it('pilih semua calon memilih yang tampil, lalu membatalkannya', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->call('toggleSelectAllCalon')
        ->assertSet('selectedCalon', [$this->calonBebas->id, $this->calonPindah->id])
        ->call('toggleSelectAllCalon')
        ->assertSet('selectedCalon', []);
});

it('pindah massal menonaktifkan penempatan lama di tahun ajaran yang sama', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('selectedCalon', [$this->calonBebas->id, $this->calonPindah->id])
        ->call('assignSelected');

    expect($this->rombel->students()->wherePivot('status', 'aktif')->where('students.id', $this->calonBebas->id)->exists())->toBeTrue();
    expect($this->rombel->students()->wherePivot('status', 'aktif')->where('students.id', $this->calonPindah->id)->exists())->toBeTrue();
    expect($this->rombelLain->students()->wherePivot('status', 'aktif')->where('students.id', $this->calonPindah->id)->exists())->toBeFalse();
    expect(DB::table('class_student')->where('rombel_id', $this->rombelLain->id)->where('student_id', $this->calonPindah->id)->value('status'))->toBe('pindah');
});

it('keluar massal mengubah penempatan menjadi pindah, bukan menghapus riwayat', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('selectedAnggota', [$this->anggota->id])
        ->call('removeSelected');

    expect($this->rombel->students()->wherePivot('status', 'aktif')->where('students.id', $this->anggota->id)->exists())->toBeFalse();
    expect(DB::table('class_student')->where('rombel_id', $this->rombel->id)->where('student_id', $this->anggota->id)->value('status'))->toBe('pindah');
});

it('seleksi calon bertahan saat kata kunci pencarian diubah', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('selectedCalon', [$this->calonBebas->id])
        ->set('searchSiswa', 'Pindah')
        ->assertSet('selectedCalon', [$this->calonBebas->id]);
});

it('pindah saat kelas penuh menahan aksi sampai dikonfirmasi', function (): void {
    $this->rombel->update(['kapasitas' => 1]);

    $test = Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->call('assignStudent', $this->calonBebas->id)
        ->assertSet('confirmKapasitas', true)
        ->assertSet('pendingAssign', [$this->calonBebas->id]);

    expect($this->rombel->students()->wherePivot('status', 'aktif')->where('students.id', $this->calonBebas->id)->exists())->toBeFalse();

    $test->call('confirmPendingAssign')
        ->assertSet('confirmKapasitas', false);

    expect($this->rombel->students()->wherePivot('status', 'aktif')->where('students.id', $this->calonBebas->id)->exists())->toBeTrue();
});

it('aksi massal ditolak tanpa kelas.update', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('selectedCalon', [$this->calonBebas->id])
        ->call('assignSelected')
        ->assertForbidden();

    Livewire::actingAs($this->viewer)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('selectedAnggota', [$this->anggota->id])
        ->call('removeSelected')
        ->assertForbidden();

    expect($this->rombel->students()->wherePivot('status', 'aktif')->where('students.id', $this->calonBebas->id)->exists())->toBeFalse();
});

it('backfill angkatan memakai tahun mulai penempatan paling awal', function (): void {
    $taLama = AcademicYear::create([
        'nama' => '2025/2026', 'aktif' => false,
        'tgl_mulai' => '2025-07-01', 'tgl_selesai' => '2026-06-30',
    ]);
    $rombelLama = Rombel::create([
        'academic_year_id' => $taLama->id, 'tingkat' => '6', 'nama' => 'VI A',
        'kapasitas' => 36,
    ]);

    $lama = Student::create(['nis' => '4001', 'nama' => 'Siswa Lama', 'jk' => 'L', 'status' => 'aktif']);
    $lama->rombels()->attach($rombelLama->id, ['academic_year_id' => $taLama->id, 'status' => 'pindah']);
    $lama->rombels()->attach($this->rombelLain->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);

    $baru = Student::create(['nis' => '4002', 'nama' => 'Siswa Baru', 'jk' => 'P', 'status' => 'aktif']);
    $baru->rombels()->attach($this->rombelLain->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);

    $tanpa = Student::create(['nis' => '4003', 'nama' => 'Siswa Tanpa Kelas', 'jk' => 'L', 'status' => 'aktif']);

    $migration = require base_path('database/migrations/2026_10_27_024050_add_angkatan_to_students_table.php');
    $migration->up();

    expect($lama->fresh()->angkatan)->toBe(2025);
    expect($baru->fresh()->angkatan)->toBe(2026);
    expect($tanpa->fresh()->angkatan)->toBeNull();
});

it('form siswa menyimpan angkatan', function (): void {
    Livewire::actingAs($this->admin)
        ->test(StudentForm::class)
        ->set('nis', '3001')
        ->set('nama', 'Siswa Angkatan')
        ->set('jk', 'L')
        ->set('angkatan', '2024')
        ->call('save')
        ->assertHasNoErrors();

    expect(Student::where('nis', '3001')->value('angkatan'))->toBe(2024);
});
