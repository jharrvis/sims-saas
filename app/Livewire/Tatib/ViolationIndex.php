<?php

declare(strict_types=1);

namespace App\Livewire\Tatib;

use App\Models\Violation;
use App\Tenancy\TenantContext;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar pelanggaran + rekap poin per siswa.
 */
class ViolationIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $kategori = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingKategori(): void
    {
        $this->resetPage();
    }

    public function destroy(Violation $violation): void
    {
        $this->authorize('tatib.delete');

        $violation->delete();
        session()->flash('ok', 'Catatan pelanggaran dihapus.');
    }

    public function render(): View
    {
        $items = Violation::with(['student:id,nis,nama', 'type:id,nama,kategori,poin'])
            ->when($this->search !== '', fn ($q) => $q->whereHas('student', fn ($w) => $w->where('nama', 'like', '%'.$this->search.'%')))
            ->when($this->kategori !== '', fn ($q) => $q->whereHas('type', fn ($w) => $w->where('kategori', $this->kategori)))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15);

        $rekap = Violation::join('violation_types as t', 't.id', '=', 'violations.violation_type_id')
            ->join('students as s', 's.id', '=', 'violations.student_id')
            ->where('violations.school_id', app(TenantContext::class)->requireSchoolId())
            ->groupBy('s.id', 's.nis', 's.nama')
            ->orderByDesc('total_poin')
            ->limit(10)
            ->selectRaw('s.nis, s.nama, SUM(t.poin) as total_poin, COUNT(*) as jumlah')
            ->get();

        return view('livewire.tatib.violation-index', ['items' => $items, 'rekap' => $rekap])
            ->layout('layouts.app', ['title' => 'Tata Tertib']);
    }
}
