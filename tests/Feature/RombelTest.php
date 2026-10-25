<?php

declare(strict_types=1);

use App\Livewire\Akademik\RombelForm;
use App\Livewire\Akademik\RombelIndex;
use App\Livewire\Akademik\RombelShow;
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
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Rombel Form', 'slug' => 'romf-'.uniqid(), 'jenjang' => 'SMP',
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

    $this->siswa = Student::create(['nis' => '1001', 'nama' => 'Siswa Rombel', 'jk' => 'L', 'status' => 'aktif']);
    $this->siswa->rombels()->attach($this->rombel->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);

    $this->calon = Student::create(['nis' => '1002', 'nama' => 'Siswa Calon', 'jk' => 'P', 'status' => 'aktif']);

    $this->admin = User::create([
        'username' => 'romfadmin-'.uniqid(), 'name' => 'Admin Rombel',
        'phone' => '62883'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->viewer = User::create([
        'username' => 'romfviewer-'.uniqid(), 'name' => 'Viewer Rombel',
        'phone' => '62884'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->viewer->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->viewer->assignRole('tata-usaha');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('form rombel memakai struktur design system management', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelForm::class)
        ->assertSee('Tambah Kelas')
        ->assertSee('Kembali ke Data Kelas')
        ->assertSeeHtml('management-form-card')
        ->assertSeeHtml('management-form-grid')
        ->assertDontSee('bg-boxdark')
        ->set('nama', '')
        ->call('save')
        ->assertHasErrors(['nama'])
        ->assertSeeHtml('management-field-error');

    Livewire::actingAs($this->admin)
        ->test(RombelForm::class, ['rombel' => $this->rombel])
        ->assertSee('Ubah Kelas');
});

it('simpan form rombel menolak pengguna tanpa kelas.create dan kelas.update', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(RombelForm::class)
        ->set('academic_year_id', $this->ta->id)
        ->set('tingkat', '7')
        ->set('nama', 'VII X')
        ->set('kapasitas', 30)
        ->call('save')
        ->assertForbidden();

    expect(Rombel::where('nama', 'VII X')->exists())->toBeFalse();

    Livewire::actingAs($this->viewer)
        ->test(RombelForm::class, ['rombel' => $this->rombel])
        ->set('nama', 'Nama Diubah Paksa')
        ->call('save')
        ->assertForbidden();

    expect(Rombel::find($this->rombel->id)->nama)->toBe('VII A');
});

it('simpan form rombel admin berhasil dengan data tenant sendiri', function (): void {
    Livewire::actingAs($this->admin)
        ->test(RombelForm::class)
        ->set('academic_year_id', $this->ta->id)
        ->set('tingkat', '7')
        ->set('nama', 'VII B')
        ->set('kapasitas', 30)
        ->set('wali_kelas_id', $this->wali->id)
        ->call('save')
        ->assertHasNoErrors();

    expect(Rombel::where('nama', 'VII B')->exists())->toBeTrue();
});

it('form rombel menolak tahun ajaran dan wali kelas milik tenant lain', function (): void {
    $sekolahLain = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(TenantContext::class)->set($sekolahLain);
    $taLain = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);
    $waliLain = Employee::create([
        'nama' => 'Wali Lain', 'jenis' => 'guru_kelas', 'status_kepegawaian' => 'honorer',
        'status_aktif' => true,
    ]);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    Livewire::actingAs($this->admin)
        ->test(RombelForm::class)
        ->set('academic_year_id', $taLain->id)
        ->set('tingkat', '7')
        ->set('nama', 'VII Asing')
        ->set('kapasitas', 30)
        ->call('save')
        ->assertHasErrors(['academic_year_id']);

    Livewire::actingAs($this->admin)
        ->test(RombelForm::class)
        ->set('academic_year_id', $this->ta->id)
        ->set('tingkat', '7')
        ->set('nama', 'VII Asing')
        ->set('kapasitas', 30)
        ->set('wali_kelas_id', $waliLain->id)
        ->call('save')
        ->assertHasErrors(['wali_kelas_id']);

    expect(Rombel::where('nama', 'VII Asing')->exists())->toBeFalse();
});

it('detail rombel: anggota dapat dicari, tempatkan dan keluarkan hanya dengan kelas.update', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->call('assignStudent', $this->calon->id)
        ->assertForbidden();

    Livewire::actingAs($this->viewer)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->call('removeStudent', $this->siswa->id)
        ->assertForbidden();

    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->set('searchAnggota', 'Rombel')
        ->assertSee('Siswa Rombel')
        ->set('searchAnggota', 'TidakAda')
        ->assertSee('Siswa tidak ditemukan')
        ->assertDontSee('Siswa Rombel');

    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->call('assignStudent', $this->calon->id);

    expect($this->rombel->students()->wherePivot('status', 'aktif')->where('students.id', $this->calon->id)->exists())->toBeTrue();

    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $this->rombel])
        ->call('removeStudent', $this->calon->id);

    expect($this->rombel->students()->wherePivot('status', 'aktif')->where('students.id', $this->calon->id)->exists())->toBeFalse();
});

it('detail rombel kosong menampilkan empty state anggota', function (): void {
    $kosong = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '8', 'nama' => 'VIII Kosong',
        'kapasitas' => 36,
    ]);

    Livewire::actingAs($this->admin)
        ->test(RombelShow::class, ['rombel' => $kosong])
        ->assertSee('Belum ada siswa di kelas ini');
});

it('tombol kenaikan kelas hanya tampil bila route tersedia dan pengguna punya kelas.promote', function (): void {
    // Fitur kenaikan (PromoteStudents) belum diimplementasikan, sehingga
    // route produksi kelas.kenaikan belum terdaftar. Uji ini memakai route
    // stub dengan permission yang sama untuk menguji guard di Blade:
    // Route::has('kelas.kenaikan') + can('kelas.promote').
    Route::get('/kelas/kenaikan-stub', fn () => 'stub')
        ->name('kelas.kenaikan')
        ->middleware('permission:kelas.promote');

    Livewire::actingAs($this->admin)
        ->test(RombelIndex::class)
        ->assertSee('Kenaikan Kelas');

    Livewire::actingAs($this->viewer)
        ->test(RombelIndex::class)
        ->assertDontSee('Kenaikan Kelas');
});

it('route kenaikan di web.php memakai permission kelas.promote, bukan kelas.kelola', function (): void {
    $routes = file_get_contents(base_path('routes/web.php'));

    expect($routes)->toContain("->middleware('permission:kelas.promote')")
        ->and($routes)->not->toContain('kelas.kelola');
});
