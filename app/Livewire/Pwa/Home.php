<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Pwa\Services\PwaContext;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\Schedule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Beranda PWA — P-02 (siswa) / P-04 (orang tua).
 */
class Home extends Component
{
    public function render(PwaContext $pwa): View
    {
        $student = $pwa->activeStudent();
        $role = $pwa->role();

        $rombel = $student?->rombels()->wherePivot('status', 'aktif')->latest('class_student.id')->first();

        $hariIni = strtolower(now()->locale('id')->dayName);
        $jadwalHariIni = $rombel
            ? Schedule::with(['subject:id,nama,kode', 'employee:id,nama'])
                ->where('rombel_id', $rombel->id)
                ->where('hari', $hariIni)
                ->orderBy('jam_ke')
                ->get()
            : collect();

        $pengumuman = Announcement::tayang()->orderByDesc('penting')->orderByDesc('tayang_dari')->limit(3)->get();

        $awalBulan = now()->startOfMonth()->toDateString();
        $rekapAbsensi = $student
            ? Attendance::where('student_id', $student->id)
                ->whereDate('tanggal', '>=', $awalBulan)
                ->selectRaw('status, COUNT(*) as jml')
                ->groupBy('status')
                ->pluck('jml', 'status')
            : collect();

        $tagihanAktif = $student
            ? Invoice::where('student_id', $student->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->count()
            : 0;

        $layoutData = [
            'title' => $role === 'orang-tua' ? 'Beranda Orang Tua' : 'Beranda',
            'schoolName' => $student?->school?->nama,
            'showChildSwitcher' => $role === 'orang-tua',
            'children' => $pwa->children(),
            'activeStudentId' => $student?->id,
        ];

        return view('livewire.pwa.home', [
            'role' => $role,
            'student' => $student,
            'children' => $pwa->children(),
            'rombel' => $rombel,
            'jadwalHariIni' => $jadwalHariIni,
            'hariIni' => ucfirst($hariIni),
            'pengumuman' => $pengumuman,
            'rekapAbsensi' => $rekapAbsensi,
            'tagihanAktif' => $tagihanAktif,
        ])->layout('layouts.pwa', $layoutData);
    }
}
