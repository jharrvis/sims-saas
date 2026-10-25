<?php

declare(strict_types=1);

namespace App\Livewire\Platform;

use App\Domains\Platform\Services\TenantProvisioningService;
use App\Models\Plan;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah tenant manual oleh operator platform.
 * Memakai ulang TenantProvisioningService (sama seperti /daftar):
 * sekolah + trial + admin + modul inti. Nomor WA admin boleh sudah
 * terdaftar (identitas global) — akan ditambah membership-nya.
 */
class TenantCreate extends Component
{
    public string $nama = '';

    public string $jenjang = '';

    public string $npsn = '';

    public string $admin_nama = '';

    public string $admin_phone = '';

    public string $plan_slug = 'basic';

    public function simpan(TenantProvisioningService $provisioning): void
    {
        $operator = auth()->user();

        abort_unless(
            $operator instanceof User
                && $operator->is_platform
                && $operator->hasPermissionTo('platform.tenant.create', 'platform'),
            403,
        );

        $this->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jenjang' => ['required', 'in:SD,SMP,SMA,SMK'],
            'npsn' => ['nullable', 'string', 'max:20', 'unique:schools,npsn'],
            'admin_nama' => ['required', 'string', 'max:100'],
            'admin_phone' => ['required', 'regex:/^62[0-9]{9,13}$/'],
            'plan_slug' => ['required', 'exists:plans,slug'],
        ], [
            'admin_phone.regex' => 'Nomor WA harus diawali 62, mis. 6281234567890.',
        ]);

        $school = $provisioning->provision([
            'nama' => $this->nama,
            'jenjang' => $this->jenjang,
            'npsn' => $this->npsn !== '' ? $this->npsn : null,
            'admin_nama' => $this->admin_nama,
            'admin_phone' => $this->admin_phone,
            'plan_slug' => $this->plan_slug,
        ]);

        session()->flash('ok', "Tenant {$school->nama} berhasil dibuat dengan trial 30 hari.");

        $this->redirect(route('platform.tenants.show', $school->id), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.platform.tenant-create', [
            'plans' => Plan::orderBy('harga_bulanan')->get(),
        ])->layout('layouts.platform', ['title' => 'Tambah Tenant']);
    }
}
