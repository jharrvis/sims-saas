<?php

declare(strict_types=1);

namespace App\Livewire\Platform;

use App\Domains\Platform\Services\PlatformService;
use App\Models\AuditLog;
use App\Models\BillingInvoice;
use App\Models\Module;
use App\Models\Plan;
use App\Models\School;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\TenantModule;
use App\Models\TenantQuotaOverride;
use App\Models\TenantUsage;
use App\Scopes\SchoolScope;
use Illuminate\View\View;
use Livewire\Component;

class TenantShow extends Component
{
    public School $school;

    public string $plan_id = '';

    public string $periode = 'bulanan';

    public bool $trial = false;

    public string $ov_metrik = '';

    public string $ov_batas = '';

    public string $ov_alasan = '';

    public function mount(int $schoolId): void
    {
        // Tanpa tenant scope (operator platform) — binding implisit akan 404.
        $this->school = School::withoutGlobalScope(SchoolScope::class)->findOrFail($schoolId);
    }

    public function buatLangganan(PlatformService $platform): void
    {
        $this->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'periode' => ['required', 'in:bulanan,tahunan'],
        ]);

        $plan = Plan::findOrFail($this->plan_id);
        $platform->createSubscription($this->school, $plan, $this->periode, auth()->user(), $this->trial);

        $this->reset(['plan_id', 'trial']);
        session()->flash('ok', 'Langganan dibuat.');
    }

    public function toggleModule(int $moduleId, PlatformService $platform): void
    {
        $tm = TenantModule::withoutGlobalScope(SchoolScope::class)
            ->firstOrCreate(
                ['school_id' => $this->school->id, 'module_id' => $moduleId],
                ['aktif' => false]
            );

        $tm->update([
            'aktif' => ! $tm->aktif,
            'diaktifkan_pada' => ! $tm->aktif ? now() : $tm->diaktifkan_pada,
            'dinonaktifkan_pada' => $tm->aktif ? now() : null,
        ]);

        AuditLog::create([
            'school_id' => $this->school->id,
            'user_id' => auth()->id(),
            'aksi' => 'platform.module.toggle',
            'model_type' => TenantModule::class,
            'model_id' => (string) $tm->id,
            'after' => ['module_id' => $moduleId, 'aktif' => $tm->aktif],
            'ip_address' => request()->ip(),
        ]);
    }

    public function tambahOverride(): void
    {
        $this->validate([
            'ov_metrik' => ['required', 'string', 'max:50'],
            'ov_alasan' => ['required', 'string', 'max:500'],
            'ov_batas' => ['nullable', 'integer', 'min:0'],
        ]);

        TenantQuotaOverride::withoutGlobalScope(SchoolScope::class)->updateOrCreate(
            ['school_id' => $this->school->id, 'metrik' => $this->ov_metrik],
            [
                'batas_custom' => $this->ov_batas !== '' ? (int) $this->ov_batas : null,
                'alasan' => $this->ov_alasan,
                'dibuat_oleh' => auth()->id(),
            ]
        );

        $this->reset(['ov_metrik', 'ov_batas', 'ov_alasan']);
        session()->flash('ok', 'Quota override disimpan.');
    }

    public function hapusOverride(int $id): void
    {
        TenantQuotaOverride::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', $this->school->id)
            ->whereKey($id)
            ->delete();
    }

    public function render(PlatformService $platform): View
    {
        $sid = $this->school->id;

        $subscription = Subscription::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', $sid)->with('plan')->latest()->first();

        $modules = Module::orderBy('kategori')->orderBy('nama')->get();
        $tenantModules = TenantModule::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', $sid)->get()->keyBy('module_id');

        return view('livewire.platform.tenant-show', [
            'subscription' => $subscription,
            'plans' => Plan::where('aktif', true)->orderBy('harga_bulanan')->get(),
            'modules' => $modules,
            'tenantModules' => $tenantModules,
            'usages' => TenantUsage::withoutGlobalScope(SchoolScope::class)
                ->where('school_id', $sid)->where('periode', now()->format('Y-m'))->get(),
            'overrides' => TenantQuotaOverride::withoutGlobalScope(SchoolScope::class)
                ->where('school_id', $sid)->get(),
            'invoices' => BillingInvoice::withoutGlobalScope(SchoolScope::class)
                ->where('school_id', $sid)->orderByDesc('id')->limit(10)->get(),
            'siswaCount' => Student::withoutGlobalScope(SchoolScope::class)
                ->where('school_id', $sid)->where('status', 'aktif')->count(),
        ])->layout('layouts.platform', ['title' => 'Detail Tenant']);
    }
}
