<?php

declare(strict_types=1);

use App\Livewire\Broadcast\BroadcastForm;
use App\Livewire\Broadcast\BroadcastIndex;
use App\Models\Broadcast;
use App\Models\Employee;
use App\Models\Membership;
use App\Models\Module;
use App\Models\School;
use App\Models\Student;
use App\Models\TenantModule;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Broadcast', 'slug' => 'bc-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'broadcast')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'bcadmin', 'name' => 'BC Admin', 'phone' => '628650000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    Student::create(['nis' => '9501', 'nama' => 'Siswa Ada WA', 'jk' => 'L', 'status' => 'aktif', 'phone' => '628110000001']);
    Student::create(['nis' => '9502', 'nama' => 'Siswa Tanpa WA', 'jk' => 'P', 'status' => 'aktif']);
    Employee::create(['nama' => 'Guru Ada WA', 'jenis' => 'guru_mapel', 'status_kepegawaian' => 'honorer', 'phone' => '628120000001']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul broadcast nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/broadcast')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('broadcast tanpa jadwal berstatus draf; dengan jadwal berstatus terjadwal', function (): void {
    $this->actingAs($this->admin)->postJson('/api/v1/broadcast', [
        'judul' => 'Info Draf', 'pesan' => 'Isi', 'channel' => 'notifikasi', 'target_type' => 'semua',
    ])->assertCreated()->assertJsonPath('status', 'draf');

    $this->actingAs($this->admin)->postJson('/api/v1/broadcast', [
        'judul' => 'Info Terjadwal', 'pesan' => 'Isi', 'channel' => 'whatsapp',
        'target_type' => 'siswa', 'dijadwalkan_pada' => now()->addHour()->format('Y-m-d H:i:s'),
    ])->assertCreated()->assertJsonPath('status', 'terjadwal');
});

it('kirim broadcast mencatat log; penerima tanpa kontak gagal', function (): void {
    $broadcast = Broadcast::create([
        'judul' => 'Pengumuman', 'pesan' => 'Besok libur.', 'channel' => 'whatsapp',
        'target_type' => 'siswa', 'dibuat_oleh' => $this->admin->id,
    ]);

    $res = $this->actingAs($this->admin)->postJson("/api/v1/broadcast/{$broadcast->id}/kirim")
        ->assertOk();

    expect($res->json('message'))->toContain('1 penerima')
        ->and($broadcast->fresh()->status)->toBe('terkirim')
        ->and($broadcast->logs()->where('status', 'terkirim')->count())->toBe(1)
        ->and($broadcast->logs()->where('status', 'gagal')->count())->toBe(1);
});

it('scheduler mengirim broadcast yang sudah jatuh tempo', function (): void {
    Broadcast::create([
        'judul' => 'Terjadwal', 'pesan' => 'Isi', 'channel' => 'notifikasi',
        'target_type' => 'guru', 'status' => 'terjadwal',
        'dijadwalkan_pada' => now()->subMinute(), 'dibuat_oleh' => $this->admin->id,
    ]);

    Artisan::call('broadcast:proses');

    expect(
        Broadcast::where('judul', 'Terjadwal')->firstOrFail()->status
    )->toBe('terkirim');
});

it('scheduler memproses broadcast tanpa konteks tenant (dari console)', function (): void {
    Broadcast::create([
        'judul' => 'Terjadwal konsol', 'pesan' => 'Isi', 'channel' => 'notifikasi',
        'target_type' => 'guru', 'status' => 'terjadwal',
        'dijadwalkan_pada' => now()->subMinute(), 'dibuat_oleh' => $this->admin->id,
    ]);

    // Simulasi scheduler console: TIDAK ada tenant context aktif.
    app(TenantContext::class)->clear();

    Artisan::call('broadcast:proses');

    // Pulihkan konteks untuk assertion.
    app(TenantContext::class)->set($this->school);

    expect(Broadcast::where('judul', 'Terjadwal konsol')->firstOrFail()->status)
        ->toBe('terkirim');
});

it('scheduler memproses broadcast terjadwal dari semua sekolah', function (): void {
    $other = School::create([
        'nama' => 'Sekolah Lain B', 'slug' => 'lainb-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    Broadcast::create([
        'judul' => 'BC Sekolah A', 'pesan' => 'Isi', 'channel' => 'notifikasi',
        'target_type' => 'guru', 'status' => 'terjadwal',
        'dijadwalkan_pada' => now()->subMinute(), 'dibuat_oleh' => $this->admin->id,
    ]);

    app(TenantContext::class)->set($other);
    Employee::create([
        'nama' => 'Guru B', 'jenis' => 'guru_mapel', 'status_kepegawaian' => 'honorer',
        'phone' => '628120000009',
    ]);
    Broadcast::create([
        'judul' => 'BC Sekolah B', 'pesan' => 'Isi', 'channel' => 'notifikasi',
        'target_type' => 'guru', 'status' => 'terjadwal',
        'dijadwalkan_pada' => now()->subMinute(), 'dibuat_oleh' => $this->admin->id,
    ]);

    app(TenantContext::class)->clear();

    Artisan::call('broadcast:proses');

    app(TenantContext::class)->set($this->school);
    expect(Broadcast::where('judul', 'BC Sekolah A')->firstOrFail()->status)->toBe('terkirim');

    app(TenantContext::class)->set($other);
    expect(Broadcast::where('judul', 'BC Sekolah B')->firstOrFail()->status)->toBe('terkirim');
});

it('broadcast terisolasi per sekolah', function (): void {
    Broadcast::create([
        'judul' => 'BC A', 'pesan' => 'Isi', 'channel' => 'notifikasi',
        'target_type' => 'semua', 'dibuat_oleh' => $this->admin->id,
    ]);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain8-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(Broadcast::count())->toBe(0);
});

it('broadcast memakai struktur design system management (Batch 7)', function (): void {
    Livewire::actingAs($this->admin)->test(BroadcastIndex::class)
        ->assertSee('Broadcast')
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(BroadcastForm::class)
        ->assertSee('Buat Broadcast')
        ->assertSeeHtml('management-form-card')
        ->assertDontSee('max-w-3xl');
});
