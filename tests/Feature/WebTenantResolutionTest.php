<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantResolved;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\SetSpatieTeam;
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
use Illuminate\Auth\SessionGuard;
use Spatie\Permission\PermissionRegistrar;

/**
 * @return array{0:School, 1:User}
 */
function siapkanSekolahWeb(string $slug): array
{
    $school = School::create([
        'nama' => 'Sekolah '.$slug, 'slug' => $slug, 'jenjang' => 'SD',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($school->id);

    $user = User::create([
        'username' => 'web'.$slug, 'name' => 'User '.$slug,
        'phone' => '628'.random_int(1000000000, 1999999999),
    ]);
    Membership::create(['user_id' => $user->id, 'school_id' => $school->id, 'status' => 'aktif']);

    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
    $user->assignRole('superadmin');
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);

    return [$school, $user];
}

beforeEach(function (): void {
    $this->withoutVite();
});

/**
 * Kunci session guard web: login_web_<sha1(SessionGuard::class)>.
 * Mensimulasikan login web yang sesungguhnya — user hanya tercatat di
 * session (tidak di-set langsung ke guard seperti actingAs), sehingga
 * middleware harus me-resolve user dari session yang sudah dimulai.
 *
 * @return array<string, int>
 */
function sesiLoginWeb(User $user): array
{
    return ['login_web_'.sha1(SessionGuard::class) => $user->id];
}

it('tenant ter-resolve dari membership pada request web nyata', function (): void {
    [$school, $user] = siapkanSekolahWeb('webresolusi');

    // SENGAJA tidak memanggil TenantContext::set — biarkan middleware bekerja.
    $this->withSession(sesiLoginWeb($user))->get('/')->assertOk();

    expect(app(TenantContext::class)->schoolId())->toBe($school->id);
});

it('operator platform tidak mewarisi konteks tenant dari membership sekolah', function (): void {
    [$school] = siapkanSekolahWeb('webplatform');
    $platform = User::create([
        'username' => 'platform-web',
        'name' => 'Operator Platform',
        'phone' => '6281299999999',
        'is_platform' => true,
    ]);
    Membership::create([
        'user_id' => $platform->id,
        'school_id' => $school->id,
        'status' => 'aktif',
    ]);
    app(TenantContext::class)->set($school);

    $this->actingAs($platform)->get('/platform/login');

    expect(app(TenantContext::class)->schoolId())->toBeNull();
});

it('menu sekolah tampil sesuai permission setelah tenant ter-resolve', function (): void {
    [$school, $user] = siapkanSekolahWeb('webmenu');

    $this->withSession(sesiLoginWeb($user))
        ->get('/')
        ->assertOk()
        ->assertSee('Data Siswa')
        ->assertSee('Data Guru');
});

it('urutan middleware: resolusi tenant di grup web & api, bukan global', function (): void {
    // Regresi: sebagai global middleware, ResolveTenant berjalan sebelum
    // session dimulai sehingga $request->user() selalu null dan fallback
    // membership tidak pernah bekerja di domain pusat.
    $groups = app('router')->getMiddlewareGroups();

    expect($groups['web'])->toContain(ResolveTenant::class)
        ->and($groups['web'])->toContain(SetSpatieTeam::class)
        ->and($groups['api'])->toContain(EnsureTenantResolved::class);
});

/**
 * Membuat data lintas model (guru, siswa, rombel) lalu MENGHAPUS konteks
 * tenant. Request berikutnya hanya bisa sukses bila middleware ResolveTenant
 * berjalan SEBELUM SubstituteBindings — jika tidak, route model binding
 * melempar TenantContextMissing (HTTP 500).
 *
 * @return array{0: School, 1: User, 2: array{employee: int, student: int, rombel: int}}
 */
function siapkanDataModelBound(string $slug): array
{
    [$school, $user] = siapkanSekolahWeb($slug);

    app(TenantContext::class)->set($school);

    $tahun = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);

    $employee = Employee::create([
        'nip_nuptk' => '900'.substr($slug, -3), 'nama' => 'Guru '.$slug, 'jenis' => 'guru_mapel',
        'status_kepegawaian' => 'pns', 'phone' => '628130000001', 'status_aktif' => true,
    ]);

    $student = Student::create([
        'nis' => '900'.substr($slug, -3), 'nama' => 'Siswa '.$slug, 'jk' => 'L', 'status' => 'aktif',
    ]);

    $rombel = Rombel::create([
        'nama' => 'VII '.strtoupper(substr($slug, -2)), 'academic_year_id' => $tahun->id, 'tingkat' => '7',
    ]);

    // Wajib: konteks yang dibuat test tidak boleh terbawa ke request, agar
    // resolusi tenant benar-benar diuji pada middleware.
    app(TenantContext::class)->clear();

    return [$school, $user, [
        'employee' => $employee->id,
        'student' => $student->id,
        'rombel' => $rombel->id,
    ]];
}

