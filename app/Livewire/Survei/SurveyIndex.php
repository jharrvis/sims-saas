<?php

declare(strict_types=1);

namespace App\Livewire\Survei;

use App\Models\Survey;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar survei.
 */
class SurveyIndex extends Component
{
    use WithPagination;

    public string $status = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $items = Survey::withCount(['questions', 'responses'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.survei.survey-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'Survei & Evaluasi']);
    }
}
