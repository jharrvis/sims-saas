<?php

declare(strict_types=1);

namespace App\Livewire\Pengaturan;

use App\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Profil sekolah (nama, NPSN, alamat, kontak).
 */
class SchoolProfile extends Component
{
    public string $nama = '';

    public string $npsn = '';

    public string $jenjang = 'SMA';

    public string $alamat = '';

    public string $telepon = '';

    public string $email = '';

    public function mount(TenantContext $context): void
    {
        $this->authorize('sekolah.view');

        $school = $context->school();
        $this->nama = $school->nama;
        $this->npsn = $school->npsn ?? '';
        $this->jenjang = $school->jenjang;
        $this->alamat = $school->alamat ?? '';
        $this->telepon = $school->telepon ?? '';
        $this->email = $school->email ?? '';
    }

    public function save(TenantContext $context): void
    {
        $this->authorize('sekolah.update');

        $data = $this->validate([
            'nama' => ['required', 'string', 'max:150'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'jenjang' => ['required', Rule::in(['SD', 'SMP', 'SMA', 'SMK'])],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
        ]);

        $context->school()->update($data);

        session()->flash('ok', 'Profil sekolah diperbarui.');
    }

    public function render(): View
    {
        return view('livewire.pengaturan.school-profile')
            ->layout('layouts.app', ['title' => 'Profil Sekolah']);
    }
}
