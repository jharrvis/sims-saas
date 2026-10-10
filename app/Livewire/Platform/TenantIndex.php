<?php

declare(strict_types=1);

namespace App\Livewire\Platform;

use App\Domains\Platform\Services\PlatformService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class TenantIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function suspend(int $schoolId, PlatformService $platform): void
    {
        $tenant = $platform->findTenant($schoolId);
        $platform->suspend($tenant, auth()->user(), 'Suspend manual via panel platform');
        session()->flash('ok', "Tenant {$tenant->nama} di-suspend.");
    }

    public function activate(int $schoolId, PlatformService $platform): void
    {
        $tenant = $platform->findTenant($schoolId);
        $platform->activate($tenant, auth()->user());
        session()->flash('ok', "Tenant {$tenant->nama} diaktifkan.");
    }

    public function render(PlatformService $platform): View
    {
        $tenants = $platform->tenants(
            $this->status !== '' ? $this->status : null,
            $this->search !== '' ? $this->search : null,
        );

        $page = $this->paginators['page'] ?? 1;
        $perPage = 15;
        $items = $tenants->forPage($page, $perPage);

        return view('livewire.platform.tenant-index', [
            'tenants' => new LengthAwarePaginator(
                $items, $tenants->count(), $perPage, $page,
                ['path' => request()->url(), 'query' => request()->query()]
            ),
        ])->layout('layouts.platform', ['title' => 'Tenant']);
    }
}
