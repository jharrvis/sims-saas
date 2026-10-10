<?php

declare(strict_types=1);

use App\Domains\Platform\Services\PlatformService;
use App\Livewire\Platform\TenantIndex;
use App\Livewire\Platform\TenantShow;
use App\Models\AuditLog;
use App\Models\BillingInvoice;
use App\Models\MfaSecret;
use App\Models\Plan;
use App\Models\School;
use App\Models\TenantQuotaOverride;
use App\Models\User;
use App\Scopes\SchoolScope;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * @param  array<int, string>  $without
 */
function platformAdmin(array $without = []): User
{
    $admin = User::create([
        'username' => 'platform'.random_int(1000, 9999),
        'name' => 'Operator Platform',
        'phone' => '62899'.random_int(1000000, 9999999),
        'is_platform' => true,
    ]);

    // Sentinel team 0 = konteks platform (tanpa tenant).
    app(PermissionRegistrar::class)->setPermissionsTeamId(0);
    $role = Role::findOrCreate('platform-admin', 'platform');
    $role->syncPermissions(
        Permission::where('guard_name', 'platform')
            ->whereNotIn('name', $without)
            ->get()
    );
    $admin->assignRole($role);

    // MFA wajib untuk akun platform (FR-904) — konfirmasi agar lolos middleware.
    MfaSecret::create([
        'user_id' => $admin->id,
        'secret' => 'TESTSECRET',
        'confirmed_at' => now(),
        'recovery_codes' => [],
    ]);

    return $admin;
}

function tenantSchool(string $nama = 'SDN Contoh'): School
{
    return School::create([
        'nama' => $nama,
        'slug' => Str::slug($nama).'-'.uniqid(),
        'jenjang' => 'SD',
        'status' => 'aktif',
        'status_langganan' => 'active',
    ]);
}

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed(PermissionSeeder::class);
    $this->seed(PlanSeeder::class);
});

it('menolak non-platform mengakses area platform', function (): void {
    $user = User::create([
        'username' => 'staf'.random_int(1000, 9999),
        'name' => 'Staf',
        'phone' => '62899'.random_int(1000000, 9999999),
        'is_platform' => false,
    ]);
    tenantSchool();

    $this->actingAs($user)->get('/platform')->assertForbidden();
    $this->actingAs($user)->get('/platform/tenants')->assertForbidden();
});

it('menolak platform tanpa permission spesifik', function (): void {
    $admin = platformAdmin(['platform.metrics.view']);

    $this->actingAs($admin)->withSession(['mfa_verified_'.$admin->id => true])
        ->get('/platform')->assertForbidden();
});

it('dashboard platform tampil untuk operator berizin', function (): void {
    $admin = platformAdmin();
    tenantSchool('SDN A');
    tenantSchool('SDN B');

    $req = fn (string $url) => $this->actingAs($admin)
        ->withSession(['mfa_verified_'.$admin->id => true])->get($url);

    $req('/platform')->assertOk();
    $req('/platform/tenants')->assertOk();
    $req('/platform/paket')->assertOk();
    $req('/platform/langganan')->assertOk();
    $req('/platform/tagihan')->assertOk();
    $req('/platform/modul')->assertOk();
    $req('/platform/audit')->assertOk();
});

it('operator melihat tenant lintas sekolah', function (): void {
    $admin = platformAdmin();
    tenantSchool('SDN Lintas A');
    tenantSchool('SDN Lintas B');

    Livewire::actingAs($admin)
        ->test(TenantIndex::class)
        ->assertSee('SDN Lintas A')
        ->assertSee('SDN Lintas B');
});

it('suspend dan aktivasi tenant via panel', function (): void {
    $admin = platformAdmin();
    $school = tenantSchool();

    Livewire::actingAs($admin)
        ->test(TenantIndex::class)
        ->call('suspend', $school->id);

    expect($school->fresh()->status_langganan)->toBe('suspended');
    expect(
        AuditLog::where('aksi', 'platform.tenant.suspend')
            ->where('school_id', $school->id)->exists()
    )->toBeTrue();

    Livewire::actingAs($admin)
        ->test(TenantIndex::class)
        ->call('activate', $school->id);

    expect($school->fresh()->status_langganan)->toBe('active');
});

