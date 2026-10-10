<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Pwa\Services\PwaContext;
use App\Models\Announcement;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pengumuman — P-05. Hanya yang sedang tayang.
 */
class Pengumuman extends Component
{
    use WithPagination;

    public function render(PwaContext $pwa): View
    {
        $student = $pwa->activeStudent();

        $items = Announcement::tayang()
            ->orderByDesc('penting')
            ->orderByDesc('tayang_dari')
            ->paginate(15);

        return view('livewire.pwa.pengumuman', ['items' => $items])
            ->layout('layouts.pwa', [
                'title' => 'Pengumuman',
                'schoolName' => $student?->school?->nama,
                'showChildSwitcher' => $pwa->role() === 'orang-tua',
                'children' => $pwa->children(),
                'activeStudentId' => $student?->id,
            ]);
    }
}
