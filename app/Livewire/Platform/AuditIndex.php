<?php

declare(strict_types=1);

namespace App\Livewire\Platform;

use App\Models\AuditLog;
use App\Models\School;
use App\Models\User;
use App\Scopes\SchoolScope;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class AuditIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    public function render(): View
    {
        $logs = AuditLog::withoutGlobalScope(SchoolScope::class)
            ->when($this->search !== '', fn ($q) => $q->where('aksi', 'like', '%'.$this->search.'%'))
            ->orderByDesc('id')
            ->paginate(20);

        $names = School::withoutGlobalScope(SchoolScope::class)
            ->whereIn('id', $logs->pluck('school_id')->filter()->unique())
            ->pluck('nama', 'id');

        $users = User::query()
            ->whereIn('id', $logs->pluck('user_id')->filter()->unique())
            ->pluck('name', 'id');

        return view('livewire.platform.audit-index', [
            'logs' => $logs,
            'names' => $names,
            'users' => $users,
        ])->layout('layouts.platform', ['title' => 'Audit Log Platform']);
    }
}
