<?php

declare(strict_types=1);

use App\Livewire\Akademik\RombelIndex;
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
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Rombel', 'slug' => 'rom-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->taAktif = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);
    $this->taLama = AcademicYear::create([
        'nama' => '2025/2026', 'aktif' => false,
        'tgl_mulai' => '2025-07-01', 'tgl_selesai' => '2026-06-30',
    ]);

    $this->wali = Employee::create([
        'nama' => 'Bu Wali', 'jenis' => 'guru_kelas', 'status_kepegawaian' => 'honorer',
        'status_aktif' => true,
    ]);

    $this->rombelA = Rombel::create([
        'academic_year_id' => $this->taAktif->id, 'tingkat' => '7', 'nama' => 'VII A',
        'kapasitas' => 2, 'wali_kelas_id' => $this->wali->id,
    ]);
    $this->rombelB = Rombel::create([
        'academic_year_id' => $this->taAktif->id, 'tingkat' => '8', 'nama' => 'VIII B',
        'kapasitas' => 36,
    ]);
    $this->rombelC = Rombel::create([
        'academic_year_id' => $this->taLama->id, 'tingkat' => '7', 'nama' => 'VII C',
        'kapasitas' => 36, 'wali_kelas_id' => $this->wali->id,
    ]);

    foreach (range(1, 2) as $i) {
        $s = Student::create(['nis' => "A$i", 'nama' => "Siswa A$i", 'jk' => 'L', 'status' => 'aktif']);
        $s->rombels()->attach($this->rombelA->id, ['academic_year_id' => $this->taAktif->id, 'status' => 'aktif']);
    }
    $s = Student::create(['nis' => 'B1', 'nama' => 'Siswa B1', 'jk' => 'P', 'status' => 'aktif']);
    $s->rombels()->attach($this->rombelB->id, ['academic_year_id' => $this->taAktif->id, 'status' => 'aktif']);

    $this->admin = User::create([
        'username' => 'romadmin-'.uniqid(), 'name' => 'Admin Rombel',
        'phone' => '62881'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->viewer = User::create([
        'username' => 'romviewer-'.uniqid(), 'name' => 'Viewer Rombel',
        'phone' => '62882'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->viewer->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->viewer->assignRole('tata-usaha');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('daftar default menampilkan rombel tahun ajaran aktif beserta statistik', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelIndex::class)
        ->assertSee('VII A')
        ->assertSee('VIII B')
        ->assertDontSee('VII C')
        ->assertSee('Total kelas')
        ->assertSee('Siswa aktif di kelas')
        ->assertSee('Kelas tanpa wali kelas')
        ->assertSee('Kelas mencapai kapasitas');
});

it('statistik dihitung di query layer: total, siswa, tanpa wali, penuh', function (): void {
    $stats = Livewire::actingAs($this->admin)
        ->test(RombelIndex::class)
        ->viewData('stats');

    expect($stats)->toBe([
        'total' => 3,
        'siswa' => 3,
        'tanpaWali' => 1,
        'penuh' => 1,
    ]);
});

it('filter tingkat, tahun ajaran, dan pencarian membatasi daftar', function (): void {
    $component = Livewire::actingAs($this->admin)->test(RombelIndex::class);

    $component->set('tingkat', '7')
        ->assertSee('VII A')
        ->assertDontSee('VIII B');

    $component->set('tingkat', '')
        ->set('academicYearId', $this->taLama->id)
        ->assertSee('VII C')
        ->assertDontSee('VII A');

    $component->set('academicYearId', $this->taAktif->id)
        ->set('search', 'VIII')
        ->assertSee('VIII B')
        ->assertDontSee('VII A');
});

it('reset filter kembali ke tahun ajaran aktif', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelIndex::class)
        ->set('search', 'VIII')
        ->set('tingkat', '8')
        ->set('academicYearId', $this->taLama->id)
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('tingkat', '')
        ->assertSet('academicYearId', $this->taAktif->id)
        ->assertSee('VII A')
        ->assertSee('VIII B');
});

it('perubahan pencarian me-reset pagination ke halaman pertama', function (): void {
    foreach (range(1, 16) as $i) {
        Rombel::create([
            'academic_year_id' => $this->taAktif->id, 'tingkat' => '9',
            'nama' => sprintf('IX %02d', $i), 'kapasitas' => 36,
        ]);
    }

    Livewire::actingAs($this->admin)
        ->test(RombelIndex::class)
        ->call('gotoPage', 2)
        ->set('search', 'IX 01')
        ->assertSee('IX 01')
        ->assertDontSee('IX 16');
});

it('tombol tambah hanya tampil bagi pemegang kelas.create', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelIndex::class)
        ->assertSee('Tambah Kelas');

    Livewire::actingAs($this->viewer)
        ->test(RombelIndex::class)
        ->assertDontSee('Tambah Kelas');
});

it('daftar kosong menampilkan empty state', function (): void {
    Rombel::query()->forceDelete();

    Livewire::actingAs($this->admin)
        ->test(RombelIndex::class)
        ->set('search', 'tidak-ada')
        ->assertSee('Kelas tidak ditemukan');
});
