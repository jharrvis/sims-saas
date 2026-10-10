<?php

declare(strict_types=1);

use App\Domains\ERapor\Services\ERaporService;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Membership;
use App\Models\Module;
use App\Models\RaporNote;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TenantModule;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Rapor', 'slug' => 'erapor-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'e_rapor')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'eraporadmin', 'name' => 'E-Rapor Admin', 'phone' => '628590000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $ta = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);
    $this->semester = Semester::create([
        'academic_year_id' => $ta->id, 'nama' => 'ganjil', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2026-12-31',
    ]);

    $this->mapel = Subject::create([
        'kode' => 'MTK', 'nama' => 'Matematika', 'kelompok' => 'wajib', 'kkm_default' => 75,
    ]);
    $komponen = GradeComponent::create([
        'subject_id' => $this->mapel->id, 'nama' => 'UH', 'bobot' => 100, 'urutan' => 0,
    ]);

    $this->siswa = Student::create(['nis' => '9501', 'nama' => 'Siswa Rapor', 'jk' => 'L', 'status' => 'aktif']);
    Grade::create([
        'student_id' => $this->siswa->id, 'subject_id' => $this->mapel->id,
        'grade_component_id' => $komponen->id, 'semester_id' => $this->semester->id,
        'nilai' => 85, 'input_by' => $this->admin->id,
    ]);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul e_rapor nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)
        ->getJson("/api/v1/erapor/siswa/{$this->siswa->id}/semester/{$this->semester->id}")
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('rapor memuat nilai, predikat, dan rata-rata', function (): void {
    $data = $this->actingAs($this->admin)
        ->getJson("/api/v1/erapor/siswa/{$this->siswa->id}/semester/{$this->semester->id}")
        ->assertOk()
        ->json();

    expect($data['rata_rata'])->toEqual(85.0);
    expect($data['predikat_rata'])->toBe('B');
    $mtk = collect($data['mapel'])->firstWhere('kode', 'MTK');
    expect($mtk['nilai_akhir'])->toEqual(85.0);
    expect($mtk['predikat'])->toBe('B');
    expect($mtk['tuntas'])->toBeTrue();
});

it('catatan wali tersimpan dan terbaca di rapor', function (): void {
    $this->actingAs($this->admin)
        ->postJson("/api/v1/erapor/siswa/{$this->siswa->id}/semester/{$this->semester->id}/catatan", [
            'catatan_wali' => 'Tingkatkan konsistensi belajar.',
        ])
        ->assertCreated();

    expect(RaporNote::count())->toBe(1);

    $data = $this->actingAs($this->admin)
        ->getJson("/api/v1/erapor/siswa/{$this->siswa->id}/semester/{$this->semester->id}")
        ->assertOk()
        ->json();

    expect($data['catatan_wali'])->toBe('Tingkatkan konsistensi belajar.');
});

it('konversi predikat mengikuti rentang nilai', function (): void {
    $service = app(ERaporService::class);

    expect($service->predikat(95))->toBe('A');
    expect($service->predikat(85))->toBe('B');
    expect($service->predikat(75))->toBe('C');
    expect($service->predikat(60))->toBe('D');
});
