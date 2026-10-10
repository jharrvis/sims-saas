<?php

declare(strict_types=1);

namespace App\Livewire\Aset;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar aset inventaris.
 */
class AssetIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $kondisi = '';

    public string $kategori = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingKondisi(): void
    {
        $this->resetPage();
    }

    public function updatingKategori(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $items = Asset::with('category:id,nama')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w->where('nama', 'like', '%'.$this->search.'%')->orWhere('kode', 'like', '%'.$this->search.'%')))
            ->when($this->kondisi !== '', fn ($q) => $q->where('kondisi', $this->kondisi))
            ->when($this->kategori !== '', fn ($q) => $q->where('asset_category_id', (int) $this->kategori))
            ->orderBy('nama')
            ->paginate(15);

        return view('livewire.aset.asset-index', [
            'items' => $items,
            'categories' => AssetCategory::orderBy('nama')->get(['id', 'nama']),
        ])->layout('layouts.app', ['title' => 'Inventaris & Aset']);
    }
}
