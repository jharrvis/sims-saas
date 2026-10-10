<?php

declare(strict_types=1);

namespace App\Livewire\Uks;

use App\Models\Medicine;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Stok obat UKS + catat pemakaian.
 */
class MedicineIndex extends Component
{
    public string $search = '';

    public string $nama = '';

    public string $satuan = 'tablet';

    public string $stok = '';

    public string $stok_minimum = '';

    public bool $showForm = false;

    public ?int $pakai_id = null;

    /** @var array<int,string> */
    public array $pakai_jumlah = [];

    public function save(): void
    {
        $this->authorize('uks.obat');

        $data = $this->validate([
            'nama' => ['required', 'string', 'max:150'],
            'satuan' => ['required', 'string', 'max:30'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
        ]);

        Medicine::create($data);

        $this->reset(['nama', 'satuan', 'stok', 'stok_minimum', 'showForm']);
        $this->satuan = 'tablet';
        session()->flash('ok', 'Obat ditambahkan.');
    }

    public function pakai(int $id): void
    {
        $this->authorize('uks.obat');

        $this->validate([
            "pakai_jumlah.$id" => ['required', 'integer', 'min:1'],
        ]);

        $medicine = Medicine::findOrFail($id);
        $medicine->pakai((int) $this->pakai_jumlah[$id], auth()->id());

        unset($this->pakai_jumlah[$id]);
        session()->flash('ok', 'Pemakaian obat dicatat; stok berkurang.');
    }

    public function render(): View
    {
        $items = Medicine::when($this->search !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->search.'%'))
            ->orderBy('nama')
            ->get();

        return view('livewire.uks.medicine-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'Obat UKS']);
    }
}
