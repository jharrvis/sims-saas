<?php

declare(strict_types=1);

namespace App\Livewire\Keuangan;

use App\Domains\Keuangan\Services\KeuanganService;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail tagihan + catat pembayaran + riwayat (kuitansi).
 */
class InvoiceShow extends Component
{
    public Invoice $invoice;

    public float $nominal = 0;

    public string $tgl_bayar = '';

    public string $metode = 'tunai';

    public string $catatan = '';

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load(['student:id,nama,nis', 'feeType:id,nama', 'payments']);
        $this->tgl_bayar = now()->toDateString();
        $this->nominal = $invoice->sisa();
    }

    public function pay(KeuanganService $service): void
    {
        $this->authorize('keuangan.bayar.create');

        $this->validate([
            'nominal' => ['required', 'numeric', 'min:1', 'max:'.$this->invoice->sisa()],
            'tgl_bayar' => ['required', 'date'],
            'metode' => ['required', Rule::in(array_keys(Payment::METODES))],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $payment = $service->recordPayment($this->invoice, [
                'nominal' => $this->nominal,
                'tgl_bayar' => $this->tgl_bayar,
                'metode' => $this->metode,
                'catatan' => $this->catatan ?: null,
            ], auth()->id());
        } catch (\InvalidArgumentException $e) {
            $this->addError('nominal', $e->getMessage());

            return;
        }

        $this->invoice = $this->invoice->fresh(['student:id,nama,nis', 'feeType:id,nama', 'payments']);
        $this->nominal = $this->invoice->sisa();
        $this->catatan = '';

        session()->flash('paid', "Pembayaran tercatat — kuitansi {$payment->no_kuitansi}.");
    }

    public function render(): View
    {
        return view('livewire.keuangan.invoice-show')
            ->layout('layouts.app', ['title' => 'Tagihan '.$this->invoice->no_invoice]);
    }
}
