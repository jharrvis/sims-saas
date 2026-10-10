<?php

declare(strict_types=1);

use App\Domains\Pwa\Services\QrLoginService;
use App\Livewire\Qr\QrCardIndex;
use App\Models\AcademicYear;
use App\Models\Membership;
use App\Models\QrLoginCard;
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
    $this->withoutVite();

    $this->school = School::create([
        'nama' => 'Sekolah QR', 'slug' => 'qr-'.uniqid(), 'jenjang' => 'SMP',
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
    $this->rombel = Rombel::create([
        'academic_year_id' => $ta->id, 'tingkat' => '7', 'nama' => 'VII-1', 'kapasitas' => 36,
    ]);

    $this->siswa = Student::create([
        'nis' => '7001', 'nama' => 'Siswa QR', 'jk' => 'L', 'status' => 'aktif',
    ]);
    $this->siswa->rombels()->attach($this->rombel->id, [
        'academic_year_id' => $ta->id, 'status' => 'aktif',
    ]);

    $this->admin = User::create([
        'username' => 'qradmin', 'name' => 'QR Admin', 'phone' => '628770000099',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('menerbitkan kartu dan menukarnya menjadi sesi (sekali pakai per token kartu: dapat dipakai ulang hingga dicabut)', function (): void {
    $qr = app(QrLoginService::class);

    $token = $qr->issueCard($this->siswa, $this->admin);

    expect(strlen($token))->toBe(32)
        ->and(QrLoginCard::where('student_id', $this->siswa->id)->where('aktif', true)->count())->toBe(1);

    $hasil = $qr->redeem($token);

    expect($hasil)->not->toBeNull()
        ->and($hasil['student']->id)->toBe($this->siswa->id)
        ->and($hasil['card_id'])->not->toBeNull();
});

it('penerbitan baru menonaktifkan kartu lama', function (): void {
    $qr = app(QrLoginService::class);

    $lama = $qr->issueCard($this->siswa, $this->admin);
    $baru = $qr->issueCard($this->siswa, $this->admin);

    expect($qr->redeem($lama))->toBeNull()
        ->and($qr->redeem($baru))->not->toBeNull();
});

it('menolak token yang tidak dikenal', function (): void {
    expect(app(QrLoginService::class)->redeem(str_repeat('a', 32)))->toBeNull();
});

it('tantangan berlaku 60 detik dan sekali pakai', function (): void {
    $qr = app(QrLoginService::class);

    $hasil = $qr->createChallenge($this->admin, $this->siswa);

    expect($hasil['expires_at']->diffInSeconds(now()))->toBeLessThanOrEqual(60);

    expect($qr->redeem($hasil['token'])['student']->id)->toBe($this->siswa->id);

    // Sekali pakai
    expect($qr->redeem($hasil['token']))->toBeNull();
});

it('tantangan kedaluwarsa ditolak', function (): void {
    $qr = app(QrLoginService::class);
    $hasil = $qr->createChallenge($this->admin, $this->siswa);

    $this->travel(2)->minutes();

    expect($qr->redeem($hasil['token']))->toBeNull();
});

it('kartu yang dicabut tidak bisa ditukar dan sesinya ikut gugur', function (): void {
    $qr = app(QrLoginService::class);
    $token = $qr->issueCard($this->siswa, $this->admin);

    expect($qr->revokeCard($this->siswa))->toBeTrue()
        ->and($qr->redeem($token))->toBeNull();
});

it('redeem via web membuat sesi terbatas 8 jam tanpa remember-me', function (): void {
    $token = app(QrLoginService::class)->issueCard($this->siswa, $this->admin);

    $res = $this->postJson('/pwa/qr/redeem', ['token' => $token]);

    $res->assertOk()->assertJson(['ok' => true]);
    $this->assertAuthenticated();

    $this->assertEquals(true, session('qr_limited'));
    expect(session('qr_expires_at') - time())->toBeLessThanOrEqual(8 * 3600 + 5);

    $this->get('/pwa')->assertOk()->assertSee('Siswa QR');
});

it('redeem via web tetap jalan sebagai tamu tanpa konteks tenant (regresi auto-link)', function (): void {
    $token = app(QrLoginService::class)->issueCard($this->siswa, $this->admin);

    // Simulasi produksi: tamu belum punya tenant sama sekali.
    app(TenantContext::class)->clear();

    $this->postJson('/pwa/qr/redeem', ['token' => $token])->assertOk();
    $this->assertAuthenticated();

    // End-to-end: tenant terkunci ke sekolah siswa setelah login.
    $this->get('/pwa')->assertOk()->assertSee('Siswa QR');
});

it('redeem via web menolak token salah', function (): void {
    $this->postJson('/pwa/qr/redeem', ['token' => str_repeat('b', 32)])
        ->assertStatus(422);
    $this->assertGuest();
});

it('sesi QR kedaluwarsa dipaksa logout', function (): void {
    $token = app(QrLoginService::class)->issueCard($this->siswa, $this->admin);
    $this->postJson('/pwa/qr/redeem', ['token' => $token])->assertOk();

    $this->travel(9)->hours();

    $this->get('/pwa')->assertRedirect(route('pwa.login'));
    $this->assertGuest();
});

it('kartu dicabut setelah login membuat sesi gugur', function (): void {
    $qr = app(QrLoginService::class);
    $token = $qr->issueCard($this->siswa, $this->admin);
    $this->postJson('/pwa/qr/redeem', ['token' => $token])->assertOk();

    $qr->revokeCard($this->siswa);

    $this->get('/pwa')->assertRedirect(route('pwa.login'));
    $this->assertGuest();
});

it('API redeem menerbitkan token sanctum 8 jam', function (): void {
    $token = app(QrLoginService::class)->issueCard($this->siswa, $this->admin);

    $res = $this->postJson('/api/v1/auth/qr/redeem', ['token' => $token]);

    $res->assertOk()->assertJsonPath('student.nama', 'Siswa QR');
    expect($res->json('token'))->not->toBeEmpty();

    $user = User::where('student_id', $this->siswa->id)->firstOrFail();
    $pat = $user->tokens()->where('name', 'qr-login')->firstOrFail();
    expect((int) round(abs($pat->expires_at->diffInHours(now()))))->toBe(8);
});

it('API challenge menolak tanpa izin qr.manage', function (): void {
    // Catatan: satu request Bearer per test — guard Sanctum meng-cache user
    // antar request dalam proses test yang sama.
    $biasa = User::create(['username' => 'biasa', 'name' => 'Biasa']);
    Membership::create(['user_id' => $biasa->id, 'school_id' => $this->school->id, 'status' => 'aktif']);

    $this->withHeader('Authorization', 'Bearer '.$biasa->createToken('t')->plainTextToken)
        ->postJson('/api/v1/auth/qr/challenge', ['student_id' => $this->siswa->id])
        ->assertForbidden();
});

it('API challenge dan revoke-card untuk pemegang izin qr.manage', function (): void {
    $api = $this->withHeader('Authorization', 'Bearer '.$this->admin->createToken('t')->plainTextToken);

    $res = $api->postJson('/api/v1/auth/qr/challenge', ['student_id' => $this->siswa->id]);
    $res->assertOk();
    expect(strlen($res->json('token')))->toBe(32);

    $token = app(QrLoginService::class)->issueCard($this->siswa, $this->admin);
    $api->postJson('/api/v1/auth/qr/revoke-card', ['student_id' => $this->siswa->id])
        ->assertOk()->assertJson(['revoked' => true]);

    expect(app(QrLoginService::class)->redeem($token))->toBeNull();
});

it('halaman kartu QR butuh izin qr.view', function (): void {
    $this->actingAs($this->admin)->get('/qr/kartu')->assertOk();

    $biasa = User::create(['username' => 'biasa2', 'name' => 'Biasa 2']);
    Membership::create(['user_id' => $biasa->id, 'school_id' => $this->school->id, 'status' => 'aktif']);

    $this->actingAs($biasa)->get('/qr/kartu')->assertForbidden();
});

it('admin menerbitkan kartu dari UI dan token tampil sekali', function (): void {
    Livewire::actingAs($this->admin)
        ->test(QrCardIndex::class)
        ->call('terbitkan', $this->siswa->id)
        ->assertSee('diterbitkan');

    expect(session('qr_fresh_tokens')[$this->siswa->id] ?? null)->not->toBeNull();
    expect(QrLoginCard::where('student_id', $this->siswa->id)->where('aktif', true)->count())->toBe(1);
});
