<?php

declare(strict_types=1);

namespace App\Livewire\Transportasi;

use App\Models\TransportRoute;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah rute antar-jemput.
 */
class RouteForm extends Component
{
    public ?TransportRoute $route = null;

    public string $nama = '';

    public string $sopir = '';

    public string $no_kendaraan = '';

    public string $kapasitas = '';

    public bool $aktif = true;

    public function mount(?TransportRoute $route = null): void
    {
        $this->authorize($route?->exists ? 'transportasi.update' : 'transportasi.create');

        if ($route?->exists) {
            $this->route = $route;
            $this->nama = $route->nama;
            $this->sopir = $route->sopir ?? '';
            $this->no_kendaraan = $route->no_kendaraan ?? '';
            $this->kapasitas = (string) $route->kapasitas;
            $this->aktif = $route->aktif;
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'nama' => ['required', 'string', 'max:150'],
            'sopir' => ['nullable', 'string', 'max:150'],
            'no_kendaraan' => ['nullable', 'string', 'max:30'],
            'kapasitas' => ['required', 'integer', 'min:0', 'max:100'],
            'aktif' => ['boolean'],
        ]);

        /** @var array<string,mixed> $payload */
        $payload = [
            'nama' => $data['nama'],
            'sopir' => $data['sopir'] ?: null,
            'no_kendaraan' => $data['no_kendaraan'] ?: null,
            'kapasitas' => (int) $data['kapasitas'],
            'aktif' => (bool) $data['aktif'],
        ];

        if ($this->route?->exists) {
            $this->route->update($payload);
            session()->flash('ok', 'Rute diperbarui.');
        } else {
            TransportRoute::create($payload);
            session()->flash('ok', 'Rute ditambahkan.');
        }

        $this->redirectRoute('transportasi.index');
    }

    public function render(): View
    {
        return view('livewire.transportasi.route-form', [
            'isEdit' => (bool) $this->route?->exists,
        ])->layout('layouts.app', ['title' => $this->route?->exists ? 'Ubah Rute' : 'Tambah Rute']);
    }
}
