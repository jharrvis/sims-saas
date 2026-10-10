<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\Rombel;
use App\Models\Student;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail rombel: daftar siswa + penempatan siswa (FR-102).
 */
class RombelShow extends Component
{
    public Rombel $rombel;

    public string $searchSiswa = '';

    public function mount(Rombel $rombel): void
    {
        $this->rombel = $rombel->load(['waliKelas:id,nama', 'academicYear:id,nama']);
    }

    /**
     * Tempatkan siswa ke rombel ini (FR-102: satu siswa satu rombel aktif per tahun ajaran).
     */
    public function assignStudent(int $studentId): void
    {
        $this->authorize('kelas.update');

        $student = Student::findOrFail($studentId);

        // Pindahkan: nonaktifkan penempatan lama di tahun ajaran yang sama
        \DB::table('class_student')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $this->rombel->academic_year_id)
            ->update(['status' => 'pindah']);

        $this->rombel->students()->syncWithoutDetaching([
            $student->id => [
                'academic_year_id' => $this->rombel->academic_year_id,
                'status' => 'aktif',
            ],
        ]);

        $this->rombel->load('students');
    }

    public function removeStudent(int $studentId): void
    {
        $this->authorize('kelas.update');

        \DB::table('class_student')
            ->where('rombel_id', $this->rombel->id)
            ->where('student_id', $studentId)
            ->where('academic_year_id', $this->rombel->academic_year_id)
            ->update(['status' => 'pindah']);
    }

    public function render(): View
    {
        $siswa = $this->rombel->students()
            ->wherePivot('academic_year_id', $this->rombel->academic_year_id)
            ->wherePivot('status', 'aktif')
            ->orderBy('nama')
            ->get();

        $calon = Student::where('status', 'aktif')
            ->where('nama', 'like', '%'.$this->searchSiswa.'%')
            ->whereNotIn('id', $siswa->pluck('id'))
            ->orderBy('nama')
            ->limit(10)
            ->get();

        return view('livewire.akademik.rombel-show', [
            'siswa' => $siswa,
            'calon' => $calon,
        ])->layout('layouts.app', ['title' => 'Kelas '.$this->rombel->nama]);
    }
}
