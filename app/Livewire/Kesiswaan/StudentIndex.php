<?php

declare(strict_types=1);

namespace App\Livewire\Kesiswaan;

use App\Models\Student;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar siswa dengan pencarian + filter status (FR-104).
 * Akses dibatasi permission siswa.view via route middleware.
 */
class StudentIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $students = Student::query()
            ->when($this->search !== '', fn ($q) => $q->where(function ($w): void {
                $w->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('nis', 'like', '%'.$this->search.'%')
                    ->orWhere('nisn', 'like', '%'.$this->search.'%');
            }))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->orderBy('nama')
            ->paginate(15);

        return view('livewire.kesiswaan.student-index', [
            'students' => $students,
        ])->layout('layouts.app', ['title' => 'Data Siswa']);
    }
}
