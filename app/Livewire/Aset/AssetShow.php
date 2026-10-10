<?php

declare(strict_types=1);

namespace App\Livewire\Aset;

use App\Models\Asset;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail aset + riwayat mutasi + catat mutasi.
 */
class AssetShow extends Component
{
    public Asset $asset;

    public string $jenis = 'pindah';

    public string $dari = '';

    public string $ke = '';

    public string $catatan = '';

    public string $tgl = '';

    public bool $showForm = false;

    public function mount(Asset $asset): void
    {
        $this->authorize('aset.view');
        $this->asset = $asset;
        $this->tgl = now()->toDateString();
    }

    public function simpanMutasi(): void
    {
        $this->authorize('aset.mutasi');

        $data = $this->validate([
            'jenis' => ['required', Rule::in(['pindah', 'kondisi', 'keluar'])],
            'dari' => ['nullable', 'string', 'max:150'],
            'ke' => ['nullable', 'string', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'tgl' => ['nullable', 'date'],
        ]);

        if ($data['jenis'] === 'kondisi') {
            $this->validate(['ke' => ['required', Rule::in(array_keys(Asset::KONDISI))]]);
        }

        $this->asset->catatMutasi($data['jenis'], [
            'dari' => $data['dari'] ?: null,
            'ke' => $data['ke'] ?: null,
            'catatan' => $data['catatan'] ?: null,
            'tgl' => $data['tgl'] ?: null,
        ], auth()->id());

        $this->asset->refresh();
        $this->reset(['dari', 'ke', 'catatan', 'showForm']);
        session()->flash('ok', 'Mutasi dicatat.');
    }

    public function render(): View
    {
        $mutations = $this->asset->mutations()
            ->orderByDesc('tgl')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.aset.asset-show', ['mutations' => $mutations])
            ->layout('layouts.app', ['title' => $this->asset->nama]);
    }
}
