<?php

declare(strict_types=1);

namespace App\Livewire\Alumni;

use App\Models\Alumni;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar alumni.
 */
class AlumniIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $tahun = '';

    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTahun(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $items = Alumni::with('careers')
            ->when($this->search !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->search.'%'))
            ->when($this->tahun !== '', fn ($q) => $q->where('tahun_lulus', $this->tahun))
            ->when($this->status !== '', fn ($q) => $q->whereHas('careers', fn ($c) => $c->where('status', $this->status)))
            ->orderByDesc('tahun_lulus')
            ->orderBy('nama')
            ->paginate(15);

        $tahuns = Alumni::distinct()->orderByDesc('tahun_lulus')->pluck('tahun_lulus')->filter()->values();

        return view('livewire.alumni.alumni-index', [
            'items' => $items,
            'tahuns' => $tahuns,
        ])->layout('layouts.app', ['title' => 'Alumni & Karier']);
    }
}
