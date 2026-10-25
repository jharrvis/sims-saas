<?php

declare(strict_types=1);

namespace App\Livewire\Kesiswaan;

use App\Models\Student;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Profil 360° siswa (FR-105): biodata + wali.
 */
class StudentShow extends Component
{
    public Student $student;

    public function mount(Student $student): void
    {
        $this->student = $student->load('guardians');
    }

    public function render(): View
    {
        // Halaman ini hanya menampilkan daftar wali secara read-only; tidak
        // ada penautan wali dari halaman ini.
        return view('livewire.kesiswaan.student-show')
            ->layout('layouts.app', ['title' => 'Profil Siswa']);
    }
}
