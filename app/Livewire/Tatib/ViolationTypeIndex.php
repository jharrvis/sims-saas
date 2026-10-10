<?php

declare(strict_types=1);

namespace App\Livewire\Tatib;

use App\Models\ViolationType;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Master jenis pelanggaran + bobot poin.
 */
class ViolationTypeIndex extends Component
{
    public string $nama = '';

    public string $kategori = 'ringan';

    public int $poin = 0;

    public ?int $editId = null;

    public function save(): void
    {
        $this->authorize($this->editId ? 'tatib.update' : 'tatib.create');

        $data = $this->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['required', Rule::in(['ringan', 'sedang', 'berat'])],
            'poin' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        if ($this->editId) {
            ViolationType::findOrFail($this->editId)->update($data);
            session()->flash('ok', 'Jenis pelanggaran diperbarui.');
        } else {
            ViolationType::create($data);
            session()->flash('ok', 'Jenis pelanggaran ditambahkan.');
        }

        $this->reset(['nama', 'poin', 'editId']);
        $this->kategori = 'ringan';
    }

    public function edit(ViolationType $type): void
    {
        $this->authorize('tatib.update');

        $this->editId = $type->id;
        $this->nama = $type->nama;
        $this->kategori = $type->kategori;
        $this->poin = $type->poin;
    }

    public function cancelEdit(): void
    {
        $this->reset(['nama', 'poin', 'editId']);
        $this->kategori = 'ringan';
    }

    public function toggle(ViolationType $type): void
    {
        $this->authorize('tatib.update');

        $type->update(['aktif' => ! $type->aktif]);
    }

    public function render(): View
    {
        return view('livewire.tatib.violation-type-index', [
            'items' => ViolationType::orderBy('kategori')->orderByDesc('poin')->get(),
        ])->layout('layouts.app', ['title' => 'Jenis Pelanggaran']);
    }
}
