<?php

declare(strict_types=1);

namespace App\Livewire\Kesiswaan;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail wali murid + daftar siswa tertaut (FR-302).
 *
 * Penautan siswa memakai permission wali-murid.link dan pivot
 * guardian_student. Menautkan ulang siswa yang sudah tertaut akan
 * memperbarui hubungan di pivot, bukan membuat baris ganda
 * (unique guardian_id + student_id).
 */
class GuardianShow extends Component
{
    public Guardian $guardian;

    public string $linkStudentId = '';

    public string $linkHubungan = '';

    public function mount(Guardian $guardian): void
    {
        $this->guardian = $guardian;
        $this->linkHubungan = $guardian->hubungan;
    }

    public function linkStudent(): void
    {
        $this->authorize('wali-murid.link');

        $this->validate([
            'linkStudentId' => ['required', 'integer'],
            'linkHubungan' => ['required', Rule::in(['ayah', 'ibu', 'wali'])],
        ]);

        // SchoolScope menjaga siswa tetap satu tenant dengan wali.
        $student = Student::findOrFail((int) $this->linkStudentId);

        $this->guardian->students()->syncWithoutDetaching([
            $student->id => ['hubungan' => $this->linkHubungan],
        ]);

        $this->linkStudentId = '';

        $label = match ($this->linkHubungan) {
            'ayah' => 'Ayah',
            'ibu' => 'Ibu',
            default => 'Wali',
        };

        session()->flash('sukses', "{$student->nama} ditautkan sebagai {$label}.");
    }

    public function unlinkStudent(int $studentId): void
    {
        $this->authorize('wali-murid.link');

        // SchoolScope menjaga siswa tetap satu tenant dengan wali.
        $student = Student::findOrFail($studentId);

        $this->guardian->students()->detach($student->id);

        session()->flash('sukses', "Tautan {$student->nama} dilepas.");
    }

    public function render(): View
    {
        return view('livewire.kesiswaan.guardian-show', [
            'students' => $this->guardian->students()->orderBy('nama')->get(),
            'unlinkedStudents' => Student::query()
                ->whereDoesntHave('guardians', fn ($q) => $q->where('guardians.id', $this->guardian->id))
                ->orderBy('nama')
                ->get(['id', 'nama', 'nis']),
        ])->layout('layouts.app', ['title' => 'Detail Wali']);
    }
}
