<?php

declare(strict_types=1);

namespace App\Livewire\Aset;

use App\Models\Asset;
use App\Models\AssetCategory;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah aset.
 */
class AssetForm extends Component
{
    public ?Asset $asset = null;

    public ?int $asset_category_id = null;

    public string $kode = '';

    public string $nama = '';

    public string $kondisi = 'baik';

    public string $lokasi = '';

    public string $tgl_beli = '';

    public string $harga_beli = '';

    public string $sumber_dana = '';

    public string $new_kategori_kode = '';

    public string $new_kategori_nama = '';

    public function mount(?Asset $asset = null): void
    {
        $this->authorize($asset?->exists ? 'aset.update' : 'aset.create');

        if ($asset?->exists) {
            $this->asset = $asset;
            $this->asset_category_id = $asset->asset_category_id;
            $this->kode = $asset->kode;
            $this->nama = $asset->nama;
            $this->kondisi = $asset->kondisi;
            $this->lokasi = $asset->lokasi ?? '';
            /** @var Carbon|null $tglBeli */
            $tglBeli = $asset->tgl_beli;
            $this->tgl_beli = $tglBeli?->toDateString() ?? '';
            $this->harga_beli = $asset->harga_beli !== null ? (string) $asset->harga_beli : '';
            $this->sumber_dana = $asset->sumber_dana ?? '';
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'asset_category_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
            'kode' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:200'],
            'kondisi' => ['required', Rule::in(array_keys(Asset::KONDISI))],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'tgl_beli' => ['nullable', 'date'],
            'harga_beli' => ['nullable', 'numeric', 'min:0'],
            'sumber_dana' => ['nullable', 'string', 'max:100'],
            'new_kategori_kode' => ['nullable', 'string', 'max:30'],
            'new_kategori_nama' => ['nullable', 'string', 'max:150'],
        ]);

        $categoryId = $data['asset_category_id'];

        if ($categoryId === null && $data['new_kategori_nama'] !== null && $data['new_kategori_nama'] !== '') {
            $kode = $data['new_kategori_kode'] !== null && $data['new_kategori_kode'] !== ''
                ? $data['new_kategori_kode']
                : strtoupper(substr((string) preg_replace('/[^A-Za-z]/', '', (string) $data['new_kategori_nama']), 0, 5));

            $categoryId = AssetCategory::firstOrCreate(
                ['kode' => $kode],
                ['nama' => $data['new_kategori_nama']]
            )->id;
        }

        abort_if($categoryId === null, 422, 'Kategori wajib diisi atau dibuat baru.');

        /** @var array<string,mixed> $payload */
        $payload = [
            'asset_category_id' => $categoryId,
            'kode' => $data['kode'],
            'nama' => $data['nama'],
            'kondisi' => $data['kondisi'],
            'lokasi' => $data['lokasi'] ?: null,
            'tgl_beli' => $data['tgl_beli'] ?: null,
            'harga_beli' => $data['harga_beli'] !== null && $data['harga_beli'] !== '' ? (float) $data['harga_beli'] : null,
            'sumber_dana' => $data['sumber_dana'] ?: null,
        ];

        if ($this->asset?->exists) {
            $this->asset->update($payload);
            session()->flash('ok', 'Aset diperbarui.');
        } else {
            $asset = Asset::create($payload);
            $asset->catatMutasi('masuk', ['ke' => $payload['lokasi'], 'catatan' => 'Pengadaan awal'], auth()->id());
            session()->flash('ok', 'Aset ditambahkan.');
        }

        $this->redirectRoute('aset.index');
    }

    public function render(): View
    {
        return view('livewire.aset.asset-form', [
            'categories' => AssetCategory::orderBy('nama')->get(['id', 'nama']),
            'isEdit' => (bool) $this->asset?->exists,
        ])->layout('layouts.app', ['title' => $this->asset?->exists ? 'Ubah Aset' : 'Tambah Aset']);
    }
}
