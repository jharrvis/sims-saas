<?php

declare(strict_types=1);

namespace App\Domains\Billing\Services;

use App\Models\BillingInvoice;
use App\Models\School;
use App\Models\Subscription;
use App\Scopes\SchoolScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

/**
 * Pembayaran langganan via Midtrans Snap (keputusan pemilik produk 10 Okt 2026).
 */
class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$clientKey = (string) config('midtrans.client_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Buat transaksi Snap untuk tagihan; simpan order_id + payment URL.
     *
     * @return array{token: string, redirect_url: string}
     */
    public function createSnapTransaction(BillingInvoice $invoice): array
    {
        if ($invoice->midtrans_order_id === null) {
            $invoice->update([
                'midtrans_order_id' => 'SIMS-'.$invoice->id.'-'.now()->format('YmdHis'),
            ]);
        }

        /** @var School|null $school */
        $school = School::withoutGlobalScope(SchoolScope::class)->find($invoice->school_id);

        $namaCustomer = $invoice->nama_sekolah_snapshot;

        if ($school instanceof School) {
            $namaCustomer = $school->nama;
        }

        /** @var Carbon $periodeDari */
        $periodeDari = $invoice->periode_dari;

        $params = [
            'transaction_details' => [
                'order_id' => $invoice->midtrans_order_id,
                'gross_amount' => (int) $invoice->total,
            ],
            'customer_details' => [
                'first_name' => $namaCustomer,
            ],
            'item_details' => [
                [
                    'id' => 'langganan',
                    'price' => (int) $invoice->total,
                    'quantity' => 1,
                    'name' => 'Langganan SIMS '.$periodeDari->format('M Y'),
                ],
            ],
            'callbacks' => [
                'finish' => url(config('midtrans.finish_url')),
            ],
        ];

        $snapToken = Snap::getSnapToken($params);
        $redirectUrl = Snap::getSnapUrl($params);

        $invoice->update([
            'snap_token' => $snapToken,
            'payment_url' => $redirectUrl,
        ]);

        return ['token' => $snapToken, 'redirect_url' => $redirectUrl];
    }

    /**
     * Verifikasi signature_key notifikasi Midtrans (Dok Midtrans §HTTP notification).
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifySignature(array $payload): bool
    {
        $expected = hash('sha512',
            ($payload['order_id'] ?? '')
            .($payload['status_code'] ?? '')
            .($payload['gross_amount'] ?? '')
            .config('midtrans.server_key')
        );

        return hash_equals($expected, (string) ($payload['signature_key'] ?? ''));
    }

    /**
     * Proses notifikasi webhook → update status tagihan & langganan.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleNotification(array $payload): void
    {
        if (! $this->verifySignature($payload)) {
            Log::warning('midtrans: signature tidak valid', ['order_id' => $payload['order_id'] ?? null]);

            return;
        }

        $invoice = BillingInvoice::withoutGlobalScope(SchoolScope::class)
            ->where('midtrans_order_id', $payload['order_id'] ?? '')
            ->first();

        if ($invoice === null) {
            Log::warning('midtrans: invoice tidak ditemukan', ['order_id' => $payload['order_id'] ?? null]);

            return;
        }

        $status = (string) ($payload['transaction_status'] ?? '');
        $fraud = (string) ($payload['fraud_status'] ?? '');

        match (true) {
            $status === 'capture' && $fraud === 'accept' => $this->markPaid($invoice, 'midtrans'),
            $status === 'settlement' => $this->markPaid($invoice, 'midtrans'),
            $status === 'pending' => $invoice->update(['status' => 'issued']),
            $status === 'expire' => $invoice->update([
                'status' => 'overdue',
                'snap_token' => null,
                'payment_url' => null,
            ]),
            $status === 'deny' || $status === 'cancel' => $invoice->update(['status' => 'overdue']),
            default => Log::info('midtrans: status tidak ditangani', ['status' => $status]),
        };
    }

    private function markPaid(BillingInvoice $invoice, string $metode): void
    {
        if ($invoice->status === 'paid') {
            return; // idempoten: notifikasi ganda Midtrans
        }

        $invoice->update([
            'status' => 'paid',
            'dibayar_pada' => now(),
            'metode_bayar' => $metode,
        ]);

        $school = School::withoutGlobalScope(SchoolScope::class)->find($invoice->school_id);

        if ($school instanceof School && $school->status_langganan === 'past_due') {
            $school->update(['status_langganan' => 'active']);

            Subscription::withoutGlobalScope(SchoolScope::class)
                ->where('school_id', $school->id)
                ->where('status', 'past_due')
                ->update(['status' => 'active']);
        }

        Log::info('midtrans: invoice lunas', ['nomor' => $invoice->nomor]);
    }
}
