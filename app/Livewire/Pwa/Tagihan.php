<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Pwa\Services\PwaContext;
use App\Models\Invoice;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Tagihan SPP siswa aktif (read-only di PWA v1).
 */
class Tagihan extends Component
{
    public function render(PwaContext $pwa): View
    {
        $student = $pwa->activeStudent();

        $items = $student
            ? Invoice::with('feeType:id,nama')
                ->where('student_id', $student->id)
                ->orderByRaw("CASE status WHEN 'overdue' THEN 1 WHEN 'unpaid' THEN 2 WHEN 'partial' THEN 3 WHEN 'paid' THEN 4 ELSE 5 END")
                ->orderByDesc('jatuh_tempo')
                ->get()
            : collect();

        $totalAktif = $items->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum(fn ($i) => (float) $i->sisa());

        $totalDibayar = $items->sum(fn ($invoice): float => (float) $invoice->dibayar);
        $jumlahAktif = $items->whereIn('status', ['unpaid', 'partial', 'overdue'])->count();
        $jatuhTempoTerdekat = $items
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->filter(fn ($invoice): bool => $invoice->jatuh_tempo !== null)
            ->sortBy('jatuh_tempo')
            ->first()?->jatuh_tempo;

        return view('livewire.pwa.tagihan', [
            'items' => $items,
            'totalAktif' => $totalAktif,
            'totalDibayar' => $totalDibayar,
            'jumlahAktif' => $jumlahAktif,
            'jatuhTempoTerdekat' => $jatuhTempoTerdekat,
        ])->layout('layouts.pwa', [
            'title' => 'Tagihan',
            'schoolName' => $student?->school?->nama,
            'showChildSwitcher' => $pwa->role() === 'orang-tua',
            'children' => $pwa->children(),
            'activeStudentId' => $student?->id,
        ]);
    }
}
