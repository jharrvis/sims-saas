<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\AcademicYear;
use App\Models\Rombel;
use App\Models\Schedule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Jadwal mingguan per rombel (FR-501).
 */
class ScheduleIndex extends Component
{
    public ?int $rombelId = null;

    public ?int $academicYearId = null;

    public function mount(): void
    {
        $this->academicYearId = AcademicYear::where('aktif', true)->value('id');
        $this->rombelId = Rombel::when($this->academicYearId, fn ($q) => $q->where('academic_year_id', $this->academicYearId))
            ->orderBy('nama')->value('id');
    }

    /**
     * @return array<string, array<int, Schedule>>
     */
    public function grid(): array
    {
        if (! $this->rombelId || ! $this->academicYearId) {
            return [];
        }

        $entries = Schedule::with(['subject:id,nama,kode', 'employee:id,nama'])
            ->where('rombel_id', $this->rombelId)
            ->where('academic_year_id', $this->academicYearId)
            ->orderBy('jam_ke')
            ->get()
            ->groupBy('hari')
            ->map(fn ($g) => $g->keyBy('jam_ke'))
            ->all();

        $maxJam = Schedule::where('rombel_id', $this->rombelId)
            ->where('academic_year_id', $this->academicYearId)
            ->max('jam_ke') ?? 0;

        $grid = [];
        foreach (Schedule::HARIS as $hari) {
            $grid[$hari] = [];
            for ($j = 1; $j <= max($maxJam, 1); $j++) {
                $grid[$hari][$j] = $entries[$hari][$j] ?? null;
            }
        }

        return $grid;
    }

    public function deleteEntry(int $id): void
    {
        $this->authorize('jadwal.delete');
        Schedule::findOrFail($id)->delete();
    }

    public function render(): View
    {
        return view('livewire.akademik.schedule-index', [
            'grid' => $this->grid(),
            'rombels' => Rombel::when($this->academicYearId, fn ($q) => $q->where('academic_year_id', $this->academicYearId))
                ->orderBy('nama')->get(),
        ])->layout('layouts.app', ['title' => 'Jadwal Pelajaran']);
    }
}
