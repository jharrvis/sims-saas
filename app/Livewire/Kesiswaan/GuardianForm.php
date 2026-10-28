<?php

declare(strict_types=1);

namespace App\Livewire\Kesiswaan;

use App\Models\Guardian;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah wali murid (FR-302).
 *
 * NIK tersimpan terenkripsi (cast 'encrypted' di model) dan tidak bisa
 * dicari lewat kolomnya; pencarian daftar memakai nama dan nomor WA.
 */
class GuardianForm extends Component
{
    public ?Guardian $guardian = null;

    public string $nik = '';

    public string $nama = '';

    public string $hubungan = 'ayah';

    public string $pekerjaan = '';

    public string $no_wa = '';

    public string $email = '';

    public string $alamat = '';

    public function mount(?Guardian $guardian = null): void
    {
        if ($guardian?->exists) {
            $this->guardian = $guardian;
            $this->nik = $guardian->nik ?? '';
            $this->nama = $guardian->nama;
            $this->hubungan = $guardian->hubungan;
            $this->pekerjaan = $guardian->pekerjaan ?? '';
            $this->no_wa = $guardian->no_wa;
            $this->email = $guardian->email ?? '';
            $this->alamat = $guardian->alamat ?? '';
        }
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        return [
            'nik' => ['nullable', 'string', 'max:25'],
            'nama' => ['required', 'string', 'max:150'],
            'hubungan' => ['required', Rule::in(['ayah', 'ibu', 'wali'])],
            'pekerjaan' => ['nullable', 'string', 'max:100'],
            'no_wa' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'alamat' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        // Authorization berjalan sebelum validasi: route middleware hanya
        // melindungi GET, bukan aksi Livewire.
        $this->authorize($this->guardian?->exists ? 'wali-murid.update' : 'wali-murid.create');

        $this->validate();

        $data = [
            'nik' => $this->nik ?: null,
            'nama' => $this->nama,
            'hubungan' => $this->hubungan,
            'pekerjaan' => $this->pekerjaan ?: null,
            'no_wa' => $this->no_wa,
            'email' => $this->email ?: null,
            'alamat' => $this->alamat ?: null,
        ];

        if ($this->guardian?->exists) {
            $this->guardian->update($data);
        } else {
            Guardian::create($data);
        }

        $this->redirect(route('wali.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.kesiswaan.guardian-form')
            ->layout('layouts.app', ['title' => $this->guardian?->exists ? 'Ubah Wali' : 'Tambah Wali']);
    }
}
