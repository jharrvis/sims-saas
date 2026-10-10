<?php

declare(strict_types=1);

namespace App\Livewire\Kantin;

use App\Models\CanteenTransaction;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Riwayat transaksi kasir.
 */
class TransactionIndex extends Component
{
    use WithPagination;

    public string $tanggal = '';

    public function updatingTanggal(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->authorize('kantin.view');
        $this->tanggal = now()->toDateString();
    }

    public function render(): View
    {
        $items = CanteenTransaction::with(['student:id,nama', 'items'])
            ->when($this->tanggal !== '', fn ($q) => $q->whereDate('tgl', $this->tanggal))
            ->orderByDesc('tgl')
            ->paginate(15);

        $omzet = CanteenTransaction::when($this->tanggal !== '', fn ($q) => $q->whereDate('tgl', $this->tanggal))
            ->sum('total');

        return view('livewire.kantin.transaction-index', [
            'items' => $items,
            'omzet' => $omzet,
        ])->layout('layouts.app', ['title' => 'Riwayat Transaksi']);
    }
}
