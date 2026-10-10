<?php

declare(strict_types=1);

use App\Domains\Billing\Services\DunningService;
use App\Domains\Identity\Contracts\WaSender;
use App\Domains\Platform\Services\PlatformService;
use App\Models\AuditLog;
use App\Models\BillingInvoice;
use App\Models\DunningLog;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use App\Scopes\SchoolScope;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanSeeder;

class FakeDunningWa implements WaSender
{
    /** @var array<int, string> */
    public static array $terkirim = [];

    public function sendOtp(string $phone, string $code, string $schoolName): bool
    {
        return true;
    }

    public function sendText(string $phone, string $message): bool
    {
        self::$terkirim[] = $phone.': '.$message;

        return true;
    }
}

beforeEach(function (): void {
    $this->seed(PermissionSeeder::class);
    $this->seed(PlanSeeder::class);
    FakeDunningWa::$terkirim = [];
    app()->bind(WaSender::class, FakeDunningWa::class);
});

/**
 * @return array{school: School, sub: Subscription, admin: User}
 */
function dunningSetup(string $statusSekolah = 'trialing'): array
{
    $school = School::create([
        'nama' => 'SDN Dunning '.random_int(100, 999),
        'slug' => 'sdn-dunning-'.uniqid(),
        'jenjang' => 'SD',
        'status' => 'aktif',
        'status_langganan' => $statusSekolah,
    ]);

    $admin = User::create([
        'username' => 'dun'.random_int(1000, 9999),
        'name' => 'Admin Dunning',
        'phone' => '62899'.random_int(1000000, 9999999),
    ]);

    Membership::create(['user_id' => $admin->id, 'school_id' => $school->id, 'status' => 'aktif']);

    $plan = Plan::where('slug', 'basic')->firstOrFail();
    $sub = Subscription::withoutGlobalScope(SchoolScope::class)->create([
        'school_id' => $school->id,
        'plan_id' => $plan->id,
        'status' => 'trial',
        'periode' => 'bulanan',
        'trial_berakhir_pada' => now()->addDays(30),
    ]);

    return ['school' => $school, 'sub' => $sub, 'admin' => $admin];
}

it('mengirim pengingat H-7 trial sekali saja', function (): void {
    ['sub' => $sub, 'school' => $school] = dunningSetup();
    $sub->update(['trial_berakhir_pada' => now()->addDays(7)->startOfDay()]);

    $hasil = app(DunningService::class)->proses();

    expect($hasil['trial_reminder'])->toBe(1)
        ->and(DunningLog::where('school_id', $school->id)->where('jenis', 'trial_h7')->count())->toBe(1)
        ->and(FakeDunningWa::$terkirim)->toHaveCount(1);

    // Dijalankan lagi: tidak kirim ganda.
    $hasil2 = app(DunningService::class)->proses();

    expect($hasil2['trial_reminder'])->toBe(0)
        ->and(FakeDunningWa::$terkirim)->toHaveCount(1);
});

it('trial kedaluwarsa menjadi past_due', function (): void {
    ['sub' => $sub, 'school' => $school] = dunningSetup();
    $sub->update(['trial_berakhir_pada' => now()->subDay()]);

    $hasil = app(DunningService::class)->proses();

    expect($hasil['trial_expired'])->toBe(1)
        ->and($sub->fresh()->status)->toBe('past_due')
        ->and($school->fresh()->status_langganan)->toBe('past_due');
});

it('past_due melewati tenggang disuspend otomatis', function (): void {
    ['school' => $school] = dunningSetup('past_due');

    Subscription::withoutGlobalScope(SchoolScope::class)
        ->where('school_id', $school->id)
        ->update(['status' => 'past_due', 'updated_at' => now()->subDays(8)]);

    $hasil = app(DunningService::class)->proses();

    expect($hasil['suspended'])->toBe(1)
        ->and($school->fresh()->status_langganan)->toBe('suspended')
        ->and(
            AuditLog::where('aksi', 'platform.tenant.suspend')
                ->where('school_id', $school->id)->exists()
        )->toBeTrue();
});

it('past_due dalam tenggang belum disuspend', function (): void {
    ['school' => $school] = dunningSetup('past_due');

    Subscription::withoutGlobalScope(SchoolScope::class)
        ->where('school_id', $school->id)
        ->update(['status' => 'past_due', 'updated_at' => now()->subDays(3)]);

    $hasil = app(DunningService::class)->proses();

    expect($hasil['suspended'])->toBe(0)
        ->and($school->fresh()->status_langganan)->toBe('past_due');
});

it('invoice lewat jatuh tempo menjadi overdue + pengingat', function (): void {
    ['school' => $school, 'admin' => $admin] = dunningSetup('active');

    $plan = Plan::where('slug', 'basic')->firstOrFail();
    $sub = app(PlatformService::class)->createSubscription($school, $plan, 'bulanan', $admin);
    $invoice = app(PlatformService::class)->issueInvoice($sub, $admin);
    $invoice->update(['jatuh_tempo' => now()->subDay()->toDateString()]);

    $hasil = app(DunningService::class)->proses();

    expect($hasil['invoice_overdue'])->toBe(1)
        ->and($invoice->fresh()->status)->toBe('overdue')
        ->and(FakeDunningWa::$terkirim)->toHaveCount(1);
});

it('menerbitkan tagihan perpanjangan H-7 tanpa duplikat', function (): void {
    ['school' => $school, 'admin' => $admin] = dunningSetup('active');

    $plan = Plan::where('slug', 'basic')->firstOrFail();
    $sub = app(PlatformService::class)->createSubscription($school, $plan, 'bulanan', $admin);
    $sub->update(['periode_berakhir_pada' => now()->addDays(5)]);

    $hasil = app(DunningService::class)->proses();

    expect($hasil['renewal_issued'])->toBe(1);

    $jumlah = BillingInvoice::withoutGlobalScope(SchoolScope::class)
        ->where('subscription_id', $sub->id)->count();

    expect($jumlah)->toBe(1);

    // Dijalankan lagi: tidak terbit ganda.
    $hasil2 = app(DunningService::class)->proses();

    expect($hasil2['renewal_issued'])->toBe(0)
        ->and(
            BillingInvoice::withoutGlobalScope(SchoolScope::class)
                ->where('subscription_id', $sub->id)->count()
        )->toBe(1);
});

it('command subscription:lifecycle berjalan', function (): void {
    $this->artisan('subscription:lifecycle')->assertSuccessful();
});
