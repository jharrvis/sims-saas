<?php

declare(strict_types=1);

namespace App\Livewire\Lms;

use App\Models\Assignment;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar tugas online.
 */
class AssignmentIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $items = Assignment::with(['subject:id,nama', 'rombel:id,nama'])
            ->withCount('submissions')
            ->when($this->search !== '', fn ($q) => $q->where('judul', 'like', '%'.$this->search.'%'))
            ->orderByDesc('deadline')
            ->paginate(15);

        return view('livewire.lms.assignment-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'Tugas Online']);
    }
}
