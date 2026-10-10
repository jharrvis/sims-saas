<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Domains\Akademik\Services\GradeCalculationService;
use App\Models\AcademicYear;
use App\Models\GradeRelease;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Leger nilai per rombel + mapel, dengan rilis ke PWA (FR-706, FR-707).
 */
class GradeLeger extends Component
{
    public ?int $rombelId = null;

    public ?int $subjectId = null;

    public ?int $semesterId = null;

    public function mount(): void
    {
        $this->semesterId = Semester::where('aktif', true)->value('id');
        $taId = AcademicYear::where('aktif', true)->value('id');
        $this->rombelId = Rombel::when($taId, fn ($q) => $q->where('academic_year_id', $taId))
            ->orderBy('nama')->value('id');
    }

    public function release(GradeCalculationService $calc): void
    {
        $this->authorize('nilai.release');

        if (! $this->rombelId || ! $this->semesterId) {
            return;
        }

        GradeRelease::updateOrCreate(
            ['semester_id' => $this->semesterId, 'rombel_id' => $this->rombelId],
            ['dirilis_pada' => now(), 'dirilis_oleh' => auth()->id()],
        );

        session()->flash('released', 'Nilai dirilis — sekarang tampil di PWA siswa/orang tua.');
    }

    public function render(): View
    {
        $taId = AcademicYear::where('aktif', true)->value('id');
        $calc = app(GradeCalculationService::class);

        $leger = ($this->rombelId && $this->subjectId && $this->semesterId)
            ? $calc->leger($this->rombelId, $this->subjectId, $this->semesterId)
            : collect();

        $released = $this->rombelId && $this->semesterId
            ? GradeRelease::where('rombel_id', $this->rombelId)
                ->where('semester_id', $this->semesterId)
                ->exists()
            : false;

        return view('livewire.akademik.grade-leger', [
            'rombels' => Rombel::when($taId, fn ($q) => $q->where('academic_year_id', $taId))
                ->orderBy('nama')->get(),
            'subjects' => Subject::orderBy('nama')->get(),
            'semesters' => Semester::orderByDesc('tgl_mulai')->get(),
            'leger' => $leger,
            'released' => $released,
        ])->layout('layouts.app', ['title' => 'Leger Nilai']);
    }
}
