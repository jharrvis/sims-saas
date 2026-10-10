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
                ->orderByRaw("FIELD(status, 'overdue','unpaid','partial','paid')")
                ->orderByDesc('jatuh_tempo')
                ->get()
            : collect();

        $totalAktif = $items->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum(fn ($i) => (float) $i->sisa());

        return view('livewire.pwa.tagihan', [
            'items' => $items,
            'totalAktif' => $totalAktif,
        ])->layout('layouts.pwa', [
            'title' => 'Tagihan',
            'schoolName' => $student?->school?->nama,
            'showChildSwitcher' => $pwa->role() === 'orang-tua',
            'children' => $pwa->children(),
            'activeStudentId' => $student?->id,
        ]);
    }
}
