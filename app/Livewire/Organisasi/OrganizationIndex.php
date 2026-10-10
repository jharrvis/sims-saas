<?php

declare(strict_types=1);

namespace App\Livewire\Organisasi;

use App\Models\Organization;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Daftar organisasi sekolah.
 */
class OrganizationIndex extends Component
{
    public function render(): View
    {
        $items = Organization::with('pembina:id,nama')
            ->withCount([
                'members' => fn ($q) => $q->where('organization_members.status', 'aktif'),
                'activities',
            ])
            ->orderBy('nama')
            ->get();

        return view('livewire.organisasi.organization-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'Organisasi']);
    }
}
