<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Pwa\Services\PwaContext;
use App\Models\Announcement;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail pengumuman.
 */
class PengumumanShow extends Component
{
    public Announcement $item;

    public function mount(int|string $announcement, PwaContext $pwa): void
    {
        $student = $pwa->activeStudent();
        $rombel = $student?->rombels()
            ->wherePivot('status', 'aktif')
            ->latest('class_student.id')
            ->first();

        $visibleAnnouncement = Announcement::tayang()
            ->visibleToPwa($pwa->role(), $rombel ? [$rombel->id] : [])
            ->findOrFail($announcement);

        $this->item = $visibleAnnouncement;
    }

    public function render(PwaContext $pwa): View
    {
        $student = $pwa->activeStudent();

        return view('livewire.pwa.pengumuman-show', ['item' => $this->item])
            ->layout('layouts.pwa', [
                'title' => 'Pengumuman',
                'schoolName' => $student?->school?->nama,
                'showChildSwitcher' => $pwa->role() === 'orang-tua',
                'children' => $pwa->children(),
                'activeStudentId' => $student?->id,
            ]);
    }
}
