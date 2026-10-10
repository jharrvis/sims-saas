<?php

declare(strict_types=1);

namespace App\Livewire\Ekskul;

use App\Models\Extracurricular;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Daftar ekstrakurikuler + jumlah anggota.
 */
class EkskulIndex extends Component
{
    public function render(): View
    {
        $items = Extracurricular::with('pembina:id,nama')
            ->withCount(['members' => fn ($q) => $q->where('extracurricular_members.status', 'aktif')])
            ->orderBy('nama')
            ->get();

        return view('livewire.ekskul.ekskul-index', ['items' => $items])
            ->layout('layouts.app', ['title' => 'Ekstrakurikuler']);
    }
}
