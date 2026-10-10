<?php

declare(strict_types=1);

namespace App\Livewire\Uks;

use App\Models\HealthVisit;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar kunjungan UKS.
 */
class HealthVisitIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $rujukan = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRujukan(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $items = HealthVisit::with('student:id,nis,nama')
            ->when($this->search !== '', fn ($q) => $q->whereHas('student', fn ($s) => $s->where('nama', 'like', '%'.$this->search.'%')))
            ->when($this->rujukan !== '', fn ($q) => $q->where('rujukan', $this->rujukan))
            ->orderByDesc('tgl')
            ->paginate(15);

        return view('livewire.uks.health-visit-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'UKS & Kesehatan']);
    }
}
