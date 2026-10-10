<?php

declare(strict_types=1);

namespace App\Livewire\Bk;

use App\Models\CounselingSession;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar sesi konseling.
 */
class CounselingIndex extends Component
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

    public function tandaiSelesai(CounselingSession $session): void
    {
        $this->authorize('bk.update');

        $session->update(['status' => 'selesai']);
        session()->flash('ok', 'Sesi ditandai selesai.');
    }

    public function destroy(CounselingSession $session): void
    {
        $this->authorize('bk.delete');

        $session->delete();
        session()->flash('ok', 'Sesi konseling dihapus.');
    }

    public function render(): View
    {
        $items = CounselingSession::with(['student:id,nis,nama', 'counselor:id,name'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', fn ($q) => $q->whereHas('student', fn ($w) => $w->where('nama', 'like', '%'.$this->search.'%')))
            ->orderBy('tanggal')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.bk.counseling-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'BK & Konseling']);
    }
}
