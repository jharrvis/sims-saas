<?php

declare(strict_types=1);

use App\Domains\Identity\Contracts\WaSender;
use App\Domains\Pwa\Services\PwaContext;
use App\Domains\Pwa\Services\PwaIdentityService;
use App\Livewire\Pwa\Login as PwaLogin;
use App\Livewire\Pwa\Profil as PwaProfil;
use App\Models\AcademicYear;
use App\Models\Announcement;
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

class PwaFakeWaSender implements WaSender
{
    public static ?string $lastCode = null;

    public function sendOtp(string $phone, string $code, string $schoolName): bool
    {
        self::$lastCode = $code;

        return true;
    }

    public function sendText(string $phone, string $message): bool
    {
        return true;
    }
}

beforeEach(function (): void {
    $this->withoutVite();
    $this->app->bind(WaSender::class, PwaFakeWaSender::class);
    PwaFakeWaSender::$lastCode = null;

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
        ->set('code', PwaFakeWaSender::$lastCode)
        ->call('verify')
        ->assertRedirect(route('pwa.home'));

    $user = User::where('phone', '628222000001')->firstOrFail();

    expect($user->guardians)->toHaveCount(1);
    $this->assertAuthenticatedAs($user);
});

it('tamu diarahkan ke login PWA', function (): void {
    $this->get('/pwa')->assertRedirect(route('pwa.login'));
});

