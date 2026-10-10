<?php

declare(strict_types=1);

namespace App\Livewire\Survei;

use App\Models\Survey;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Hasil agregat survei.
 */
class SurveyResult extends Component
{
    public Survey $survey;

    public function mount(Survey $survey): void
    {
        $this->authorize('survei.hasil');
        $this->survey = $survey;
    }

    public function render(): View
    {
        $questions = $this->survey->questions()->withCount('answers')->get();
        $agregat = $questions->mapWithKeys(fn ($q) => [$q->id => $q->agregat()]);

        return view('livewire.survei.survey-result', [
            'questions' => $questions,
            'agregat' => $agregat,
            'totalResponden' => $this->survey->responses()->count(),
        ])->layout('layouts.app', ['title' => 'Hasil: '.$this->survey->judul]);
    }
}
