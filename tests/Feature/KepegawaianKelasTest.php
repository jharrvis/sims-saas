<?php

declare(strict_types=1);

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
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Keg', 'slug' => 'keg-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->ta = AcademicYear::create([
        'nama' => '2026/2027', 'semester' => 'ganjil', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);

    $this->admin = User::create([
        'username' => 'kegadmin', 'name' => 'Keg Admin', 'phone' => '628880000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('admin bisa CRUD guru, guru-mapel tidak bisa create guru', function (): void {
    $guruUser = User::create(['username' => 'keggr', 'name' => 'Keg Gr', 'phone' => '628880000002']);
    Membership::create(['user_id' => $guruUser->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $guruUser->assignRole('guru-mapel');

    $this->actingAs($this->admin)->postJson('/api/v1/guru', [
        'nama' => 'Drs. Hadi', 'jenis' => 'guru_mapel', 'status_kepegawaian' => 'pns',
        'nip_nuptk' => '197001011999031001',
    ])->assertCreated();

    $this->actingAs($guruUser)->postJson('/api/v1/guru', [
        'nama' => 'X', 'jenis' => 'tendik', 'status_kepegawaian' => 'honorer',
    ])->assertForbidden();

    $guru = Employee::first();
    $this->actingAs($this->admin)->deleteJson('/api/v1/guru/'.$guru->id)->assertOk();
    expect(Employee::withTrashed()->find($guru->id)->trashed())->toBeTrue();
});

it('admin bisa CRUD rombel dengan wali kelas', function (): void {
    $wali = Employee::create([
        'nama' => 'Bu Wali', 'jenis' => 'guru_kelas', 'status_kepegawaian' => 'honorer',
    ]);

    $res = $this->actingAs($this->admin)->postJson('/api/v1/kelas', [
        'academic_year_id' => $this->ta->id,
        'tingkat' => '7',
        'nama' => 'VII A',
        'kapasitas' => 32,
        'wali_kelas_id' => $wali->id,
    ])->assertCreated();

    $rombel = Rombel::find($res->json('id'));
    expect($rombel->waliKelas->nama)->toBe('Bu Wali');

    // Nama kelas unik per tahun ajaran di satu sekolah
    $this->actingAs($this->admin)->postJson('/api/v1/kelas', [
        'academic_year_id' => $this->ta->id,
        'tingkat' => '7',
        'nama' => 'VII A',
        'kapasitas' => 32,
    ])->assertStatus(422);

    // Rombel tenant lain tidak bisa diakses
    $schoolB = School::create([
        'nama' => 'Sekolah B', 'slug' => 'keg-b-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($schoolB->id);

    $userB = User::create(['username' => 'kegb', 'name' => 'Keg B', 'phone' => '628880000003']);
    Membership::create(['user_id' => $userB->id, 'school_id' => $schoolB->id, 'status' => 'aktif']);
    app(PermissionRegistrar::class)->setPermissionsTeamId($schoolB->id);
    $userB->assignRole('admin-sekolah');

    $this->actingAs($userB)->getJson('/api/v1/kelas/'.$rombel->id)->assertNotFound();
});

it('siswa bisa ditempatkan ke rombel, satu siswa satu rombel aktif per tahun', function (): void {
    $siswa = Student::create(['nis' => '5001', 'nama' => 'Siswa Rombel', 'jk' => 'L']);

    $rombelA = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '7', 'nama' => 'VII A', 'kapasitas' => 32,
    ]);
    $rombelB = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '7', 'nama' => 'VII B', 'kapasitas' => 32,
    ]);

    // Tempatkan ke VII A
    $rombelA->students()->attach($siswa->id, [
        'academic_year_id' => $this->ta->id, 'status' => 'aktif',
    ]);

    expect($rombelA->students()->wherePivot('status', 'aktif')->count())->toBe(1);

    // Pindah ke VII B: penempatan lama jadi 'pindah', riwayat tersimpan
    DB::table('class_student')
        ->where('student_id', $siswa->id)
        ->where('academic_year_id', $this->ta->id)
        ->update(['status' => 'pindah']);
    $rombelB->students()->attach($siswa->id, [
        'academic_year_id' => $this->ta->id, 'status' => 'aktif',
    ]);

    expect($rombelB->students()->wherePivot('status', 'aktif')->count())->toBe(1)
        ->and($rombelA->students()->wherePivot('status', 'aktif')->count())->toBe(0)
        ->and(DB::table('class_student')->where('student_id', $siswa->id)->count())->toBe(2);
});
