<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Akademik\Services\ReleasedGradeService;
use App\Domains\Pwa\Services\PwaContext;
use App\Models\Semester;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Nilai Saya — P-03. Hanya nilai yang sudah dirilis (FR-707).
 */
class Nilai extends Component
{
    public ?int $semester_id = null;

    public function mount(PwaContext $pwa): void
    {
        $student = $pwa->activeStudent();

        $this->semester_id = Semester::where('academic_year_id', $student?->rombels()->wherePivot('status', 'aktif')->value('class_student.academic_year_id'))
            ->orderByDesc('id')
            ->value('id');
    }

    public function render(PwaContext $pwa, ReleasedGradeService $grades): View
    {
        $student = $pwa->activeStudent();

        $semesters = Semester::orderByDesc('id')->get(['id', 'nama', 'academic_year_id']);

        $hasil = null;
        if ($student && $this->semester_id) {
            $hasil = $grades->rapor($student->id, $this->semester_id);
        }

        return view('livewire.pwa.nilai', [
            'semesters' => $semesters,
            'hasil' => $hasil,
            'student' => $student,
        ])->layout('layouts.pwa', [
            'title' => 'Nilai',
            'schoolName' => $student?->school?->nama,
            'showChildSwitcher' => $pwa->role() === 'orang-tua',
            'children' => $pwa->children(),
            'activeStudentId' => $student?->id,
        ]);
    }
}
