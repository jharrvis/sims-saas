<?php

declare(strict_types=1);

use App\Domains\Platform\Services\QuotaService;
use App\Domains\Platform\Services\TenantProvisioningService;
use App\Livewire\Onboarding\DaftarSekolah;
use App\Models\Membership;
use App\Models\Module;
use App\Models\School;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\TenantModule;
use App\Models\TenantQuotaOverride;
use App\Models\User;
use App\Scopes\SchoolScope;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed(PermissionSeeder::class);
    $this->seed(ModuleSeeder::class);
    $this->seed(PlanSeeder::class);
});

/**
 * @param  array<string, mixed>  $over
 * @return array<string, mixed>
 */
function daftarData(array $over = []): array
{
    return array_merge([
        'nama' => 'SDN Trial '.random_int(100, 999),
        'jenjang' => 'SD',
        'npsn' => (string) random_int(20000000, 29999999),
        'admin_nama' => 'Budi Admin',
        'admin_phone' => '62899'.random_int(1000000, 9999999),
    ], $over);
}

it('halaman daftar bisa diakses tamu', function (): void {
    $this->get('/daftar')->assertOk()->assertSee('Daftar Trial Gratis');
});

it('pendaftaran membuat sekolah trial + admin + modul inti', function (): void {
    $data = daftarData();

    Livewire::test(DaftarSekolah::class)
        ->set('nama', $data['nama'])
        ->set('jenjang', $data['jenjang'])
        ->set('npsn', $data['npsn'])
        ->set('admin_nama', $data['admin_nama'])
        ->set('admin_phone', $data['admin_phone'])
        ->call('daftar')
        ->assertHasNoErrors()
        ->assertSet('selesai', true);

    $school = School::where('npsn', $data['npsn'])->firstOrFail();

    expect($school->status_langganan)->toBe('trialing')
        ->and($school->status)->toBe('aktif');

    // Langganan trial 30 hari paket Basic.
    $sub = Subscription::withoutGlobalScope(SchoolScope::class)
        ->where('school_id', $school->id)->firstOrFail();

    expect($sub->status)->toBe('trial')
        ->and($sub->plan->slug)->toBe('basic');

    // Admin + membership + peran superadmin.
    $user = User::where('phone', $data['admin_phone'])->firstOrFail();

    expect(
        Membership::where('user_id', $user->id)->where('school_id', $school->id)->exists()
    )->toBeTrue();

    app(TenantContext::class)->set($school);

    expect($user->hasRole('superadmin'))->toBeTrue();

    app(TenantContext::class)->clear();

    // Modul inti aktif; add-on nonaktif.
    $intiAktif = TenantModule::withoutGlobalScope(SchoolScope::class)
        ->where('school_id', $school->id)->where('aktif', true)->count();
    $intiTotal = Module::where('kategori', 'inti')->where('aktif', true)->count();

    expect($intiAktif)->toBe($intiTotal);
});

it('nomor WA duplikat ditolak', function (): void {
    $data = daftarData();
    User::create([
        'username' => 'sudah-ada',
        'name' => 'Sudah Ada',
        'phone' => $data['admin_phone'],
    ]);

    Livewire::test(DaftarSekolah::class)
        ->set('nama', $data['nama'])
        ->set('jenjang', $data['jenjang'])
        ->set('admin_nama', $data['admin_nama'])
        ->set('admin_phone', $data['admin_phone'])
        ->call('daftar')
        ->assertHasErrors(['admin_phone']);
});

it('format nomor WA divalidasi', function (): void {
    Livewire::test(DaftarSekolah::class)
        ->set('nama', 'SDN X')
        ->set('jenjang', 'SD')
        ->set('admin_nama', 'Budi')
        ->set('admin_phone', '08123456789')
        ->call('daftar')
        ->assertHasErrors(['admin_phone']);
});

it('kuota trial: 100 siswa maksimal', function (): void {
    $school = app(TenantProvisioningService::class)->provision(daftarData());
    app(TenantContext::class)->set($school);

    $quota = app(QuotaService::class)->check('siswa_aktif');

    expect($quota['batas'])->toBe(100)->and($quota['boleh'])->toBeTrue();

    // Simulasi penuh: 100 siswa aktif.
    foreach (range(1, 100) as $i) {
        Student::create([
            'school_id' => $school->id,
            'nis' => 'NIS'.$i,
            'nama' => 'Siswa '.$i,
            'jk' => 'L',
            'status' => 'aktif',
        ]);
    }

    $quota = app(QuotaService::class)->check('siswa_aktif');

    expect($quota['boleh'])->toBeFalse()->and($quota['sisa'])->toBe(0);

    app(TenantContext::class)->clear();
});

it('trial tidak boleh broadcast', function (): void {
    $school = app(TenantProvisioningService::class)->provision(daftarData());
    app(TenantContext::class)->set($school);

    expect(app(QuotaService::class)->allows('broadcast'))->toBeFalse()
        ->and(app(QuotaService::class)->allows('api'))->toBeFalse();

    // Setelah upgrade ke active, broadcast dibuka.
    $school->update(['status_langganan' => 'active']);

    expect(app(QuotaService::class)->allows('broadcast'))->toBeTrue();

    app(TenantContext::class)->clear();
});

it('override kuota tidak berlaku saat trial', function (): void {
    $school = app(TenantProvisioningService::class)->provision(daftarData());

    TenantQuotaOverride::withoutGlobalScope(SchoolScope::class)->create([
        'school_id' => $school->id,
        'metrik' => 'siswa_aktif',
        'batas_custom' => 500,
        'alasan' => 'Uji override saat trial',
        'dibuat_oleh' => null,
    ]);

    app(TenantContext::class)->set($school);

    // Trial: override diabaikan, tetap batas trial 100.
    expect(app(QuotaService::class)->check('siswa_aktif')['batas'])->toBe(100);

    app(TenantContext::class)->clear();
});

it('halaman daftar memakai design system', function (): void {
    Livewire::test(DaftarSekolah::class)
        ->assertSee('Daftar Trial Gratis')
        ->assertSee('Nama Sekolah')
        ->assertDontSee('<i data-lucide');
});
