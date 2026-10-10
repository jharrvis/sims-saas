<?php

declare(strict_types=1);

use App\Domains\Akademik\Services\GradeCalculationService;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Membership;
use App\Models\Rombel;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Nilai', 'slug' => 'nilai-'.uniqid(), 'jenjang' => 'SMA',
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

    $this->semester = Semester::create([
        'academic_year_id' => $ta->id, 'nama' => 'ganjil', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2026-12-31',
    ]);

    $this->admin = User::create([
        'username' => 'nilaiadmin', 'name' => 'Nilai Admin', 'phone' => '628770000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->rombel = Rombel::create([
        'academic_year_id' => $ta->id, 'tingkat' => '10', 'nama' => 'X-1', 'kapasitas' => 36,
    ]);

    $this->mapel = Subject::create([
        'kode' => 'MTK', 'nama' => 'Matematika', 'kelompok' => 'wajib', 'kkm_default' => 75,
    ]);

    // Komponen default: Tugas 30%, UH 30%, PTS 20%, PAS 20%
    $this->komponen = collect([
        ['Tugas', 30], ['UH', 30], ['PTS', 20], ['PAS', 20],
    ])->map(fn ($c, $i) => GradeComponent::create([
        'nama' => $c[0], 'bobot' => $c[1], 'urutan' => $i,
    ]));

    $this->siswa = Student::create(['nis' => '7001', 'nama' => 'Siswa Nilai', 'jk' => 'L']);
    $this->rombel->students()->attach($this->siswa->id, [
        'academic_year_id' => $ta->id, 'status' => 'aktif',
    ]);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('nilai tersimpan dan nilai akhir dihitung berbobot', function (): void {
    $ids = $this->komponen->pluck('id', 'nama');

    $this->actingAs($this->admin)->postJson('/api/v1/nilai', [
        'subject_id' => $this->mapel->id,
        'grade_component_id' => $ids['Tugas'],
        'semester_id' => $this->semester->id,
        'records' => [['student_id' => $this->siswa->id, 'nilai' => 80]],
    ])->assertOk();

    $this->actingAs($this->admin)->postJson('/api/v1/nilai', [
        'subject_id' => $this->mapel->id,
        'grade_component_id' => $ids['UH'],
        'semester_id' => $this->semester->id,
        'records' => [['student_id' => $this->siswa->id, 'nilai' => 90]],
    ])->assertOk();

    $this->actingAs($this->admin)->postJson('/api/v1/nilai', [
        'subject_id' => $this->mapel->id,
        'grade_component_id' => $ids['PTS'],
        'semester_id' => $this->semester->id,
        'records' => [['student_id' => $this->siswa->id, 'nilai' => 70]],
    ])->assertOk();

    $this->actingAs($this->admin)->postJson('/api/v1/nilai', [
        'subject_id' => $this->mapel->id,
        'grade_component_id' => $ids['PAS'],
        'semester_id' => $this->semester->id,
        'records' => [['student_id' => $this->siswa->id, 'nilai' => 100]],
    ])->assertOk();

    // 80*0.3 + 90*0.3 + 70*0.2 + 100*0.2 = 24+27+14+20 = 85
    $na = app(GradeCalculationService::class)->finalGrade(
        $this->siswa->id, $this->mapel->id, $this->semester->id
    );

    expect($na)->toBe(85.0);

    // Nilai di luar 0-100 ditolak
    $this->actingAs($this->admin)->postJson('/api/v1/nilai', [
        'subject_id' => $this->mapel->id,
        'grade_component_id' => $ids['Tugas'],
        'semester_id' => $this->semester->id,
        'records' => [['student_id' => $this->siswa->id, 'nilai' => 150]],
    ])->assertStatus(422);
});

it('nilai hanya tampil di PWA setelah dirilis (FR-707)', function (): void {
    $ids = $this->komponen->pluck('id', 'nama');

    Grade::create([
        'student_id' => $this->siswa->id,
        'subject_id' => $this->mapel->id,
        'grade_component_id' => $ids['Tugas'],
        'semester_id' => $this->semester->id,
        'nilai' => 80,
    ]);

    $url = '/api/v1/nilai/siswa?student_id='.$this->siswa->id.'&semester_id='.$this->semester->id;

    // Sebelum rilis → tidak tampil
    $this->actingAs($this->admin)->getJson($url)
        ->assertOk()
        ->assertJsonPath('released', false);

    // Rilis
    $this->actingAs($this->admin)->postJson('/api/v1/nilai/rilis', [
        'rombel_id' => $this->rombel->id,
        'semester_id' => $this->semester->id,
    ])->assertCreated();

    // Setelah rilis → tampil
    $res = $this->actingAs($this->admin)->getJson($url)->assertOk();
    expect($res->json('released'))->toBeTrue();
    expect((float) $res->json('rata_rata'))->toBe(80.0);
});

it('guru-mapel tidak bisa input nilai tanpa permission, admin bisa', function (): void {
    $guruUser = User::create(['username' => 'nilaigr', 'name' => 'Nilai Gr', 'phone' => '628770000002']);
    Membership::create(['user_id' => $guruUser->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    // tanpa role → tidak punya nilai.input
    $this->actingAs($guruUser)->postJson('/api/v1/nilai', [
        'subject_id' => $this->mapel->id,
        'grade_component_id' => $this->komponen->first()->id,
        'semester_id' => $this->semester->id,
        'records' => [['student_id' => $this->siswa->id, 'nilai' => 80]],
    ])->assertForbidden();
});

it('leger menghitung nilai akhir per siswa dalam rombel', function (): void {
    $s2 = Student::create(['nis' => '7002', 'nama' => 'Siswa Dua', 'jk' => 'P']);
    $this->rombel->students()->attach($s2->id, [
        'academic_year_id' => $this->semester->academic_year_id, 'status' => 'aktif',
    ]);

    $ids = $this->komponen->pluck('id', 'nama');

    foreach ([$this->siswa->id => 80, $s2->id => 60] as $sid => $nilai) {
        Grade::create([
            'student_id' => $sid,
            'subject_id' => $this->mapel->id,
            'grade_component_id' => $ids['Tugas'],
            'semester_id' => $this->semester->id,
            'nilai' => $nilai,
        ]);
    }

    $res = $this->actingAs($this->admin)->getJson(
        '/api/v1/nilai/leger?rombel_id='.$this->rombel->id
        .'&subject_id='.$this->mapel->id
        .'&semester_id='.$this->semester->id
    )->assertOk();

    $data = collect($res->json('data'));
    expect($data->count())->toBe(2);
    // Hanya Tugas terisi (bobot 30%) → normalisasi ke 100 → 80 dan 60
    expect((float) $data->firstWhere('student_id', $this->siswa->id)['nilai_akhir'])->toBe(80.0);
    expect((float) $data->firstWhere('student_id', $s2->id)['nilai_akhir'])->toBe(60.0);
});
