<?php

declare(strict_types=1);

namespace App\Livewire\Lms;

use App\Models\Assignment;
use App\Models\Rombel;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form buat/ubah tugas.
 */
class AssignmentForm extends Component
{
    public ?Assignment $assignment = null;

    public ?int $subject_id = null;

    public ?int $rombel_id = null;

    public string $judul = '';

    public string $deskripsi = '';

    public string $deadline = '';

    public function mount(?Assignment $assignment = null): void
    {
        $this->authorize($assignment?->exists ? 'lms.tugas.update' : 'lms.tugas.create');

        if ($assignment?->exists) {
            $this->assignment = $assignment;
            $this->subject_id = $assignment->subject_id;
            $this->rombel_id = $assignment->rombel_id;
            $this->judul = $assignment->judul;
            $this->deskripsi = $assignment->deskripsi ?? '';
            /** @var Carbon $deadline */
            $deadline = $assignment->deadline;
            $this->deadline = $deadline->format('Y-m-d\TH:i');
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'rombel_id' => ['required', 'integer', 'exists:rombels,id'],
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:10000'],
            'deadline' => ['required', 'date'],
        ]);

        $data['dibuat_oleh'] = auth()->id();

        if ($this->assignment?->exists) {
            $this->assignment->update($data);
            session()->flash('ok', 'Tugas diperbarui.');
        } else {
            Assignment::create($data);
            session()->flash('ok', 'Tugas dibuat.');
        }

        $this->redirectRoute('lms.index');
    }

    public function render(): View
    {
        return view('livewire.lms.assignment-form', [
            'subjects' => Subject::orderBy('nama')->get(['id', 'nama']),
            'rombels' => Rombel::orderBy('nama')->get(['id', 'nama']),
            'isEdit' => (bool) $this->assignment?->exists,
        ])->layout('layouts.app', ['title' => $this->assignment?->exists ? 'Ubah Tugas' : 'Buat Tugas']);
    }
}
