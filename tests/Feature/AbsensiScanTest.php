<?php

declare(strict_types=1);

use App\Livewire\Akademik\AttendanceIndex;
use App\Models\AcademicYear;
use App\Models\Attendance;
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
        'nama' => 'Sekolah Scan', 'slug' => 'scan-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $ta = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);

    $this->rombelA = Rombel::create(['nama' => 'VII A', 'academic_year_id' => $ta->id, 'tingkat' => '7']);
    $this->rombelB = Rombel::create(['nama' => 'IX B', 'academic_year_id' => $ta->id, 'tingkat' => '9']);

    $this->siswaA1 = Student::create(['nis' => 'A1', 'nama' => 'Siswa A1', 'jk' => 'L', 'status' => 'aktif']);
    $this->siswaA1->rombels()->attach($this->rombelA->id, ['academic_year_id' => $ta->id, 'status' => 'aktif']);
    $this->siswaA2 = Student::create(['nis' => 'A2', 'nama' => 'Siswa A2', 'jk' => 'P', 'status' => 'aktif']);
    $this->siswaA2->rombels()->attach($this->rombelA->id, ['academic_year_id' => $ta->id, 'status' => 'aktif']);
    $this->siswaB1 = Student::create(['nis' => 'B1', 'nama' => 'Siswa B1', 'jk' => 'L', 'status' => 'aktif']);
    $this->siswaB1->rombels()->attach($this->rombelB->id, ['academic_year_id' => $ta->id, 'status' => 'aktif']);

    $this->admin = User::create([
        'username' => 'admin-'.uniqid(), 'name' => 'Admin Sekolah',
        'phone' => '62812'.random_int(1000000, 9999999),
    ]);
    Membership::create([
        'user_id' => $this->admin->id,
        'school_id' => $this->school->id,
        'status' => 'aktif',
    ]);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('memindai NIS siswa di kelas terpilih dan menandainya Hadir tanpa langsung menyimpan', function (): void {
    Livewire::actingAs($this->admin)->test(AttendanceIndex::class)
        ->set('rombelId', $this->rombelA->id)
        ->set('scanNis', 'A1')
        ->call('scanSiswa')
        ->assertSet('marks.'.$this->siswaA1->id, 'H')
        ->assertSet('scanBerhasil', true)
        ->assertSee('ditandai Hadir');

    expect(Attendance::count())->toBe(0);
});

it('menolak NIS yang tidak dikenal di sekolah ini', function (): void {
    Livewire::actingAs($this->admin)->test(AttendanceIndex::class)
        ->set('rombelId', $this->rombelA->id)
        ->set('scanNis', 'TIDAK-ADA')
        ->call('scanSiswa')
        ->assertSet('scanBerhasil', false)
        ->assertSee('tidak ditemukan di sekolah ini');
});

it('menolak siswa dari kelas lain tanpa mengubah kelas yang dipilih', function (): void {
    Livewire::actingAs($this->admin)->test(AttendanceIndex::class)
        ->set('rombelId', $this->rombelA->id)
        ->set('scanNis', 'B1')
        ->call('scanSiswa')
        ->assertSet('rombelId', $this->rombelA->id)
        ->assertSet('scanBerhasil', false)
        ->assertSee('bukan kelas yang sedang dipilih')
        ->assertSee('IX B');
});

it('memindai mengubah status lama menjadi Hadir dan menyebut status sebelumnya', function (): void {
    Livewire::actingAs($this->admin)->test(AttendanceIndex::class)
        ->set('rombelId', $this->rombelA->id)
        ->set('marks.'.$this->siswaA1->id, 'S')
        ->set('scanNis', 'A1')
        ->call('scanSiswa')
        ->assertSet('marks.'.$this->siswaA1->id, 'H')
        ->assertSee('diubah dari Sakit menjadi Hadir');
});

it('menolak pindaian saat absensi sudah dikunci', function (): void {
    // Insert mentah: cast 'date' Eloquent menyimpan datetime lengkap di SQLite,
    // sementara loadExisting membandingkan string tanggal (di MySQL kolom DATE
    // memotongnya otomatis sehingga tidak bermasalah di produksi).
    DB::table('attendances')->insert([
        'school_id' => $this->school->id,
        'student_id' => $this->siswaA1->id,
        'rombel_id' => $this->rombelA->id,
        'tanggal' => now()->toDateString(),
        'status' => 'H',
        'locked_at' => now(),
        'input_by' => $this->admin->id,
    ]);

    Livewire::actingAs($this->admin)->test(AttendanceIndex::class)
        ->set('rombelId', $this->rombelA->id)
        ->assertSet('marks.'.$this->siswaA1->id, 'H')
        ->assertSet('locked', true)
        ->set('scanNis', 'A2')
        ->call('scanSiswa')
        ->assertSet('scanBerhasil', false)
        ->assertSee('sudah dikunci');
});
