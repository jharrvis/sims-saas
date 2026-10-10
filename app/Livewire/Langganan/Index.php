<?php

declare(strict_types=1);

namespace App\Livewire\Langganan;

use App\Domains\Billing\Services\MidtransService;
use App\Models\BillingInvoice;
use App\Models\Subscription;
use App\Tenancy\TenantContext;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Langganan Saya — paket aktif & tagihan sekolah (tenant melihat datanya sendiri).
 */
class Index extends Component
{
    public function bayar(int $invoiceId, MidtransService $midtrans): void
    {
        $invoice = BillingInvoice::whereKey($invoiceId)->firstOrFail();

        abort_unless(in_array($invoice->status, ['issued', 'overdue'], true), 422);

        try {
            $result = $midtrans->createSnapTransaction($invoice);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal membuat pembayaran. Coba lagi nanti.');

            return;
        }

        $this->redirect($result['redirect_url'], navigate: false);
    }

    public function render(): View
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        $subscription = Subscription::where('school_id', $schoolId)
            ->with('plan')
            ->orderByDesc('id')
            ->first();

        $invoices = BillingInvoice::where('school_id', $schoolId)
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('livewire.langganan.index', [
            'subscription' => $subscription,
            'invoices' => $invoices,
        ])->layout('layouts.app', ['title' => 'Langganan Saya']);
    }
}
