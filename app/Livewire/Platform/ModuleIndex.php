<?php

declare(strict_types=1);

namespace App\Livewire\Platform;

use App\Models\Module;
use App\Models\TenantModule;
use App\Scopes\SchoolScope;
use Illuminate\View\View;
use Livewire\Component;

class ModuleIndex extends Component
{
    public function render(): View
    {
        $modules = Module::orderBy('kategori')->orderBy('nama')->get();

        $counts = TenantModule::withoutGlobalScope(SchoolScope::class)
            ->where('aktif', true)
            ->selectRaw('module_id, count(*) as jumlah')
            ->groupBy('module_id')
            ->pluck('jumlah', 'module_id');

        return view('livewire.platform.module-index', [
            'modules' => $modules,
            'counts' => $counts,
        ])->layout('layouts.platform', ['title' => 'Katalog Modul']);
    }
}
