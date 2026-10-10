<?php

declare(strict_types=1);

namespace App\Livewire\Transportasi;

use App\Models\TransportRoute;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Daftar rute antar-jemput.
 */
class RouteIndex extends Component
{
    public function render(): View
    {
        $items = TransportRoute::withCount([
            'members' => fn ($q) => $q->where('transport_members.status', 'aktif'),
            'stops',
        ])->orderBy('nama')->get();

        return view('livewire.transportasi.route-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'Antar-Jemput']);
    }
}
