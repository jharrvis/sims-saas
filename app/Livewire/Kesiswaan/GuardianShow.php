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

    /** Kata kunci pencarian kandidat siswa untuk ditautkan (QA B-09). */
    public string $linkSearch = '';

    public function mount(Guardian $guardian): void
    {
        $this->guardian = $guardian;
        // Nilai awal dari hubungan UTAMA wali; hubungan per siswa yang
        // tersimpan di pivot tetap yang berlaku (QA B-08).
        $this->linkHubungan = $guardian->hubungan;
    }

    public function linkStudent(): void
    {
        $this->authorize('wali-murid.link');

        $this->validate([
            // exists dibatasi sekolah wali: id siswa sekolah lain ditolak
            // sebagai error validasi, bukan 404 (QA B-11).
            'linkStudentId' => ['required', 'integer', Rule::exists('students', 'id')
                ->where('school_id', $this->guardian->school_id)],
            'linkHubungan' => ['required', Rule::in(['ayah', 'ibu', 'wali'])],
        ]);

        // SchoolScope menjaga siswa tetap satu tenant dengan wali.
        $student = Student::findOrFail((int) $this->linkStudentId);

        // Siswa non-aktif (lulus/pindah/keluar/DO) tidak ditautkan ke wali
        // baru (QA B-11).
        if ($student->status !== 'aktif') {
            $this->addError('linkStudentId', "Siswa ini berstatus {$student->status}; hanya siswa aktif yang dapat ditautkan.");

            return;
        }

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
            // Kandidat tautan dibatasi 50 dan bisa dicari nama/NIS agar
            // halaman tetap ringan di sekolah besar (QA B-09).
            'unlinkedStudents' => Student::query()
                ->whereDoesntHave('guardians', fn ($q) => $q->where('guardians.id', $this->guardian->id))
                ->when($this->linkSearch !== '', fn ($q) => $q->where(function ($w): void {
                    $w->where('nama', 'like', '%'.$this->linkSearch.'%')
                        ->orWhere('nis', 'like', '%'.$this->linkSearch.'%');
                }))
                ->orderBy('nama')
                ->limit(50)
                ->get(['id', 'nama', 'nis']),
        ])->layout('layouts.app', ['title' => 'Detail Wali']);
    }
}
