<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Pwa\Services\PwaContext;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail pengumuman.
 */
class PengumumanShow extends Component
{
    public Announcement $announcement;

    public function mount(Announcement $announcement): void
    {
        $now = now()->toDateString();
        /** @var Carbon|null $tayangDari */
        $tayangDari = $announcement->tayang_dari;
        /** @var Carbon|null $tayangSampai */
        $tayangSampai = $announcement->tayang_sampai;
        $dari = $tayangDari?->toDateString();
        $sampai = $tayangSampai?->toDateString();

        $tayang = $announcement->status === 'tayang'
            && $dari !== null && $dari <= $now
            && ($sampai === null || $sampai >= $now);

        abort_unless($tayang, 404);
        $this->announcement = $announcement;
    }

    public function render(PwaContext $pwa): View
    {
        $student = $pwa->activeStudent();

        return view('livewire.pwa.pengumuman-show', ['item' => $this->announcement])
            ->layout('layouts.pwa', [
                'title' => 'Pengumuman',
                'schoolName' => $student?->school?->nama,
                'showChildSwitcher' => $pwa->role() === 'orang-tua',
                'children' => $pwa->children(),
                'activeStudentId' => $student?->id,
            ]);
    }
}
