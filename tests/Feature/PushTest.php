<?php

declare(strict_types=1);

use App\Domains\Keuangan\Services\KeuanganService;
use App\Domains\Pwa\Jobs\SendPushNotification;
use App\Domains\Pwa\Services\PushService;
use App\Livewire\Akademik\AttendanceIndex;
use App\Livewire\Pengumuman\AnnouncementIndex;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\FeeType;
use App\Models\Guardian;
use App\Models\Membership;
use App\Models\PushSubscription;
use App\Models\Rombel;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->withoutVite();
    config(['push.enabled_in_tests' => true]);

    $this->school = School::create([
        'nama' => 'Sekolah Push', 'slug' => 'push-'.uniqid(), 'jenjang' => 'SMA',
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
        'nis' => '8001', 'nama' => 'Siswa Push', 'jk' => 'L', 'status' => 'aktif',
    ]);
    $this->siswa->rombels()->attach($this->rombel->id, [
        'academic_year_id' => $this->ta->id, 'status' => 'aktif',
    ]);

    $this->wali = Guardian::create([
        'nik' => '456', 'nama' => 'Wali Push', 'hubungan' => 'ayah', 'no_wa' => '628555000001',
    ]);
    $this->wali->students()->attach($this->siswa->id, ['hubungan' => 'ayah']);

    // Akun siswa & orang tua + langganan push masing-masing.
    $this->akunSiswa = User::create(['username' => 'push-siswa', 'name' => 'Siswa Push', 'student_id' => $this->siswa->id]);
    $this->akunOrtu = User::create(['username' => 'push-ortu', 'name' => 'Wali Push']);
    $this->akunOrtu->guardians()->attach($this->wali->id);

    foreach ([$this->akunSiswa, $this->akunOrtu] as $i => $u) {
        Membership::create(['user_id' => $u->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
        PushSubscription::create([
            'school_id' => $this->school->id,
            'user_id' => $u->id,
            'endpoint' => 'https://fcm.test/ep-'.$i.'-'.uniqid(),
            'p256dh' => 'p256dh-key',
            'auth' => 'auth-key',
        ]);
    }

    $this->admin = User::create(['username' => 'pushadmin', 'name' => 'Push Admin']);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('API subscribe menyimpan langganan dan idempoten untuk endpoint sama', function (): void {
    $token = $this->akunSiswa->createToken('t')->plainTextToken;
    $payload = [
        'endpoint' => 'https://fcm.test/ep-baru',
        'keys' => ['p256dh' => 'k1', 'auth' => 'a1'],
    ];

    $r1 = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/push/subscribe', $payload);
    $r1->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.endpoint_terdaftar', true);
    $id = $r1->json('data.subscription_id');

    // Idempoten → 200, id sama
    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/push/subscribe', $payload)
        ->assertOk()
        ->assertJsonPath('data.subscription_id', $id);

    expect(PushSubscription::where('endpoint', 'https://fcm.test/ep-baru')->count())->toBe(1);
});

it('API subscribe menolak tanpa kunci VAPID/konfigurasi (503)', function (): void {
    config(['push.enabled_in_tests' => false]);

    $token = $this->akunSiswa->createToken('t')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/push/subscribe', [
            'endpoint' => 'https://fcm.test/ep-x',
            'keys' => ['p256dh' => 'k', 'auth' => 'a'],
        ])
        ->assertStatus(503);
});

it('API unsubscribe menghapus dan 404 bila tidak ada', function (): void {
    $token = $this->akunOrtu->createToken('t2')->plainTextToken;
    $endpoint = PushSubscription::where('user_id', $this->akunOrtu->id)->value('endpoint');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/push/unsubscribe', ['endpoint' => $endpoint])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(PushSubscription::where('endpoint', $endpoint)->exists())->toBeFalse();

    // Lagi → 404 + kode Dok 03
    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/push/unsubscribe', ['endpoint' => $endpoint])
        ->assertNotFound()
        ->assertJsonPath('code', 'SUBSCRIPTION_TIDAK_DITEMUKAN');
});

