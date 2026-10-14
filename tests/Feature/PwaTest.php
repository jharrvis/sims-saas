<?php

declare(strict_types=1);

use App\Domains\Identity\Contracts\WaSender;
use App\Domains\Pwa\Services\PwaContext;
use App\Domains\Pwa\Services\PwaIdentityService;
use App\Livewire\Pwa\Login as PwaLogin;
use App\Models\AcademicYear;
use App\Models\GradeRelease;
use App\Models\Guardian;
use App\Models\Membership;
use App\Models\OtpCode;
use App\Models\Rombel;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->withoutVite();
    $this->app->bind(WaSender::class, FakeWaSender::class);
    FakeWaSender::$lastCode = null;

    $this->school = School::create([
        'nama' => 'Sekolah PWA', 'slug' => 'pwa-'.uniqid(), 'jenjang' => 'SMA',
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
    $this->semester = Semester::create([
        'academic_year_id' => $this->ta->id, 'nama' => 'ganjil', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2026-12-31',
    ]);
    $this->rombel = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '10', 'nama' => 'X-1', 'kapasitas' => 36,
    ]);

    $this->siswa = Student::create([
        'nis' => '9001', 'nama' => 'Siswa PWA', 'jk' => 'L', 'status' => 'aktif',
        'phone' => '628111000001',
    ]);
    $this->siswa->rombels()->attach($this->rombel->id, [
        'academic_year_id' => $this->ta->id, 'status' => 'aktif',
    ]);

    $this->wali = Guardian::create([
        'nik' => '123', 'nama' => 'Wali PWA', 'hubungan' => 'ayah',
        'no_wa' => '628222000001',
    ]);
    $this->wali->students()->attach($this->siswa->id, ['hubungan' => 'ayah']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

function buatAkunPwa(string $phone): User
{
    return User::create([
        'username' => 'pwa-'.$phone, 'name' => 'Akun PWA', 'phone' => $phone,
    ]);
}

it('menautkan wali via no_wa + membership + peran orang-tua', function (): void {
    $user = buatAkunPwa('628222000001');

    app(PwaIdentityService::class)->linkByPhone($user);

    expect($user->fresh()->guardians)->toHaveCount(1)
        ->and(Membership::where('user_id', $user->id)->where('school_id', $this->school->id)->where('status', 'aktif')->exists())->toBeTrue();

    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
    expect($user->fresh()->hasRole('orang-tua'))->toBeTrue();
});

it('menautkan siswa via phone + peran siswa', function (): void {
    $user = buatAkunPwa('628111000001');

    app(PwaIdentityService::class)->linkByPhone($user);

    expect($user->fresh()->student_id)->toBe($this->siswa->id);

    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
    expect($user->fresh()->hasRole('siswa'))->toBeTrue();
});

it('login OTP PWA membuat akun dan menautkan otomatis', function (): void {
    Livewire::test(PwaLogin::class)
        ->set('phone', '08222000001')
        ->call('requestOtp')
        ->assertSet('step', 2)
        ->set('code', FakeWaSender::$lastCode)
        ->call('verify')
        ->assertRedirect(route('pwa.home'));

    $user = User::where('phone', '628222000001')->firstOrFail();

    expect($user->guardians)->toHaveCount(1);
    $this->assertAuthenticatedAs($user);
});

it('tamu diarahkan ke login PWA', function (): void {
    $this->get('/pwa')->assertRedirect(route('pwa.login'));
});

it('akun tak terhubung diarahkan ke halaman belum-terhubung', function (): void {
    $user = buatAkunPwa('628999000001');

    $this->actingAs($user)->get('/pwa')->assertRedirect(route('pwa.unlinked'));
    $this->actingAs($user)->get('/pwa/belum-terhubung')->assertOk();
});

it('orang tua melihat beranda dengan nama anak', function (): void {
    $user = buatAkunPwa('628222000001');
    app(PwaIdentityService::class)->linkByPhone($user);

    $this->actingAs($user)->get('/pwa')
        ->assertOk()
        ->assertSee('Siswa PWA');
});

it('nilai disembunyikan sebelum dirilis (FR-707) dan tampil setelah dirilis', function (): void {
    $user = buatAkunPwa('628111000001');
    app(PwaIdentityService::class)->linkByPhone($user);

    // Belum dirilis
    $this->actingAs($user)->get('/pwa/nilai')
        ->assertOk()
        ->assertSee('Nilai belum dirilis');

    // Setelah dirilis
    GradeRelease::create([
        'semester_id' => $this->semester->id,
        'rombel_id' => $this->rombel->id,
        'dirilis_pada' => now(),
        'dirilis_oleh' => $user->id,
    ]);

    $this->actingAs($user)->get('/pwa/nilai')
        ->assertOk()
        ->assertSee('Rata-rata')
        ->assertDontSee('Nilai belum dirilis');
});

it('orang tua bisa ganti anak; anak orang lain ditolak', function (): void {
    $anak2 = Student::create([
        'nis' => '9002', 'nama' => 'Anak Kedua', 'jk' => 'P', 'status' => 'aktif',
    ]);
    $this->wali->students()->attach($anak2->id, ['hubungan' => 'ayah']);

    $lain = Guardian::create([
        'nik' => '999', 'nama' => 'Wali Lain', 'hubungan' => 'ibu', 'no_wa' => '628333000001',
    ]);
    $anakLain = Student::create([
        'nis' => '9003', 'nama' => 'Anak Orang Lain', 'jk' => 'L', 'status' => 'aktif',
    ]);
    $lain->students()->attach($anakLain->id, ['hubungan' => 'ibu']);

    $user = buatAkunPwa('628222000001');
    app(PwaIdentityService::class)->linkByPhone($user);

    // Default: anak pertama
    $this->actingAs($user)->get('/pwa')->assertSee('Siswa PWA');

    // Ganti ke anak kedua
    $this->actingAs($user)->post('/pwa/ganti-anak', ['student_id' => $anak2->id])
        ->assertRedirect();
    $this->actingAs($user)->get('/pwa')->assertSee('Anak Kedua');

    // Anak milik orang lain → 403
    $this->actingAs($user)->post('/pwa/ganti-anak', ['student_id' => $anakLain->id])
        ->assertForbidden();
});

it('konteks tenant mengikuti sekolah anak aktif', function (): void {
    $user = buatAkunPwa('628222000001');
    app(PwaIdentityService::class)->linkByPhone($user);

    $this->actingAs($user)->get('/pwa')->assertOk();

    expect(app(TenantContext::class)->schoolId())->toBe($this->school->id);
    expect(app(PwaContext::class)->activeStudent()?->id)->toBe($this->siswa->id);
});

it('OTP PWA ditolak untuk nomor yang tidak terdaftar (tanpa WA terkirim)', function (): void {
    Livewire::test(PwaLogin::class)
        ->set('phone', '32423424234234324')
        ->call('requestOtp')
        ->assertSet('step', 1)
        ->assertSet('error', 'Nomor WhatsApp tidak terdaftar di sekolah manapun. Hubungi sekolah untuk mendaftarkan nomor Anda.');

    expect(FakeWaSender::$lastCode)->toBeNull()
        ->and(OtpCode::count())->toBe(0);
});

it('OTP PWA dikirim untuk nomor siswa yang terdaftar', function (): void {
    Livewire::test(PwaLogin::class)
        ->set('phone', '08111000001')
        ->call('requestOtp')
        ->assertSet('step', 2)
        ->assertSet('error', null);

    expect(FakeWaSender::$lastCode)->not->toBeNull()
        ->and(OtpCode::count())->toBe(1);
});
