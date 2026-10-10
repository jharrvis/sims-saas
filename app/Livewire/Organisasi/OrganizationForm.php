<?php

declare(strict_types=1);

namespace App\Livewire\Organisasi;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah organisasi.
 */
class OrganizationForm extends Component
{
    public ?Organization $organization = null;

    public string $nama = '';

    public string $jenis = 'lainnya';

    public ?int $pembina_id = null;

    public bool $aktif = true;

    public function mount(?Organization $organization = null): void
    {
        $this->authorize($organization?->exists ? 'organisasi.update' : 'organisasi.create');

        if ($organization?->exists) {
            $this->organization = $organization;
            $this->nama = $organization->nama;
            $this->jenis = $organization->jenis;
            $this->pembina_id = $organization->pembina_id;
            $this->aktif = $organization->aktif;
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jenis' => ['required', Rule::in(array_keys(Organization::JENIS))],
            'pembina_id' => ['nullable', 'integer', 'exists:employees,id'],
            'aktif' => ['boolean'],
        ]);

        if ($this->organization?->exists) {
            $this->organization->update($data);
            session()->flash('ok', 'Organisasi diperbarui.');
        } else {
            Organization::create($data);
            session()->flash('ok', 'Organisasi ditambahkan.');
        }

        $this->redirectRoute('organisasi.index');
    }

    public function render(): View
    {
        return view('livewire.organisasi.organization-form', [
            'pembinas' => Employee::where('status_aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'isEdit' => (bool) $this->organization?->exists,
        ])->layout('layouts.app', ['title' => $this->organization?->exists ? 'Ubah Organisasi' : 'Tambah Organisasi']);
    }
}
