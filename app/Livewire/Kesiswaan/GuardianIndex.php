<?php

declare(strict_types=1);

namespace App\Livewire\Kesiswaan;

use App\Models\Guardian;
use App\Support\AuditLogger;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar wali murid dengan pencarian + filter hubungan (FR-302).
 * Akses dibatasi permission wali-murid.view via route middleware.
 *
 * Mode sampah: hapus = soft delete; pulihkan / hapus permanen hanya dari
 * tampilan sampah. Seluruh operasi tetap tenant-scoped lewat global
 * scope SchoolScope.
 */
class GuardianIndex extends Component
{
    use WithPagination;

    public string $search = '';

    /** '' = semua, sisanya salah satu dari ayah/ibu/wali. */
    public string $hubungan = '';

    public bool $showTrashed = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingHubungan(): void
    {
        $this->resetPage();
    }

    /**
     * Pembaruan properti langsung (mis. ->set('showTrashed', true)) tidak
     * melewati toggleTrashed(), jadi otorisasi harus ditegakkan di sini:
     * membuka tampilan sampah membutuhkan permission wali-murid.delete.
     * Menutup sampah (false) tidak membutuhkan permission.
     */
    public function updatingShowTrashed(bool $value): void
    {
        if ($value) {
            $this->authorize('wali-murid.delete');
            $this->resetPage();
        }
    }

    public function toggleTrashed(): void
    {
        $this->authorize('wali-murid.delete');

        $this->showTrashed = ! $this->showTrashed;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->hubungan = '';
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->authorize('wali-murid.delete');

        // Lookup setelah authorization; SchoolScope menjaga tetap satu tenant.
        $guardian = Guardian::findOrFail($id);
        $guardian->delete();

        AuditLogger::log('wali.delete', $guardian, ['nama' => $guardian->nama]);

        session()->flash('sukses', 'Data wali murid dipindah ke sampah.');
    }

    public function restore(int $id): void
    {
        $this->authorize('wali-murid.delete');

        $guardian = Guardian::onlyTrashed()->findOrFail($id);
        $guardian->restore();

        session()->flash('sukses', "Data {$guardian->nama} dipulihkan.");
    }

    public function forceDelete(int $id): void
    {
        $this->authorize('wali-murid.delete');

        $guardian = Guardian::onlyTrashed()->findOrFail($id);
        $guardian->forceDelete();

        AuditLogger::log('wali.force_delete', $guardian, ['nama' => $guardian->nama]);

        session()->flash('sukses', 'Data wali murid dihapus permanen.');
    }

    public function render(): View
    {
        // Pertahanan lapis kedua: state sampah tidak boleh dirender untuk
        // pengguna tanpa permission delete, apa pun jalurnya sampai ke sini.
        if ($this->showTrashed && ! auth()->user()?->can('wali-murid.delete')) {
            abort(403);
        }

        $guardians = Guardian::query()
            ->withCount('students')
            ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
            ->when($this->search !== '', fn ($q) => $q->where(function ($w): void {
                $w->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('no_wa', 'like', '%'.$this->search.'%');
            }))
            ->when($this->hubungan !== '', fn ($q) => $q->where('hubungan', $this->hubungan))
            ->orderBy('nama')
            ->paginate(15);

        // Satu query agregat (tenant-scoped, tanpa data sampah) untuk ringkasan.
        $ringkasan = Guardian::query()
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when hubungan = 'ayah' then 1 else 0 end) as ayah")
            ->selectRaw("sum(case when hubungan = 'ibu' then 1 else 0 end) as ibu")
            ->selectRaw("sum(case when hubungan = 'wali' then 1 else 0 end) as wali")
            ->first();

        return view('livewire.kesiswaan.guardian-index', [
            'guardians' => $guardians,
            'stats' => [
                'total' => (int) ($ringkasan?->total ?? 0),
                'ayah' => (int) ($ringkasan?->ayah ?? 0),
                'ibu' => (int) ($ringkasan?->ibu ?? 0),
                'wali' => (int) ($ringkasan?->wali ?? 0),
            ],
            'trashedCount' => Guardian::onlyTrashed()->count(),
        ])->layout('layouts.app', ['title' => 'Data Wali']);
    }
}
