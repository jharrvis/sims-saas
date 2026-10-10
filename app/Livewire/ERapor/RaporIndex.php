<?php

declare(strict_types=1);

namespace App\Livewire\ERapor;

use App\Models\Rombel;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Daftar rapor per rombel + semester.
 */
class RaporIndex extends Component
{
    public ?int $rombelId = null;

    public ?int $semesterId = null;

    public function mount(): void
    {
        $this->authorize('erapor.view');
        $this->semesterId = Semester::where('aktif', true)->value('id');
    }

    public function render(): View
    {
        $students = collect();
        if ($this->rombelId && $this->semesterId) {
            $students = Student::where('status', 'aktif')
                ->whereHas('rombels', fn ($q) => $q->where('rombels.id', $this->rombelId)->where('class_student.status', 'aktif'))
                ->orderBy('nama')
                ->get(['id', 'nis', 'nama']);
        }

        return view('livewire.erapor.rapor-index', [
            'rombels' => Rombel::orderBy('nama')->get(['id', 'nama']),
            'semesters' => Semester::orderByDesc('id')->limit(10)->get(['id', 'nama']),
            'students' => $students,
        ])->layout('layouts.app', ['title' => 'E-Rapor']);
    }
}
