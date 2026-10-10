<?php

declare(strict_types=1);

namespace App\Livewire\Lms;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail tugas: pengumpulan & penilaian.
 */
class AssignmentShow extends Component
{
    public Assignment $assignment;

    public ?int $student_id = null;

    public string $searchSiswa = '';

    public string $jawaban = '';

    /** @var array<int,string> nilai per submission */
    public array $nilai = [];

    /** @var array<int,string> feedback per submission */
    public array $feedback = [];

    public function mount(Assignment $assignment): void
    {
        $this->authorize('lms.view');
        $this->assignment = $assignment;
    }

    public function kumpul(): void
    {
        $this->authorize('lms.kumpul');

        $this->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'jawaban' => ['required', 'string', 'max:20000'],
        ]);

        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $this->assignment->id, 'student_id' => $this->student_id],
            ['jawaban' => $this->jawaban, 'tgl_kumpul' => now()]
        );

        $this->reset(['jawaban', 'student_id', 'searchSiswa']);
        session()->flash('ok', 'Tugas terkumpul.');
    }

    public function nilai(AssignmentSubmission $submission): void
    {
        $this->authorize('lms.nilai');

        $key = $submission->id;

        $this->validate([
            "nilai.$key" => ['required', 'numeric', 'min:0', 'max:100'],
            "feedback.$key" => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'nilai' => (float) $this->nilai[$key],
            'feedback' => $this->feedback[$key] ?? null,
        ]);

        session()->flash('ok', 'Nilai disimpan.');
    }

    public function render(): View
    {
        $submissions = $this->assignment->submissions()
            ->with('student:id,nis,nama')
            ->orderByDesc('tgl_kumpul')
            ->get();

        foreach ($submissions as $s) {
            $this->nilai[$s->id] ??= $s->nilai !== null ? (string) $s->nilai : '';
            $this->feedback[$s->id] ??= (string) ($s->feedback ?? '');
        }

        $siswaList = Student::where('status', 'aktif')
            ->when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->orderBy('nama')->limit(10)->get(['id', 'nis', 'nama']);

        return view('livewire.lms.assignment-show', [
            'submissions' => $submissions,
            'siswaList' => $siswaList,
            'bolehKumpul' => auth()->user()?->can('lms.kumpul') ?? false,
            'bolehNilai' => auth()->user()?->can('lms.nilai') ?? false,
        ])->layout('layouts.app', ['title' => $this->assignment->judul]);
    }
}
