<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\AcademicYear;
use App\Models\Rombel;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar rombel per tahun ajaran (FR-401).
 */
class RombelIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $academicYearId = null;

    public function mount(): void
    {
        $this->academicYearId = AcademicYear::where('aktif', true)->value('id');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $rombels = Rombel::query()
            ->with(['waliKelas:id,nama', 'academicYear:id,nama'])
            ->withCount(['students as siswa_count' => fn ($q) => $q->wherePivot('status', 'aktif')])
            ->when($this->academicYearId, fn ($q) => $q->where('academic_year_id', $this->academicYearId))
            ->when($this->search !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->search.'%'))
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->paginate(15);

        return view('livewire.akademik.rombel-index', [
            'rombels' => $rombels,
            'tahunAjaran' => AcademicYear::orderByDesc('tgl_mulai')->get(),
        ])->layout('layouts.app', ['title' => 'Data Kelas']);
    }
}
