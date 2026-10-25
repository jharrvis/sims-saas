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
 * Statistik dihitung di query layer (agregasi skalar database), bukan di
 * Blade, tanpa N+1, dan mengikuti filter tahun ajaran yang sedang aktif:
 * siswa dihitung distinct agar penempatan aktif di dua tahun tidak
 * terhitung ganda, dan rombel tahun lama tidak masuk ringkasan tahun
 * berjalan. Penempatan hanya dihitung bila tahun pivot sama dengan tahun
 * rombelnya, agar pivot lintas tahun yang usang tidak bocor ke hitungan.
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
            ->withCount(['students as siswa_count' => fn ($q) => $q->where('class_student.status', 'aktif')
                ->whereColumn('class_student.academic_year_id', 'rombels.academic_year_id')])
            ->when($this->academicYearId, fn ($q) => $q->where('academic_year_id', $this->academicYearId))
            ->when($this->tingkat !== '', fn ($q) => $q->where('tingkat', $this->tingkat))
            ->when($this->search !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->search.'%'))
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->paginate(15);

        // Statistik mengikuti filter tahun ajaran yang sedang aktif (null =
        // semua tahun). Empat agregasi skalar, tenant-scoped via SchoolScope;
        // tidak ada model rombel yang dimuat ke memori.
        $tahun = $this->academicYearId;
        $rombelScope = fn () => Rombel::query()
            ->when($tahun, fn ($q) => $q->where('academic_year_id', $tahun));

        $stats = [
            'total' => $rombelScope()->count(),
            'siswa' => Rombel::query()
                ->join('class_student', 'class_student.rombel_id', '=', 'rombels.id')
                ->where('class_student.status', 'aktif')
                ->whereColumn('class_student.academic_year_id', 'rombels.academic_year_id')
                ->when($tahun, fn ($q) => $q->where('rombels.academic_year_id', $tahun))
                ->distinct()
                ->count('class_student.student_id'),
            'tanpaWali' => $rombelScope()->whereNull('wali_kelas_id')->count(),
            'penuh' => $rombelScope()->whereRaw(
                '(select count(*) from class_student where class_student.rombel_id = rombels.id and class_student.status = ? and class_student.academic_year_id = rombels.academic_year_id) >= rombels.kapasitas',
                ['aktif']
            )->count(),
        ];

        $tahunAjaran = AcademicYear::orderByDesc('tgl_mulai')->get();
        $tahunDipilih = $tahunAjaran->firstWhere('id', $tahun);

        return view('livewire.akademik.rombel-index', [
            'rombels' => $rombels,
            'tahunAjaran' => $tahunAjaran,
            'tahunAktifId' => AcademicYear::where('aktif', true)->value('id'),
            'tingkatList' => Rombel::query()->distinct()->orderBy('tingkat')->pluck('tingkat'),
            'stats' => $stats,
            'statsScope' => $tahunDipilih ? 'Tahun ajaran '.$tahunDipilih->nama : 'Seluruh tahun ajaran',
        ])->layout('layouts.app', ['title' => 'Data Kelas']);
    }
}
