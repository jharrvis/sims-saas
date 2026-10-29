<?php

declare(strict_types=1);

namespace App\Livewire\Kepegawaian;

use App\Domains\Kepegawaian\Services\EmployeeAccountSync;
use App\Domains\Kesiswaan\Services\StudentPhotoService;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar guru & pegawai dengan pencarian + filter jenis/status (FR-301).
 * Akses dibatasi permission guru.view via route middleware.
 *
 * Status filter memetai eksplisit ke kolom boolean status_aktif
 * ('aktif' => true, 'nonaktif' => false).
 *
 * Mode sampah: hapus = soft delete; pulihkan / hapus permanen hanya dari
 * tampilan sampah. Foto TIDAK dihapus saat soft delete karena data masih dapat
 * dipulihkan; file baru dibuang setelah baris database benar-benar hilang.
 * Seluruh operasi (hapus, daftar sampah, pulihkan, hapus permanen) tetap
 * tenant-scoped lewat global scope SchoolScope.
 */
class EmployeeIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $jenis = '';

    /** '' = semua, 'aktif' = status_aktif true, 'nonaktif' = status_aktif false. */
    public string $status = '';

    public bool $showTrashed = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingJenis(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Pembaruan properti langsung (mis. ->set('showTrashed', true)) tidak
     * melewati toggleTrashed(), jadi otorisasi harus ditegakkan di sini:
     * membuka tampilan sampah membutuhkan permission guru.delete.
     * Menutup sampah (false) tidak membutuhkan permission.
     */
    public function updatingShowTrashed(bool $value): void
    {
        if ($value) {
            $this->authorize('guru.delete');
            $this->resetPage();
        }
    }

    public function toggleTrashed(): void
    {
        $this->authorize('guru.delete');

        $this->showTrashed = ! $this->showTrashed;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->jenis = '';
        $this->status = '';
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->authorize('guru.delete');

        // Lookup setelah authorization; SchoolScope menjaga tetap satu tenant.
        $employee = Employee::findOrFail($id);
        $employee->delete();

        // Akun login tertaut ikut kehilangan akses selama data di sampah (QA B-01).
        app(EmployeeAccountSync::class)->syncMembershipStatus($employee);

        session()->flash('sukses', 'Data guru/pegawai dipindah ke sampah.');
    }

    public function restore(int $id): void
    {
        $this->authorize('guru.delete');

        $employee = Employee::onlyTrashed()->findOrFail($id);
        $employee->restore();

        // Akses akun tertaut dipulihkan mengikuti status_aktif pegawai (QA B-01).
        app(EmployeeAccountSync::class)->syncMembershipStatus($employee);

        session()->flash('sukses', "Data {$employee->nama} dipulihkan.");
    }

    public function forceDelete(int $id): void
    {
        // Hapus permanen adalah aksi terpisah dari soft delete (QA B-03).
        $this->authorize('guru.force-delete');

        $employee = Employee::onlyTrashed()->findOrFail($id);

        // FK schedules.employee_id bersifat restrictOnDelete: tanpa cek ini
        // pengguna menerima error 500 mentah dari QueryException.
        $jadwal = $employee->schedules()->count();
        if ($jadwal > 0) {
            session()->flash('gagal', "Data {$employee->nama} tidak dapat dihapus permanen karena masih memiliki {$jadwal} jadwal mengajar. Pindahkan atau hapus jadwal tersebut terlebih dahulu.");

            return;
        }

        // Rombel binaan tidak boleh kehilangan wali kelas secara diam-diam
        // lewat nullOnDelete.
        $rombelBinaan = $employee->rombelsAsWali()->count();
        if ($rombelBinaan > 0) {
            session()->flash('gagal', "Data {$employee->nama} tidak dapat dihapus permanen karena masih tercatat sebagai wali kelas {$rombelBinaan} rombel. Tunjuk wali kelas pengganti terlebih dahulu.");

            return;
        }

        $punyaAkun = $employee->user !== null;

        // Simpan path lebih dulu, force delete, lalu buang file: kegagalan pada
        // operasi database tidak menghapus foto yang masih terpasang di storage.
        $fotoPath = $employee->foto_path;

        DB::transaction(function () use ($employee): void {
            // Nasib akun tertaut diputuskan eksplisit: akses sekolah
            // dinonaktifkan (QA B-01), lalu FK nullOnDelete melepas tautan
            // employee_id pada baris user saat data pegawai hilang.
            app(EmployeeAccountSync::class)->syncMembershipStatus($employee);
            $employee->forceDelete();
        });

        app(StudentPhotoService::class)->delete($fotoPath);

        session()->flash('sukses', $punyaAkun
            ? "Data {$employee->nama} dihapus permanen. Akun login tertaut dinonaktifkan dan tautannya dilepas."
            : 'Data guru/pegawai dihapus permanen.');
    }

    public function render(): View
    {
        // Pertahanan lapis kedua: state sampah tidak boleh dirender untuk
        // pengguna tanpa permission delete, apa pun jalurnya sampai ke sini.
        if ($this->showTrashed && ! auth()->user()?->can('guru.delete')) {
            abort(403);
        }

        $employees = Employee::query()
            ->with('user')
            ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
            ->when($this->search !== '', fn ($q) => $q->where(function ($w): void {
                $w->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('nip_nuptk', 'like', '%'.$this->search.'%');
            }))
            ->when($this->jenis !== '', fn ($q) => $q->where('jenis', $this->jenis))
            ->when($this->status === 'aktif', fn ($q) => $q->where('status_aktif', true))
            ->when($this->status === 'nonaktif', fn ($q) => $q->where('status_aktif', false))
            ->orderBy('nama')
            ->paginate(15);

        // Satu query agregat (tenant-scoped, tanpa data sampah) untuk ringkasan.
        $ringkasan = Employee::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status_aktif = 1 then 1 else 0 end) as aktif')
            ->selectRaw("sum(case when jenis in ('guru_mapel', 'guru_kelas') then 1 else 0 end) as pengajar")
            ->selectRaw("sum(case when jenis = 'tendik' then 1 else 0 end) as tendik")
            ->first();

        return view('livewire.kepegawaian.employee-index', [
            'employees' => $employees,
            'stats' => [
                'total' => (int) ($ringkasan?->total ?? 0),
                'aktif' => (int) ($ringkasan?->aktif ?? 0),
                'pengajar' => (int) ($ringkasan?->pengajar ?? 0),
                'tendik' => (int) ($ringkasan?->tendik ?? 0),
            ],
            'trashedCount' => Employee::onlyTrashed()->count(),
        ])->layout('layouts.app', ['title' => 'Data Guru']);
    }
}
