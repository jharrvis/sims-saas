<?php

declare(strict_types=1);

namespace App\Livewire\Pengaturan;

use App\Models\Module;
use App\Models\TenantModule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Kelola modul add-on per sekolah (feature flag).
 */
class ModuleIndex extends Component
{
    public function toggle(int $moduleId): void
    {
        $this->authorize('sekolah.update');

        $module = Module::findOrFail($moduleId);

        /** @var TenantModule $tm */
        $tm = TenantModule::firstOrCreate(
            ['module_id' => $module->id],
            ['aktif' => false],
        );

        $tm->update([
            'aktif' => ! $tm->aktif,
            'diaktifkan_pada' => ! $tm->aktif ? now() : $tm->diaktifkan_pada,
            'dinonaktifkan_pada' => $tm->aktif ? now() : null,
        ]);

        $status = $tm->aktif ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('ok', "Modul {$module->nama} {$status}.");
    }

    public function render(): View
    {
        $modules = Module::with('tenantModule')->orderBy('nama')->get();

        return view('livewire.pengaturan.module-index', ['modules' => $modules])
            ->layout('layouts.app', ['title' => 'Modul Add-on']);
    }
}