it('unsubscribe milik user lain tidak menghapus', function (): void {
    $lain = User::create(['username' => 'push-lain', 'name' => 'Lain']);
    Membership::create(['user_id' => $lain->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $endpoint = PushSubscription::where('user_id', $this->akunSiswa->id)->value('endpoint');

    $token = $lain->createToken('t3')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/push/unsubscribe', ['endpoint' => $endpoint])
        ->assertNotFound();

    expect(PushSubscription::where('endpoint', $endpoint)->exists())->toBeTrue();
});

it('PWA web bisa subscribe/unsubscribe + vapid-key', function (): void {
    $this->actingAs($this->akunSiswa);

    $this->get('/pwa/push/vapid-key')->assertOk()
        ->assertJsonStructure(['key']);

    $this->postJson('/pwa/push/subscribe', [
        'endpoint' => 'https://fcm.test/ep-pwa',
        'keys' => ['p256dh' => 'k', 'auth' => 'a'],
    ])->assertCreated();

    $this->deleteJson('/pwa/push/unsubscribe', ['endpoint' => 'https://fcm.test/ep-pwa'])
        ->assertOk()->assertJson(['ok' => true]);
});

it('rilis nilai mengirim push ke siswa dan orang tua', function (): void {
    Queue::fake();

    app(PushService::class)->notifyGradeRelease($this->rombel, $this->semester);

    Queue::assertPushed(SendPushNotification::class, 2);
    Queue::assertPushed(SendPushNotification::class, fn ($job) => $job->payload['title'] === 'Nilai Telah Dirilis'
        && $job->payload['url'] === route('pwa.nilai'));
});

it('pengumuman semua dikirim ke siswa+ortu; target peran guru diabaikan', function (): void {
    Queue::fake();
    $push = app(PushService::class);

    $umum = Announcement::create([
        'judul' => 'Info Umum', 'isi' => 'Isi pengumuman', 'target_type' => 'semua', 'tayang_dari' => now()->toDateString(),
        'status' => 'tayang', 'dibuat_oleh' => $this->admin->id,
    ]);
    $push->notifyAnnouncement($umum);
    Queue::assertPushed(SendPushNotification::class, 2);

    Queue::fake();
    $guru = Announcement::create([
        'judul' => 'Rapat Guru', 'isi' => 'Khusus guru', 'target_type' => 'peran', 'tayang_dari' => now()->toDateString(),
        'target_data' => ['roles' => ['guru-mapel']], 'status' => 'tayang',
        'dibuat_oleh' => $this->admin->id,
    ]);
    $push->notifyAnnouncement($guru);
    Queue::assertNotPushed(SendPushNotification::class);
});

it('pengumuman target kelas hanya ke rombel tersebut', function (): void {
    Queue::fake();

    $rombel2 = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '10', 'nama' => 'X-2', 'kapasitas' => 36,
    ]);
    $siswa2 = Student::create(['nis' => '8002', 'nama' => 'Siswa Dua', 'jk' => 'P', 'status' => 'aktif']);
    $siswa2->rombels()->attach($rombel2->id, ['academic_year_id' => $this->ta->id, 'status' => 'aktif']);
    $akun2 = User::create(['username' => 'push-siswa2', 'name' => 'Siswa Dua', 'student_id' => $siswa2->id]);
    Membership::create(['user_id' => $akun2->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    PushSubscription::create([
        'school_id' => $this->school->id, 'user_id' => $akun2->id,
        'endpoint' => 'https://fcm.test/ep-x2', 'p256dh' => 'k', 'auth' => 'a',
    ]);

    $a = Announcement::create([
        'judul' => 'Khusus X-1', 'isi' => 'Isi', 'target_type' => 'kelas', 'tayang_dari' => now()->toDateString(),
        'target_data' => ['rombel_ids' => [$this->rombel->id]], 'status' => 'tayang',
        'dibuat_oleh' => $this->admin->id,
    ]);

    app(PushService::class)->notifyAnnouncement($a);

    // Hanya akun siswa X-1 + ortunya (2), bukan siswa X-2.
    Queue::assertPushed(SendPushNotification::class, 2);
});

it('publish pengumuman dari UI memicu push', function (): void {
    Queue::fake();

    $a = Announcement::create([
        'judul' => 'Libur', 'isi' => 'Libur nasional', 'target_type' => 'semua', 'tayang_dari' => now()->toDateString(),
        'status' => 'draf', 'dibuat_oleh' => $this->admin->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(AnnouncementIndex::class)
        ->call('publish', $a->id);

    expect($a->fresh()->status)->toBe('tayang');
    Queue::assertPushed(SendPushNotification::class, 2);
});

it('absensi alpa memberi tahu orang tua', function (): void {
    Queue::fake();

    Livewire::actingAs($this->admin)
        ->test(AttendanceIndex::class)
        ->set('rombelId', $this->rombel->id)
        ->set('tanggal', now()->toDateString())
        ->set('marks', [$this->siswa->id => 'A'])
        ->call('save');

    // Hanya ortu (1), bukan akun siswa.
    Queue::assertPushed(SendPushNotification::class, 1);
    Queue::assertPushed(SendPushNotification::class,
        fn ($job) => str_contains($job->payload['body'], 'alpa'));
});

it('generate tagihan memberi tahu orang tua', function (): void {
    Queue::fake();

    $fee = FeeType::create([
        'nama' => 'SPP', 'nominal' => 500000, 'periode' => 'bulanan', 'aktif' => true,
    ]);

    app(KeuanganService::class)
        ->generateInvoices($fee->id, 'Oktober 2026');

    Queue::assertPushed(SendPushNotification::class, 1);
    Queue::assertPushed(SendPushNotification::class,
        fn ($job) => $job->payload['title'] === 'Tagihan Baru');
});