it('membuat langganan trial 30 hari', function (): void {
    $admin = platformAdmin();
    $school = tenantSchool();
    $plan = Plan::where('slug', 'pro')->firstOrFail();

    $service = app(PlatformService::class);
    $sub = $service->createSubscription($school, $plan, 'bulanan', $admin, true);

    /** @var Carbon $akhirTrial */
    $akhirTrial = $sub->trial_berakhir_pada;

    expect($sub->status)->toBe('trial')
        ->and($school->fresh()->status_langganan)->toBe('trialing')
        ->and($akhirTrial->isAfter(now()->addDays(29)))->toBeTrue()
        ->and($akhirTrial->isBefore(now()->addDays(31)))->toBeTrue();
});

it('menerbitkan dan melunasi tagihan langganan', function (): void {
    $admin = platformAdmin();
    $school = tenantSchool();
    $plan = Plan::where('slug', 'basic')->firstOrFail();

    $service = app(PlatformService::class);
    $sub = $service->createSubscription($school, $plan, 'bulanan', $admin);

    $invoice = $service->issueInvoice($sub, $admin, 49000);

    expect($invoice->nomor)->toStartWith('INV/'.now()->format('Y/m').'/')
        ->and($invoice->total)->toBe(499000 - 49000)
        ->and($invoice->status)->toBe('issued')
        ->and($invoice->nama_sekolah_snapshot)->toBe($school->nama);

    $school->update(['status_langganan' => 'past_due']);
    $service->markInvoicePaid($invoice, $admin, 'midtrans');

    expect($invoice->fresh()->status)->toBe('paid')
        ->and($invoice->fresh()->metode_bayar)->toBe('midtrans')
        ->and($school->fresh()->status_langganan)->toBe('active');
});

it('nomor tagihan berurutan anti-duplikat', function (): void {
    $admin = platformAdmin();
    $school = tenantSchool();
    $plan = Plan::where('slug', 'basic')->firstOrFail();
    $service = app(PlatformService::class);
    $sub = $service->createSubscription($school, $plan, 'bulanan', $admin);

    $a = $service->issueInvoice($sub, $admin);
    $b = $service->issueInvoice($sub, $admin);

    expect($a->nomor)->not->toBe($b->nomor)
        ->and(BillingInvoice::withoutGlobalScope(SchoolScope::class)->where('nomor', $b->nomor)->count())->toBe(1);
});

it('quota override bisa dibuat dan dihapus dari detail tenant', function (): void {
    $admin = platformAdmin();
    $school = tenantSchool();

    Livewire::actingAs($admin)
        ->test(TenantShow::class, ['schoolId' => $school->id])
        ->set('ov_metrik', 'siswa_aktif')
        ->set('ov_batas', '200')
        ->set('ov_alasan', 'Diskon kemitraan')
        ->call('tambahOverride')
        ->assertHasNoErrors();

    $ov = TenantQuotaOverride::withoutGlobalScope(SchoolScope::class)
        ->where('school_id', $school->id)->where('metrik', 'siswa_aktif')->firstOrFail();

    expect($ov->batas_custom)->toBe(200)->and($ov->isBerlaku())->toBeTrue();

    Livewire::actingAs($admin)
        ->test(TenantShow::class, ['schoolId' => $school->id])
        ->call('hapusOverride', $ov->id);

    expect(
        TenantQuotaOverride::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', $school->id)->count()
    )->toBe(0);
});

it('metrik dashboard menghitung MRR dengan benar', function (): void {
    $admin = platformAdmin();
    $service = app(PlatformService::class);

    $basic = Plan::where('slug', 'basic')->firstOrFail();
    $pro = Plan::where('slug', 'pro')->firstOrFail();

    $service->createSubscription(tenantSchool('S1'), $basic, 'bulanan', $admin);
    $service->createSubscription(tenantSchool('S2'), $pro, 'bulanan', $admin);

    $metrics = $service->metrics();

    expect($metrics['total_tenant'])->toBe(2)
        ->and($metrics['mrr'])->toBe(499000 + 999000);
});
