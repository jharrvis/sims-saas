<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Pwa\Services\PwaContext;
use App\Models\Schedule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Jadwal pelajaran seminggu siswa aktif.
 */
class Jadwal extends Component
{
    public function render(PwaContext $pwa): View
    {
        $student = $pwa->activeStudent();

        $rombel = $student?->rombels()->wherePivot('status', 'aktif')->latest('class_student.id')->first();

        $items = $rombel
            ? Schedule::with(['subject:id,nama,kode', 'employee:id,nama'])
                ->where('rombel_id', $rombel->id)
                ->orderByRaw("CASE hari WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3 WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6 ELSE 7 END")
                ->orderBy('jam_ke')
                ->get()
                ->groupBy('hari')
            : collect();

        return view('livewire.pwa.jadwal', [
            'items' => $items,
            'rombel' => $rombel,
            'haris' => Schedule::HARIS,
        ])->layout('layouts.pwa', [
            'title' => 'Jadwal',
            'schoolName' => $student?->school?->nama,
            'showChildSwitcher' => $pwa->role() === 'orang-tua',
            'children' => $pwa->children(),
            'activeStudentId' => $student?->id,
        ]);
    }
}
