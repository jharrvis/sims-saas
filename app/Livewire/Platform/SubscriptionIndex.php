<?php

declare(strict_types=1);

namespace App\Livewire\Platform;

use App\Models\School;
use App\Models\Subscription;
use App\Scopes\SchoolScope;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionIndex extends Component
{
    use WithPagination;

    public string $status = 'past_due';

    public string $search = '';

    public function render(): View
    {
        $subs = Subscription::withoutGlobalScope(SchoolScope::class)
            ->with(['plan'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($q): void {
                $ids = School::withoutGlobalScope(SchoolScope::class)
                    ->where('nama', 'like', '%'.$this->search.'%')
                    ->pluck('id');
                $q->whereIn('school_id', $ids);
            })
            ->orderByDesc('id')
            ->paginate(15);

        $names = School::withoutGlobalScope(SchoolScope::class)
            ->whereIn('id', $subs->pluck('school_id'))
            ->pluck('nama', 'id');

        return view('livewire.platform.subscription-index', [
            'subs' => $subs,
            'names' => $names,
        ])->layout('layouts.platform', ['title' => 'Langganan']);
    }
}
