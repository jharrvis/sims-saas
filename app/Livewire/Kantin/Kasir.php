<?php

declare(strict_types=1);

namespace App\Livewire\Kantin;

use App\Models\CanteenProduct;
use App\Models\CanteenTransaction;
use App\Models\Student;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Kasir (POS) kantin.
 */
class Kasir extends Component
{
    public string $search = '';

    /** @var array<int,int> product_id => qty */
    public array $keranjang = [];

    public ?int $student_id = null;

    public string $searchSiswa = '';

    public string $bayar = '';

    public ?CanteenTransaction $terakhir = null;

    public function mount(): void
    {
        $this->authorize('kantin.kasir');
    }

    public function tambah(int $id): void
    {
        $p = CanteenProduct::findOrFail($id);
        $qty = ($this->keranjang[$id] ?? 0) + 1;

        if ($qty > $p->stok) {
            $this->addError('keranjang', "Stok {$p->nama} tidak mencukupi.");

            return;
        }

        $this->keranjang[$id] = $qty;
    }

    public function kurang(int $id): void
    {
        $qty = ($this->keranjang[$id] ?? 0) - 1;

        if ($qty <= 0) {
            unset($this->keranjang[$id]);
        } else {
            $this->keranjang[$id] = $qty;
        }
    }

    public function checkout(): void
    {
        $this->authorize('kantin.kasir');

        $this->validate([
            'bayar' => ['required', 'numeric', 'min:0'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
        ]);

        $keranjang = [];
        foreach ($this->keranjang as $productId => $qty) {
            $keranjang[] = ['product_id' => $productId, 'qty' => $qty];
        }

        $this->terakhir = CanteenTransaction::checkout(
            $keranjang,
            $this->student_id,
            (float) $this->bayar,
            auth()->id()
        );

        $this->terakhir->load('items');
        $this->reset(['keranjang', 'bayar', 'student_id', 'searchSiswa']);
        session()->flash('ok', 'Transaksi berhasil.');
    }

    public function render(): View
    {
        $products = CanteenProduct::where('aktif', true)
            ->when($this->search !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->search.'%'))
            ->orderBy('nama')
            ->get();

        $detail = [];
        $total = 0.0;
        foreach ($this->keranjang as $productId => $qty) {
            $p = $products->firstWhere('id', $productId) ?? CanteenProduct::find($productId);
            if ($p === null) {
                continue;
            }
            $subtotal = (float) $p->harga * $qty;
            $total += $subtotal;
            $detail[] = ['product' => $p, 'qty' => $qty, 'subtotal' => $subtotal];
        }

        $siswaList = Student::where('status', 'aktif')
            ->when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->orderBy('nama')->limit(10)->get(['id', 'nis', 'nama']);

        return view('livewire.kantin.kasir', [
            'products' => $products,
            'detail' => $detail,
            'total' => $total,
            'siswaList' => $siswaList,
        ])->layout('layouts.app', ['title' => 'Kasir Kantin']);
    }
}