it('menyajikan service worker dari route tanpa men-cache navigasi privat', function (): void {
    $response = $this->get(route('pwa.service-worker'))
        ->assertOk()
        ->assertHeader('Service-Worker-Allowed', '/pwa');

    expect($response->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store')
        ->not->toContain('public');

    expect(file_get_contents(public_path('pwa-assets/sw.js')))
        ->toContain("const isNavigation = request.mode === 'navigate';")
        ->toContain('fetch(request).catch(() => caches.match(OFFLINE_URL))')
        ->toContain("event.data?.type === 'SKIP_WAITING'");
});

it('login PWA menjelaskan identitas otomatis dan urutan metode masuk', function (): void {
    $this->get(route('pwa.login'))
        ->assertOk()
        ->assertSee('Akses otomatis sesuai akun')
        ->assertSeeInOrder(['OTP', 'Pindai QR', 'Password']);
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
        ->assertSee('Siswa PWA')
        ->assertSee('Kehadiran bulan ini')
        ->assertSee('Jadwal Hari Ini');
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

it('jadwal PWA menampilkan state kosong yang terarah', function (): void {
    $user = buatAkunPwa('628111000001');
    app(PwaIdentityService::class)->linkByPhone($user);

    $this->actingAs($user)->get(route('pwa.jadwal'))
        ->assertOk()
        ->assertSee('JADWAL MINGGUAN')
        ->assertSee('Belum ada jadwal');
});

it('absensi PWA menampilkan ringkasan dan state kosong', function (): void {
    $user = buatAkunPwa('628111000001');
    app(PwaIdentityService::class)->linkByPhone($user);

    $this->actingAs($user)->get(route('pwa.absensi'))
        ->assertOk()
        ->assertSee('REKAP BULANAN')
        ->assertSee('Belum ada data absensi');
});

it('tagihan PWA menampilkan ringkasan backend dan state kosong', function (): void {
    $user = buatAkunPwa('628111000001');
    app(PwaIdentityService::class)->linkByPhone($user);

    $this->actingAs($user)->get(route('pwa.tagihan'))
        ->assertOk()
        ->assertSee('Ringkasan keuangan')
        ->assertSee('Rp0')
        ->assertSee('Belum ada tagihan');
});

it('pengumuman PWA hanya menampilkan target siswa dan kelas aktif', function (): void {
    $user = buatAkunPwa('628111000001');
    app(PwaIdentityService::class)->linkByPhone($user);

    Announcement::create([
        'judul' => 'Informasi Umum', 'isi' => 'Untuk semua pengguna.', 'status' => 'tayang',
        'target_type' => 'semua', 'tayang_dari' => now()->toDateString(),
    ]);
    Announcement::create([
        'judul' => 'Khusus Siswa', 'isi' => 'Untuk peran siswa.', 'status' => 'tayang',
        'target_type' => 'peran', 'target_data' => ['roles' => ['siswa']], 'tayang_dari' => now()->toDateString(),
    ]);
    $guru = Announcement::create([
        'judul' => 'Rapat Guru', 'isi' => 'Tidak untuk siswa.', 'status' => 'tayang',
        'target_type' => 'peran', 'target_data' => ['roles' => ['guru-mapel']], 'tayang_dari' => now()->toDateString(),
    ]);
    Announcement::create([
        'judul' => 'Khusus X-1', 'isi' => 'Untuk kelas aktif.', 'status' => 'tayang',
        'target_type' => 'kelas', 'target_data' => ['rombel_ids' => [$this->rombel->id]], 'tayang_dari' => now()->toDateString(),
    ]);
    Announcement::create([
        'judul' => 'Khusus Kelas Lain', 'isi' => 'Tidak untuk kelas aktif.', 'status' => 'tayang',
        'target_type' => 'kelas', 'target_data' => ['rombel_ids' => [999999]], 'tayang_dari' => now()->toDateString(),
    ]);

    $this->actingAs($user)->get(route('pwa.pengumuman'))
        ->assertOk()
        ->assertSee('Informasi Umum')
        ->assertSee('Khusus Siswa')
        ->assertSee('Khusus X-1')
        ->assertDontSee('Rapat Guru')
        ->assertDontSee('Khusus Kelas Lain');

    $this->actingAs($user)->get(route('pwa.pengumuman.show', $guru))->assertNotFound();
});

it('pengumuman PWA meng-escape isi yang berasal dari sekolah', function (): void {
    $user = buatAkunPwa('628111000001');
    app(PwaIdentityService::class)->linkByPhone($user);
    $announcement = Announcement::create([
        'judul' => 'Pengumuman Aman',
        'isi' => '<script>alert("xss")</script>',
        'status' => 'tayang',
        'target_type' => 'semua',
        'tayang_dari' => now()->toDateString(),
    ]);

    app(TenantContext::class)->clear();

    $this->actingAs($user)->get(route('pwa.pengumuman.show', $announcement))
        ->assertOk()
        ->assertSee('&lt;script&gt;', false)
        ->assertDontSee('<script>', false);
});

it('profil PWA hanya dapat mencabut token milik akun sendiri', function (): void {
    $user = buatAkunPwa('628111000001');
    app(PwaIdentityService::class)->linkByPhone($user);
    $ownToken = $user->createToken('Ponsel pribadi')->accessToken;

    $otherUser = buatAkunPwa('628999000009');
    $otherToken = $otherUser->createToken('Perangkat lain')->accessToken;

    $this->actingAs($user);
    app(PwaContext::class)->setUser($user);

    Livewire::test(PwaProfil::class)
        ->assertSee('Ponsel pribadi')
        ->assertSeeHtml('data-pwa-push')
        ->assertSeeHtml('data-pwa-install')
        ->call('revokeToken', $otherToken->id)
        ->call('revokeToken', $ownToken->id);

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $ownToken->id]);
    $this->assertDatabaseHas('personal_access_tokens', ['id' => $otherToken->id]);
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

    expect(PwaFakeWaSender::$lastCode)->toBeNull()
        ->and(OtpCode::count())->toBe(0);
});

it('OTP PWA dikirim untuk nomor siswa yang terdaftar', function (): void {
    Livewire::test(PwaLogin::class)
        ->set('phone', '08111000001')
        ->call('requestOtp')
        ->assertSet('step', 2)
        ->assertSet('error', null);

    expect(PwaFakeWaSender::$lastCode)->not->toBeNull()
        ->and(OtpCode::count())->toBe(1);
});
