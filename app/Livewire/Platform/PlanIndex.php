<?php

declare(strict_types=1);

namespace App\Livewire\Platform;

use App\Models\Plan;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

class PlanIndex extends Component
{
    public ?int $editId = null;

    public string $nama = '';

    public string $slug = '';

    public string $deskripsi = '';

    public string $harga_bulanan = '';

    public string $harga_tahunan = '';

    public string $max_siswa = '';

    public bool $aktif = true;

    public bool $showForm = false;

    public function edit(?int $id): void
    {
        $this->ensurePlatformPermission('platform.plan.manage');
        $this->reset(['nama', 'slug', 'deskripsi', 'harga_bulanan', 'harga_tahunan', 'max_siswa']);
        $this->editId = $id;
        $this->aktif = true;
        $this->showForm = true;

        if ($id) {
            $p = Plan::findOrFail($id);
            $this->nama = $p->nama;
            $this->slug = $p->slug;
            $this->deskripsi = (string) $p->deskripsi;
            $this->harga_bulanan = (string) $p->harga_bulanan;
            $this->harga_tahunan = (string) ($p->harga_tahunan ?? '');
            $this->max_siswa = (string) ($p->max_siswa ?? '');
            $this->aktif = (bool) $p->aktif;
        }
    }

    public function simpan(): void
    {
        $this->ensurePlatformPermission('platform.plan.manage');

        $this->validate([
            'nama' => ['required', 'string', 'max:50'],
            'slug' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:plans,slug'.($this->editId ? ','.$this->editId : '')],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'harga_bulanan' => ['required', 'integer', 'min:0'],
            'harga_tahunan' => ['nullable', 'integer', 'min:0'],
            'max_siswa' => ['nullable', 'integer', 'min:0'],
            'aktif' => ['boolean'],
        ]);

        $data = [
            'nama' => $this->nama,
            'slug' => $this->slug,
            'deskripsi' => $this->deskripsi !== '' ? $this->deskripsi : null,
            'harga_bulanan' => (int) $this->harga_bulanan,
            'harga_tahunan' => $this->harga_tahunan !== '' ? (int) $this->harga_tahunan : null,
            'max_siswa' => $this->max_siswa !== '' ? (int) $this->max_siswa : null,
            'aktif' => $this->aktif,
        ];

        if ($this->editId) {
            Plan::findOrFail($this->editId)->update($data);
        } else {
            Plan::create($data);
        }

        $this->edit(null);
        $this->showForm = false;
        session()->flash('ok', 'Paket disimpan.');
    }

    public function closeForm(): void
    {
        $this->reset(['editId', 'nama', 'slug', 'deskripsi', 'harga_bulanan', 'harga_tahunan', 'max_siswa', 'showForm']);
        $this->aktif = true;
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.platform.plan-index', [
            'plans' => Plan::orderBy('harga_bulanan')->get(),
        ])->layout('layouts.platform', ['title' => 'Paket Langganan']);
    }

    private function ensurePlatformPermission(string $permission): User
    {
        $operator = auth()->user();

        abort_unless(
            $operator instanceof User
                && $operator->is_platform
                && $operator->hasPermissionTo($permission, 'platform'),
            403,
        );

        return $operator;
    }
}
