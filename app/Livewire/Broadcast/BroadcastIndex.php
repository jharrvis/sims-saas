<?php

declare(strict_types=1);

namespace App\Livewire\Broadcast;

use App\Models\Broadcast;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar broadcast.
 */
class BroadcastIndex extends Component
{
    use WithPagination;

    public string $status = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $items = Broadcast::with('pembuat:id,name')
            ->withCount('logs')
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.broadcast.broadcast-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'Broadcast']);
    }
}
