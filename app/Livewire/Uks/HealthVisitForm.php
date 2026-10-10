<?php

declare(strict_types=1);

namespace App\Livewire\Uks;

use App\Models\HealthVisit;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form catat kunjungan UKS.
 */
class HealthVisitForm extends Component
{
    public ?HealthVisit $visit = null;

    public ?int $student_id = null;

    public string $searchSiswa = '';

    public string $tgl = '';

    public string $keluhan = '';

    public string $diagnosa = '';

    public string $tindakan = '';

    public string $suhu = '';

    public string $tekanan_darah = '';

    public string $rujukan = 'tidak';

    public function mount(?HealthVisit $visit = null): void
    {
        $this->authorize($visit?->exists ? 'uks.update' : 'uks.create');

        if ($visit?->exists) {
            $this->visit = $visit;
            $this->student_id = $visit->student_id;
            /** @var Carbon $tgl */
            $tgl = $visit->tgl;
            $this->tgl = $tgl->format('Y-m-d\TH:i');
            $this->keluhan = $visit->keluhan;
            $this->diagnosa = $visit->diagnosa ?? '';
            $this->tindakan = $visit->tindakan ?? '';
            $this->suhu = $visit->suhu !== null ? (string) $visit->suhu : '';
            $this->tekanan_darah = $visit->tekanan_darah ?? '';
            $this->rujukan = $visit->rujukan;
        } else {
            $this->tgl = now()->format('Y-m-d\TH:i');
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'tgl' => ['required', 'date'],
            'keluhan' => ['required', 'string', 'max:2000'],
            'diagnosa' => ['nullable', 'string', 'max:200'],
            'tindakan' => ['nullable', 'string', 'max:2000'],
            'suhu' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'tekanan_darah' => ['nullable', 'string', 'max:20'],
            'rujukan' => ['required', Rule::in(array_keys(HealthVisit::RUJUKAN))],
        ]);

        /** @var array<string,mixed> $payload */
        $payload = [
            'student_id' => $data['student_id'],
            'tgl' => $data['tgl'],
            'keluhan' => $data['keluhan'],
            'diagnosa' => $data['diagnosa'] ?: null,
            'tindakan' => $data['tindakan'] ?: null,
            'suhu' => $data['suhu'] !== null && $data['suhu'] !== '' ? (float) $data['suhu'] : null,
            'tekanan_darah' => $data['tekanan_darah'] ?: null,
            'rujukan' => $data['rujukan'],
            'ditangani_oleh' => auth()->id(),
        ];

        if ($this->visit?->exists) {
            $this->visit->update($payload);
            session()->flash('ok', 'Kunjungan diperbarui.');
        } else {
            HealthVisit::create($payload);
            session()->flash('ok', 'Kunjungan dicatat.');
        }

        $this->redirectRoute('uks.index');
    }

    public function render(): View
    {
        $siswaList = Student::where('status', 'aktif')
            ->when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->orderBy('nama')->limit(10)->get(['id', 'nis', 'nama']);

        return view('livewire.uks.health-visit-form', [
            'siswaList' => $siswaList,
            'isEdit' => (bool) $this->visit?->exists,
        ])->layout('layouts.app', ['title' => $this->visit?->exists ? 'Ubah Kunjungan' : 'Catat Kunjungan']);
    }
}
