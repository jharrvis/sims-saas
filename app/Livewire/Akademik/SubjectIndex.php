<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\Subject;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar mata pelajaran (FR-402).
 */
class SubjectIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $subjects = Subject::query()
            ->when($this->search !== '', fn ($q) => $q->where(function ($w): void {
                $w->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('kode', 'like', '%'.$this->search.'%');
            }))
            ->orderBy('nama')
            ->paginate(15);

        return view('livewire.akademik.subject-index', [
            'subjects' => $subjects,
        ])->layout('layouts.app', ['title' => 'Mata Pelajaran']);
    }
}
