<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Input nilai per rombel + mapel + komponen (FR-702, FR-703).
 */
class GradeInput extends Component
{
    public ?int $rombelId = null;

    public ?int $subjectId = null;

    public ?int $componentId = null;

    public ?int $semesterId = null;

    /** @var array<int, ?float> student_id => nilai */
    public array $scores = [];

    public function mount(): void
    {
        $this->semesterId = Semester::where('aktif', true)->value('id');
        $taId = AcademicYear::where('aktif', true)->value('id');
        $this->rombelId = Rombel::when($taId, fn ($q) => $q->where('academic_year_id', $taId))
            ->orderBy('nama')->value('id');

        $this->loadScores();
    }

    public function updatedRombelId(): void
    {
        $this->loadScores();
    }

    public function updatedSubjectId(): void
    {
        $this->componentId = GradeComponent::where('subject_id', $this->subjectId)
            ->orderBy('urutan')->value('id')
            ?? GradeComponent::whereNull('subject_id')->orderBy('urutan')->value('id');

        $this->loadScores();
    }

    public function updatedComponentId(): void
    {
        $this->loadScores();
    }

    public function updatedSemesterId(): void
    {
        $this->loadScores();
    }

    private function loadScores(): void
    {
        $this->scores = [];

        if (! $this->rombelId || ! $this->subjectId || ! $this->componentId || ! $this->semesterId) {
            return;
        }

        $existing = Grade::where('subject_id', $this->subjectId)
            ->where('grade_component_id', $this->componentId)
            ->where('semester_id', $this->semesterId)
            ->get()
            ->keyBy('student_id');

        foreach ($this->students() as $s) {
            $this->scores[$s->id] = $existing->get($s->id)?->nilai !== null
                ? (float) $existing->get($s->id)->nilai
                : null;
        }
    }

    public function save(): void
    {
        $this->authorize('nilai.input');

        if (! $this->rombelId || ! $this->subjectId || ! $this->componentId || ! $this->semesterId) {
            return;
        }

        $this->validate([
            'scores.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($this->students() as $s) {
            $nilai = $this->scores[$s->id] ?? null;
            if ($nilai === null) {
                continue;
            }

            Grade::updateOrCreate(
                [
                    'student_id' => $s->id,
                    'subject_id' => $this->subjectId,
                    'grade_component_id' => $this->componentId,
                    'semester_id' => $this->semesterId,
                ],
                [
                    'nilai' => $nilai,
                    'input_by' => auth()->id(),
                ],
            );
        }

        $this->loadScores();
        session()->flash('saved', 'Nilai tersimpan.');
    }

    /**
     * @return Collection<int, Student>
     */
    private function students()
    {
        return Rombel::find($this->rombelId)?->students()
            ->wherePivot('status', 'aktif')
            ->orderBy('nama')
            ->get() ?? collect();
    }

    public function render(): View
    {
        $taId = AcademicYear::where('aktif', true)->value('id');

        return view('livewire.akademik.grade-input', [
            'rombels' => Rombel::when($taId, fn ($q) => $q->where('academic_year_id', $taId))
                ->orderBy('nama')->get(),
            'subjects' => Subject::orderBy('nama')->get(),
            'components' => GradeComponent::when($this->subjectId,
                fn ($q) => $q->where(fn ($w) => $w->where('subject_id', $this->subjectId)->orWhereNull('subject_id')),
                fn ($q) => $q->whereNull('subject_id'))
                ->orderBy('urutan')->get(),
            'semesters' => Semester::orderByDesc('tgl_mulai')->get(),
            'students' => $this->rombelId ? $this->students() : collect(),
        ])->layout('layouts.app', ['title' => 'Input Nilai']);
    }
}
