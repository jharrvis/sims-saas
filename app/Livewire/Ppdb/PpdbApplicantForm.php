<?php

declare(strict_types=1);

namespace App\Livewire\Ppdb;

use App\Domains\Ppdb\Services\PpdbService;
use App\Models\PpdbApplicant;
use App\Models\PpdbPeriod;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Formulir pendaftaran PPDB (internal/petugas).
 */
class PpdbApplicantForm extends Component
{
    public int $periodId;

    public string $nama = '';

    public string $nisn = '';

    public string $jk = 'L';

    public string $tgl_lahir = '';

    public string $asal_sekolah = '';

    public string $nama_ortu = '';

    public string $no_hp_ortu = '';

    public function mount(int $period): void
    {
        $this->periodId = $period;
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'jk' => ['required', Rule::in(['L', 'P'])],
            'tgl_lahir' => ['nullable', 'date'],
            'asal_sekolah' => ['nullable', 'string', 'max:150'],
            'nama_ortu' => ['nullable', 'string', 'max:150'],
            'no_hp_ortu' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function save(PpdbService $service): void
    {
        $this->authorize('ppdb.pendaftar.create');

        $data = $this->validate();

        $applicant = PpdbApplicant::create([
            ...$data,
            'ppdb_period_id' => $this->periodId,
            'no_pendaftaran' => $service->generateNoPendaftaran(),
            'status' => 'draft',
        ]);

        $this->redirect(route('ppdb.pendaftar.show', $applicant), navigate: true);
    }

    public function render(): View
    {
        $period = PpdbPeriod::findOrFail($this->periodId);

        return view('livewire.ppdb.ppdb-applicant-form', [
            'period' => $period,
        ])->layout('layouts.app', ['title' => 'Formulir PPDB — '.$period->nama]);
    }
}
