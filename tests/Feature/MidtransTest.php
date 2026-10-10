<?php

declare(strict_types=1);

use App\Domains\Billing\Services\MidtransService;
use App\Domains\Platform\Services\PlatformService;
use App\Models\BillingInvoice;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use App\Scopes\SchoolScope;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanSeeder;

beforeEach(function (): void {
    $this->seed(PermissionSeeder::class);
    $this->seed(PlanSeeder::class);
    config()->set('midtrans.server_key', 'SB-Mid-server-testkey123');
});

/**
 * @param  array<string, string>  $over
 * @return array<string, string>
 */
function midtransPayload(array $over = []): array
{
    $base = array_merge([
        'order_id' => 'SIMS-1-20261010',
        'status_code' => '200',
        'gross_amount' => '499000.00',
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'payment_type' => 'bank_transfer',
    ], $over);

    $base['signature_key'] = hash('sha512',
        $base['order_id'].$base['status_code'].$base['gross_amount'].'SB-Mid-server-testkey123');

    return $base;
}

function tagihanSiapBayar(string $statusSekolah = 'past_due'): BillingInvoice
{
    $school = School::create([
        'nama' => 'SDN Bayar',
        'slug' => 'sdn-bayar-'.uniqid(),
        'jenjang' => 'SD',
        'status' => 'aktif',
        'status_langganan' => $statusSekolah,
    ]);

    $admin = User::create([
        'username' => 'op'.random_int(1000, 9999),
        'name' => 'Operator',
        'phone' => '62899'.random_int(1000000, 9999999),
        'is_platform' => true,
    ]);

    $plan = Plan::where('slug', 'basic')->firstOrFail();
    $sub = app(PlatformService::class)->createSubscription($school, $plan, 'bulanan', $admin);
    $invoice = app(PlatformService::class)->issueInvoice($sub, $admin);
    $invoice->update(['midtrans_order_id' => 'SIMS-'.$invoice->id.'-20261010']);

    return $invoice;
}

it('signature valid lolos verifikasi, yang diubah ditolak', function (): void {
    $service = app(MidtransService::class);

    expect($service->verifySignature(midtransPayload()))->toBeTrue();

    $rusak = midtransPayload();
    $rusak['gross_amount'] = '1000.00';

    expect($service->verifySignature($rusak))->toBeFalse();
});

it('webhook settlement melunasi tagihan dan mengaktifkan tenant', function (): void {
    $invoice = tagihanSiapBayar('past_due');

    $payload = midtransPayload([
        'order_id' => $invoice->midtrans_order_id,
        'gross_amount' => number_format($invoice->total, 2, '.', ''),
    ]);

    $this->postJson('/api/v1/webhooks/midtrans', $payload)->assertOk();

    expect($invoice->fresh()->status)->toBe('paid')
        ->and($invoice->fresh()->metode_bayar)->toBe('midtrans')
        ->and($invoice->fresh()->dibayar_pada)->not->toBeNull();

    $school = School::withoutGlobalScope(SchoolScope::class)->find($invoice->school_id);

    expect($school->status_langganan)->toBe('active');
});

it('webhook idempoten untuk notifikasi ganda', function (): void {
    $invoice = tagihanSiapBayar();

    $payload = midtransPayload([
        'order_id' => $invoice->midtrans_order_id,
        'gross_amount' => number_format($invoice->total, 2, '.', ''),
    ]);

    $this->postJson('/api/v1/webhooks/midtrans', $payload)->assertOk();
    $this->postJson('/api/v1/webhooks/midtrans', $payload)->assertOk();

    expect(
        BillingInvoice::withoutGlobalScope(SchoolScope::class)
            ->where('midtrans_order_id', $invoice->midtrans_order_id)
            ->where('status', 'paid')->count()
    )->toBe(1);
});

it('webhook signature salah → 403 dan tagihan tidak berubah', function (): void {
    $invoice = tagihanSiapBayar();

    $payload = midtransPayload(['order_id' => $invoice->midtrans_order_id]);
    $payload['signature_key'] = 'salah';

    $this->postJson('/api/v1/webhooks/midtrans', $payload)->assertForbidden();

    expect($invoice->fresh()->status)->toBe('issued');
});

it('webhook expire menandai overdue dan menghapus snap token', function (): void {
    $invoice = tagihanSiapBayar();
    $invoice->update(['snap_token' => 'token-lama', 'payment_url' => 'https://x']);

    $payload = midtransPayload([
        'order_id' => $invoice->midtrans_order_id,
        'transaction_status' => 'expire',
        'status_code' => '202',
        'gross_amount' => number_format($invoice->total, 2, '.', ''),
    ]);

    $this->postJson('/api/v1/webhooks/midtrans', $payload)->assertOk();

    $fresh = $invoice->fresh();

    expect($fresh->status)->toBe('overdue')
        ->and($fresh->snap_token)->toBeNull()
        ->and($fresh->payment_url)->toBeNull();
});
