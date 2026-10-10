<?php

declare(strict_types=1);

namespace App\Livewire\Keuangan;

use App\Domains\Keuangan\Services\KeuanganService;
use App\Models\FeeType;
use App\Models\Invoice;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar tagihan + ringkasan arus kas.
 */
class InvoiceIndex extends Component
{
    use WithPagination;

    public string $status = '';

    public string $search = '';

    public ?int $feeTypeId = null;

    public string $periodeLabel = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function generate(KeuanganService $service): void
    {
        $this->authorize('keuangan.tagihan.generate');

        $this->validate([
            'feeTypeId' => ['required', 'integer', 'exists:fee_types,id'],
            'periodeLabel' => ['required', 'string', 'max:30'],
        ]);

        $count = $service->generateInvoices($this->feeTypeId, $this->periodeLabel);

        session()->flash('generated', "{$count} tagihan berhasil dibuat.");
    }

    public function render(): View
    {
        $invoices = Invoice::with(['student:id,nama,nis', 'feeType:id,nama'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', fn ($q) => $q->whereHas('student', fn ($w) => $w->where('nama', 'like', '%'.$this->search.'%')))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.keuangan.invoice-index', [
            'invoices' => $invoices,
            'feeTypes' => FeeType::where('aktif', true)->orderBy('nama')->get(),
            'summary' => app(KeuanganService::class)->summary(),
        ])->layout('layouts.app', ['title' => 'Keuangan SPP']);
    }
}