it('route model-bound guru mengembalikan 200 tanpa konteks tenant yang di-set test', function (): void {
    [, $user, $ids] = siapkanDataModelBound('mdetail');

    $this->withSession(sesiLoginWeb($user))
        ->get('/guru/'.$ids['employee'])
        ->assertOk();
});

it('route form ubah guru mengembalikan 200 tanpa konteks tenant yang di-set test', function (): void {
    [, $user, $ids] = siapkanDataModelBound('mubah');

    $this->withSession(sesiLoginWeb($user))
        ->get('/guru/'.$ids['employee'].'/ubah')
        ->assertOk();
});

it('route model-bound rombel mengembalikan 200', function (): void {
    [, $user, $ids] = siapkanDataModelBound('msiswa');

    $this->withSession(sesiLoginWeb($user))->get('/kelas/'.$ids['rombel'])->assertOk();
});

it('route model-bound siswa mengembalikan 200', function (): void {
    [, $user, $ids] = siapkanDataModelBound('msiswadetail');

    $this->withSession(sesiLoginWeb($user))->get('/siswa/'.$ids['student'])->assertOk();
});

it('route index dan form tambah guru tetap 200 setelah urutan priority berubah', function (): void {
    [, $user] = siapkanDataModelBound('mindex');

    $this->withSession(sesiLoginWeb($user))->get('/guru')->assertOk();
    $this->withSession(sesiLoginWeb($user))->get('/guru/tambah')->assertOk();
});

it('record milik tenant lain menghasilkan 404 pada route model-bound', function (): void {
    [, $user] = siapkanDataModelBound('m404a');
    [$sekolahLain] = siapkanSekolahWeb('m404b');

    app(TenantContext::class)->set($sekolahLain);
    $guruLain = Employee::create([
        'nip_nuptk' => '777001', 'nama' => 'Guru Tenant Lain', 'jenis' => 'tendik',
        'status_kepegawaian' => 'honorer', 'phone' => '628130000002', 'status_aktif' => true,
    ]);
    app(TenantContext::class)->clear();

    $this->withSession(sesiLoginWeb($user))->get('/guru/'.$guruLain->id)->assertNotFound();
});

it('permission middleware tetap mengizinkan pemegang guru.view setelah urutan priority berubah', function (): void {
    [, $user] = siapkanDataModelBound('mizin');

    $this->withSession(sesiLoginWeb($user))->get('/guru')->assertOk();
});

it('permission middleware tetap menolak role tanpa guru.view setelah urutan priority berubah', function (): void {
    [, $user, $ids] = siapkanDataModelBound('mtolak');

    // Role guru-mapel tidak memiliki permission guru.* (RoleSeeder).
    $tanpaAkses = User::create([
        'username' => 'tanpaguru', 'name' => 'Tanpa Akses Guru', 'phone' => '628150000001',
    ]);
    Membership::create([
        'user_id' => $tanpaAkses->id, 'school_id' => $user->memberships()->first()->school_id,
        'status' => 'aktif',
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($user->memberships()->first()->school_id);
    $tanpaAkses->assignRole('guru-mapel');
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);

    $this->withSession(sesiLoginWeb($tanpaAkses))->get('/guru')->assertForbidden();
    $this->withSession(sesiLoginWeb($tanpaAkses))->get('/guru/'.$ids['employee'])->assertForbidden();
});

it('route publik login tidak terpengaruh perubahan urutan middleware', function (): void {
    $this->get('/login')->assertOk();
});
