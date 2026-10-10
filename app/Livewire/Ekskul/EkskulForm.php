<?php

declare(strict_types=1);

namespace App\Livewire\Ekskul;

use App\Models\Employee;
use App\Models\Extracurricular;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah ekstrakurikuler.
 */
class EkskulForm extends Component
{
    public ?Extracurricular $ekskul = null;

    public string $nama = '';

    public string $jadwal = '';

    public ?int $pembina_id = null;

    public bool $aktif = true;

    public function mount(?Extracurricular $ekskul = null): void
    {
        $this->authorize($ekskul?->exists ? 'ekskul.update' : 'ekskul.create');

        if ($ekskul?->exists) {
            $this->ekskul = $ekskul;
            $this->nama = $ekskul->nama;
            $this->jadwal = $ekskul->jadwal ?? '';
            $this->pembina_id = $ekskul->pembina_id;
            $this->aktif = $ekskul->aktif;
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jadwal' => ['nullable', 'string', 'max:100'],
            'pembina_id' => ['nullable', 'integer', 'exists:employees,id'],
            'aktif' => ['boolean'],
        ]);

        if ($this->ekskul?->exists) {
            $this->ekskul->update($data);
            session()->flash('ok', 'Ekstrakurikuler diperbarui.');
        } else {
            Extracurricular::create($data);
            session()->flash('ok', 'Ekstrakurikuler ditambahkan.');
        }

        $this->redirectRoute('ekskul.index');
    }

    public function render(): View
    {
        return view('livewire.ekskul.ekskul-form', [
            'pembinas' => Employee::where('status_aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'isEdit' => (bool) $this->ekskul?->exists,
        ])->layout('layouts.app', ['title' => $this->ekskul?->exists ? 'Ubah Ekstrakurikuler' : 'Tambah Ekstrakurikuler']);
    }
}
