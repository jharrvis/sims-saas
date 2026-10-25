<?php

declare(strict_types=1);

use App\Livewire\Lms\AssignmentForm;
use App\Livewire\Lms\AssignmentIndex;
use App\Livewire\Lms\AssignmentShow;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Membership;
use App\Models\Module;
use App\Models\Rombel;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TenantModule;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah LMS', 'slug' => 'lms-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'lms_mini')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'lmsadmin', 'name' => 'LMS Admin', 'phone' => '628640000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $ta = AcademicYear::create([
        'nama' => '2026/2027', 'semester' => 'ganjil', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);

    $this->rombel = Rombel::create([
        'academic_year_id' => $ta->id, 'tingkat' => '10', 'nama' => 'X-1', 'kapasitas' => 36,
    ]);
    $this->mapel = Subject::create(['kode' => 'MTK', 'nama' => 'Matematika', 'kelompok' => 'wajib']);
    $this->siswa = Student::create(['nis' => '9601', 'nama' => 'Siswa LMS', 'jk' => 'L', 'status' => 'aktif']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul lms nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/lms/tugas')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('siswa mengumpulkan tugas; guru memberi nilai', function (): void {
    $tugas = $this->actingAs($this->admin)->postJson('/api/v1/lms/tugas', [
        'subject_id' => $this->mapel->id,
        'rombel_id' => $this->rombel->id,
        'judul' => 'Latihan Bab 1',
        'deadline' => now()->addDays(3)->format('Y-m-d H:i:s'),
    ])->assertCreated()->json('id');

    $submissionId = $this->actingAs($this->admin)->postJson("/api/v1/lms/tugas/{$tugas}/kumpul", [
        'student_id' => $this->siswa->id,
        'jawaban' => 'Jawaban saya.',
    ])->assertCreated()->json('id');

    // kumpul ulang menimpa, tidak duplikat
    $this->actingAs($this->admin)->postJson("/api/v1/lms/tugas/{$tugas}/kumpul", [
        'student_id' => $this->siswa->id,
        'jawaban' => 'Jawaban revisi.',
    ])->assertCreated();

    expect(AssignmentSubmission::where('assignment_id', $tugas)->count())->toBe(1);

    $this->actingAs($this->admin)->postJson("/api/v1/lms/pengumpulan/{$submissionId}/nilai", [
        'nilai' => 85.5, 'feedback' => 'Bagus.',
    ])->assertOk();

    expect((float) $this->actingAs($this->admin)->getJson("/api/v1/lms/tugas/{$tugas}")
        ->json('submissions.0.nilai'))->toBe(85.5);

    expect(
        AssignmentSubmission::find($submissionId)?->jawaban
    )->toBe('Jawaban revisi.');
});

it('pengumpulan setelah deadline ditandai terlambat', function (): void {
    $tugas = Assignment::create([
        'subject_id' => $this->mapel->id, 'rombel_id' => $this->rombel->id,
        'judul' => 'Tugas Lama', 'deadline' => now()->subHour(),
        'dibuat_oleh' => $this->admin->id,
    ]);

    $submission = AssignmentSubmission::create([
        'assignment_id' => $tugas->id, 'student_id' => $this->siswa->id,
        'jawaban' => 'Telat.', 'tgl_kumpul' => now(),
    ]);

    expect($submission->isTerlambat())->toBeTrue();
});

it('nilai di luar rentang 0-100 ditolak', function (): void {
    $tugas = Assignment::create([
        'subject_id' => $this->mapel->id, 'rombel_id' => $this->rombel->id,
        'judul' => 'Tugas', 'deadline' => now()->addDay(),
        'dibuat_oleh' => $this->admin->id,
    ]);
    $submission = AssignmentSubmission::create([
        'assignment_id' => $tugas->id, 'student_id' => $this->siswa->id,
        'jawaban' => 'Jawaban.', 'tgl_kumpul' => now(),
    ]);

    $this->actingAs($this->admin)->postJson("/api/v1/lms/pengumpulan/{$submission->id}/nilai", [
        'nilai' => 150,
    ])->assertUnprocessable();
});

it('tugas terisolasi per sekolah', function (): void {
    Assignment::create([
        'subject_id' => $this->mapel->id, 'rombel_id' => $this->rombel->id,
        'judul' => 'Tugas A', 'deadline' => now()->addDay(),
        'dibuat_oleh' => $this->admin->id,
    ]);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain7-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(Assignment::count())->toBe(0)
        ->and(AssignmentSubmission::count())->toBe(0);
});

it('tugas, form tugas, dan detail tugas memakai design system', function (): void {
    Livewire::actingAs($this->admin)->test(AssignmentIndex::class)
        ->assertSee('Tugas')
        ->assertSee('management-page-header')
        ->assertSee('data-table')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(AssignmentForm::class)
        ->assertSee('Buat Tugas')
        ->assertSee('management-form-card')
        ->assertDontSee('max-w-3xl')
        ->assertDontSee('<i data-lucide');

    $tugas = Assignment::create([
        'subject_id' => $this->mapel->id, 'rombel_id' => $this->rombel->id,
        'judul' => 'Tugas Desain', 'deadline' => now()->addDay(),
        'dibuat_oleh' => $this->admin->id,
    ]);

    Livewire::actingAs($this->admin)->test(AssignmentShow::class, ['assignment' => $tugas])
        ->assertSee('Tugas Desain')
        ->assertSee('Pengumpulan')
        ->assertDontSee('<i data-lucide');
});
