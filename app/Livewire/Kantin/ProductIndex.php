<?php

declare(strict_types=1);

namespace App\Livewire\Kantin;

use App\Models\CanteenProduct;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Kelola produk kantin.
 */
class ProductIndex extends Component
{
    public string $search = '';

    public string $nama = '';

    public string $kategori = '';

    public string $harga = '';

    public string $stok = '';

    public bool $showForm = false;

    public ?int $editId = null;

    public function edit(int $id): void
    {
        $this->authorize('kantin.produk');

        $p = CanteenProduct::findOrFail($id);
        $this->editId = $id;
        $this->nama = $p->nama;
        $this->kategori = $p->kategori ?? '';
        $this->harga = (string) $p->harga;
        $this->stok = (string) $p->stok;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('kantin.produk');

        $data = $this->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        /** @var array<string,mixed> $payload */
        $payload = [
            'nama' => $data['nama'],
            'kategori' => $data['kategori'] ?: null,
            'harga' => (float) $data['harga'],
            'stok' => (int) $data['stok'],
        ];

        if ($this->editId !== null) {
            CanteenProduct::findOrFail($this->editId)->update($payload);
            session()->flash('ok', 'Produk diperbarui.');
        } else {
            CanteenProduct::create($payload);
            session()->flash('ok', 'Produk ditambahkan.');
        }

        $this->reset(['nama', 'kategori', 'harga', 'stok', 'showForm', 'editId']);
    }

    public function toggle(int $id): void
    {
        $this->authorize('kantin.produk');

        $p = CanteenProduct::findOrFail($id);
        $p->update(['aktif' => ! $p->aktif]);
    }

    public function render(): View
    {
        $items = CanteenProduct::when($this->search !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->search.'%'))
            ->orderBy('nama')
            ->get();

        return view('livewire.kantin.product-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'Produk Kantin']);
    }
}
