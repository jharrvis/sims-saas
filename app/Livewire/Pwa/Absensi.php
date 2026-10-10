<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Pwa\Services\PwaContext;
use App\Models\Attendance;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Riwayat absensi siswa aktif + rekap bulanan.
 */
class Absensi extends Component
{
    use WithPagination;

    public function render(PwaContext $pwa): View
    {
        $student = $pwa->activeStudent();

        $items = $student
            ? Attendance::where('student_id', $student->id)
                ->orderByDesc('tanggal')
                ->paginate(20)
            : collect();

        $awalBulan = now()->startOfMonth()->toDateString();
        $rekap = $student
            ? Attendance::where('student_id', $student->id)
                ->whereDate('tanggal', '>=', $awalBulan)
                ->selectRaw('status, COUNT(*) as jml')
                ->groupBy('status')
                ->pluck('jml', 'status')
            : collect();

        return view('livewire.pwa.absensi', [
            'items' => $items,
            'rekap' => $rekap,
            'labels' => Attendance::STATUS,
        ])->layout('layouts.pwa', [
            'title' => 'Absensi',
            'schoolName' => $student?->school?->nama,
            'showChildSwitcher' => $pwa->role() === 'orang-tua',
            'children' => $pwa->children(),
            'activeStudentId' => $student?->id,
        ]);
    }
}
