<?php

declare(strict_types=1);

namespace App\Livewire\ERapor;

use App\Domains\ERapor\Services\ERaporService;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Pratinjau + cetak rapor satu siswa, termasuk catatan wali kelas.
 */
class RaporShow extends Component
{
    public Student $student;

    public Semester $semester;

    public string $catatan = '';

    /** @var array<string,mixed> */
    public array $rapor = [];

    public function mount(Student $student, Semester $semester, ERaporService $service): void
    {
        $this->authorize('erapor.view');

        $this->student = $student;
        $this->semester = $semester;
        $this->refreshRapor($service);
    }

    public function simpanCatatan(ERaporService $service): void
    {
        $this->authorize('erapor.catatan');

        $this->validate(['catatan' => ['nullable', 'string', 'max:2000']]);

        $service->simpanCatatan($this->student->id, $this->semester->id, $this->catatan ?: null);
        $this->refreshRapor($service);

        session()->flash('ok', 'Catatan wali kelas disimpan.');
    }

    private function refreshRapor(ERaporService $service): void
    {
        $this->rapor = $service->raporSiswa($this->student->id, $this->semester->id);
        $this->catatan = $this->rapor['catatan_wali'] ?? '';
    }

    public function render(): View
    {
        return view('livewire.erapor.rapor-show')
            ->layout('layouts.app', ['title' => 'Rapor — '.$this->student->nama]);
    }
}
