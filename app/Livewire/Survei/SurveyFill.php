<?php

declare(strict_types=1);

namespace App\Livewire\Survei;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Pengisian survei oleh responden.
 */
class SurveyFill extends Component
{
    public Survey $survey;

    /** @var array<int,?string> question_id => jawaban */
    public array $jawaban = [];

    public function mount(Survey $survey): void
    {
        $this->authorize('survei.isi');
        abort_if(! $survey->isDapatDiisi(), 422, 'Survei tidak sedang dibuka.');

        $this->survey = $survey;
    }

    public function submit(): void
    {
        $this->authorize('survei.isi');
        abort_if(! $this->survey->isDapatDiisi(), 422, 'Survei tidak sedang dibuka.');

        $rules = [];
        foreach ($this->survey->questions as $q) {
            $rules["jawaban.{$q->id}"] = $q->wajib ? ['required', 'string', 'max:5000'] : ['nullable', 'string', 'max:5000'];
        }
        $this->validate($rules);

        \DB::transaction(function (): void {
            $response = SurveyResponse::create([
                'survey_id' => $this->survey->id,
                'user_id' => auth()->id(),
                'tgl' => now(),
            ]);

            foreach ($this->jawaban as $questionId => $jawab) {
                if ($jawab === null || $jawab === '') {
                    continue;
                }

                $response->answers()->create([
                    'survey_question_id' => $questionId,
                    'jawaban' => $jawab,
                ]);
            }
        });

        session()->flash('ok', 'Terima kasih, jawaban Anda tersimpan.');
        $this->redirectRoute('survei.index');
    }

    public function render(): View
    {
        return view('livewire.survei.survey-fill', [
            'questions' => $this->survey->questions,
        ])->layout('layouts.app', ['title' => $this->survey->judul]);
    }
}
