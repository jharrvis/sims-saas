<?php

declare(strict_types=1);

namespace App\Livewire\Ppdb;

use App\Models\PpdbApplicant;
use App\Models\PpdbPeriod;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar pendaftar per periode dengan filter status.
 */
class PpdbApplicantIndex extends Component
{
    use WithPagination;

    public int $periodId;

    public string $status = '';

    public string $search = '';

    public function mount(int $period): void
    {
        $this->periodId = $period;
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $period = PpdbPeriod::findOrFail($this->periodId);

        $applicants = PpdbApplicant::where('ppdb_period_id', $this->periodId)
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', fn ($q) => $q->where(function ($w): void {
                $w->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('no_pendaftaran', 'like', '%'.$this->search.'%');
            }))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.ppdb.ppdb-applicant-index', [
            'period' => $period,
            'applicants' => $applicants,
        ])->layout('layouts.app', ['title' => 'Pendaftar — '.$period->nama]);
    }
}
