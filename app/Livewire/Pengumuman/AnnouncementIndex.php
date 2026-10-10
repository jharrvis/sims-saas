<?php

declare(strict_types=1);

namespace App\Livewire\Pengumuman;

use App\Domains\Pwa\Services\PushService;
use App\Models\Announcement;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar pengumuman sekolah (draf + tayang).
 */
class AnnouncementIndex extends Component
{
    use WithPagination;

    public string $status = '';

    public string $search = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function publish(Announcement $announcement, PushService $push): void
    {
        $this->authorize('pengumuman.publish');

        $announcement->update(['status' => 'tayang']);
        $push->notifyAnnouncement($announcement->fresh());
        session()->flash('ok', "Pengumuman \"{$announcement->judul}\" ditayangkan.");
    }

    public function unpublish(Announcement $announcement): void
    {
        $this->authorize('pengumuman.publish');

        $announcement->update(['status' => 'draf']);
        session()->flash('ok', "Pengumuman \"{$announcement->judul}\" ditarik menjadi draf.");
    }

    public function destroy(Announcement $announcement): void
    {
        $this->authorize('pengumuman.delete');

        $announcement->delete();
        session()->flash('ok', 'Pengumuman dihapus.');
    }

    public function render(): View
    {
        $items = Announcement::with('pembuat:id,name')
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', fn ($q) => $q->where('judul', 'like', '%'.$this->search.'%'))
            ->orderByDesc('tayang_dari')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.pengumuman.announcement-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'Pengumuman']);
    }
}
