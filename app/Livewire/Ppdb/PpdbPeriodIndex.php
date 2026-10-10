<?php

declare(strict_types=1);

namespace App\Livewire\Ppdb;

use App\Models\PpdbPeriod;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar periode PPDB (modul add-on — di belakang middleware module:ppdb).
 */
class PpdbPeriodIndex extends Component
{
    use WithPagination;

    public function render(): View
    {
        $periods = PpdbPeriod::with('academicYear:id,nama')
            ->withCount('applicants')
            ->orderByDesc('tgl_buka')
            ->paginate(15);

        return view('livewire.ppdb.ppdb-period-index', [
            'periods' => $periods,
        ])->layout('layouts.app', ['title' => 'PPDB']);
    }
}
