<?php

declare(strict_types=1);

namespace App\Livewire\Onboarding;

use App\Domains\Platform\Services\TenantProvisioningService;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Pendaftaran mandiri sekolah baru → trial 30 hari (publik, tanpa login).
 */
class DaftarSekolah extends Component
{
    public string $nama = '';

    public string $jenjang = 'SD';

    public string $npsn = '';

    public string $admin_nama = '';

    public string $admin_phone = '';

    public bool $selesai = false;

    public string $namaSekolahTerdaftar = '';

    public function daftar(TenantProvisioningService $provisioning): void
    {
        $this->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jenjang' => ['required', 'in:SD,SMP,SMA,SMK'],
            'npsn' => ['nullable', 'string', 'max:20', 'unique:schools,npsn'],
            'admin_nama' => ['required', 'string', 'max:100'],
            'admin_phone' => ['required', 'regex:/^62[0-9]{9,13}$/', 'unique:users,phone'],
        ], [
            'admin_phone.regex' => 'Nomor WA harus diawali 62, mis. 6281234567890.',
            'admin_phone.unique' => 'Nomor WA ini sudah terdaftar. Silakan masuk.',
        ]);

        $school = $provisioning->provision([
            'nama' => $this->nama,
            'jenjang' => $this->jenjang,
            'npsn' => $this->npsn !== '' ? $this->npsn : null,
            'admin_nama' => $this->admin_nama,
            'admin_phone' => $this->admin_phone,
        ]);

        $this->namaSekolahTerdaftar = $school->nama;
        $this->selesai = true;
    }

    public function render(): View
    {
        return view('livewire.onboarding.daftar-sekolah')
            ->layout('layouts.guest', ['title' => 'Daftar Trial Gratis']);
    }
}
