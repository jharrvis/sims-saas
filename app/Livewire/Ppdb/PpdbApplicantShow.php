<?php

declare(strict_types=1);

namespace App\Livewire\Ppdb;

use App\Domains\Ppdb\Services\PpdbService;
use App\Models\PpdbApplicant;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail pendaftar: verifikasi, seleksi, daftar ulang.
 */
class PpdbApplicantShow extends Component
{
    public PpdbApplicant $applicant;

    public ?float $nilai_tes = null;

    public ?float $nilai_wawancara = null;

    public string $catatan = '';

    public function mount(PpdbApplicant $applicant): void
    {
        $this->applicant = $applicant->load(['period:id,nama', 'documents', 'selection']);
        $this->catatan = $applicant->catatan ?? '';
        $this->nilai_tes = $applicant->selection?->nilai_tes !== null ? (float) $applicant->selection->nilai_tes : null;
        $this->nilai_wawancara = $applicant->selection?->nilai_wawancara !== null ? (float) $applicant->selection->nilai_wawancara : null;
    }

    public function submit(PpdbService $service): void
    {
        $this->authorize('ppdb.pendaftar.update');
        $service->setStatus($this->applicant, 'submitted');
        $this->refreshApplicant();
    }

    public function verify(PpdbService $service): void
    {
        $this->authorize('ppdb.pendaftar.verify');
        $service->setStatus($this->applicant, 'verified', $this->catatan ?: null);
        $this->refreshApplicant();
    }

    public function saveSelection(PpdbService $service): void
    {
        $this->authorize('ppdb.seleksi.input');

        $this->validate([
            'nilai_tes' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_wawancara' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $nilaiAkhir = null;
        if ($this->nilai_tes !== null && $this->nilai_wawancara !== null) {
            $nilaiAkhir = round(($this->nilai_tes + $this->nilai_wawancara) / 2, 2);
        }

        $this->applicant->selection()->updateOrCreate(
            ['ppdb_applicant_id' => $this->applicant->id],
            [
                'nilai_tes' => $this->nilai_tes,
                'nilai_wawancara' => $this->nilai_wawancara,
                'nilai_akhir' => $nilaiAkhir,
                'dinilai_oleh' => auth()->id(),
            ],
        );

        $this->refreshApplicant();
        session()->flash('saved', 'Nilai seleksi tersimpan.');
    }

    public function accept(PpdbService $service): void
    {
        $this->authorize('ppdb.pendaftar.approve');

        $this->applicant->selection()->updateOrCreate(
            ['ppdb_applicant_id' => $this->applicant->id],
            ['keputusan' => 'lulus'],
        );

        $service->setStatus($this->applicant, 'accepted', $this->catatan ?: null);
        $this->refreshApplicant();
    }

    public function reject(PpdbService $service): void
    {
        $this->authorize('ppdb.pendaftar.approve');

        $this->applicant->selection()->updateOrCreate(
            ['ppdb_applicant_id' => $this->applicant->id],
            ['keputusan' => 'tidak_lulus'],
        );

        $service->setStatus($this->applicant, 'rejected', $this->catatan ?: null);
        $this->refreshApplicant();
    }

    public function enroll(PpdbService $service): void
    {
        $this->authorize('ppdb.pendaftar.enroll');

        $student = $service->enroll($this->applicant);
        $this->refreshApplicant();

        session()->flash('enrolled', "Daftar ulang berhasil — {$student->nama} tercatat sebagai siswa (NIS {$student->nis}).");
    }

    private function refreshApplicant(): void
    {
        $this->applicant = $this->applicant->fresh(['period:id,nama', 'documents', 'selection']);
    }

    public function render(): View
    {
        return view('livewire.ppdb.ppdb-applicant-show')
            ->layout('layouts.app', ['title' => 'Pendaftar — '.$this->applicant->nama]);
    }
}
