<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\AcademicYear;
use App\Models\Rombel;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar rombel per tahun ajaran (FR-401), port Batch 2 ke shared design
 * system: page-stack, management-page-header, summary stats, toolbar,
 * table card, pagination, empty/loading state (pola EmployeeIndex).
 * Statistik dihitung di PHP/query layer, bukan di Blade, tanpa N+1.
 */
class RombelIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $tingkat = '';

    public ?int $academicYearId = null;

    public function mount(): void
    {
        $this->academicYearId = AcademicYear::where('aktif', true)->value('id');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTingkat(): void
    {
        $this->resetPage();
    }

    public function updatingAcademicYearId(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        // Kembali ke state awal (tahun ajaran aktif), bukan ke "semua tahun":
        // pola yang sama dengan Employee/StudentIndex yang kembali ke tanpa filter.
        $this->search = '';
        $this->tingkat = '';
        $this->academicYearId = AcademicYear::where('aktif', true)->value('id');
        $this->resetPage();
    }

    public function render(): View
    {
        $rombels = Rombel::query()
            ->with(['waliKelas:id,nama', 'academicYear:id,nama'])
            ->withCount(['students as siswa_count' => fn ($q) => $q->where('class_student.status', 'aktif')])
            ->when($this->academicYearId, fn ($q) => $q->where('academic_year_id', $this->academicYearId))
            ->when($this->tingkat !== '', fn ($q) => $q->where('tingkat', $this->tingkat))
            ->when($this->search !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->search.'%'))
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->paginate(15);

        // Satu query agregat (tenant-scoped via SchoolScope, withCount sebagai
        // subquery — bukan N+1) untuk ringkasan, independen dari filter list.
        $semua = Rombel::query()
            ->withCount(['students as siswa_count' => fn ($q) => $q->where('class_student.status', 'aktif')])
            ->get();

        $stats = [
            'total' => $semua->count(),
            'siswa' => (int) $semua->sum('siswa_count'),
            'tanpaWali' => $semua->whereNull('wali_kelas_id')->count(),
            'penuh' => $semua->filter(fn (Rombel $r) => $r->siswa_count >= $r->kapasitas)->count(),
        ];

        return view('livewire.akademik.rombel-index', [
            'rombels' => $rombels,
            'tahunAjaran' => AcademicYear::orderByDesc('tgl_mulai')->get(),
            'tahunAktifId' => AcademicYear::where('aktif', true)->value('id'),
            'tingkatList' => Rombel::query()->distinct()->orderBy('tingkat')->pluck('tingkat'),
            'stats' => $stats,
        ])->layout('layouts.app', ['title' => 'Data Kelas']);
    }
}
