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
use App\Models\User;
use App\Scopes\SchoolScope;
use App\Support\RoleCatalog;
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
        $this->ensurePlatformPermission('platform.tenant.view');

        // Tanpa tenant scope (operator platform) — binding implisit akan 404.
        $this->school = School::withoutGlobalScope(SchoolScope::class)->findOrFail($schoolId);
    }

    public function buatLangganan(PlatformService $platform): void
    {
        $operator = $this->ensurePlatformPermission('platform.tenant.create');

        $this->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'periode' => ['required', 'in:bulanan,tahunan'],
        ]);

        $plan = Plan::findOrFail($this->plan_id);
        $platform->createSubscription($this->school, $plan, $this->periode, $operator, $this->trial);

        $this->reset(['plan_id', 'trial']);
        session()->flash('ok', 'Langganan dibuat.');
    }

    public function toggleModule(int $moduleId, PlatformService $platform): void
    {
        $operator = $this->ensurePlatformPermission('platform.module.toggle');
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
            'user_id' => $operator->id,
            'aksi' => 'platform.module.toggle',
            'model_type' => TenantModule::class,
            'model_id' => (string) $tm->id,
            'after' => ['module_id' => $moduleId, 'aktif' => $tm->aktif],
            'ip_address' => request()->ip(),
        ]);
    }

    public function tambahOverride(): void
    {
        $operator = $this->ensurePlatformPermission('platform.tenant.create');

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
                'dibuat_oleh' => $operator->id,
            ]
        );

        $this->reset(['ov_metrik', 'ov_batas', 'ov_alasan']);
        session()->flash('ok', 'Quota override disimpan.');
    }

    public function hapusOverride(int $id): void
    {
        $this->ensurePlatformPermission('platform.tenant.create');

        TenantQuotaOverride::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', $this->school->id)
            ->whereKey($id)
            ->delete();
    }

    /**
     * Impersonate: masuk sebagai admin sekolah untuk keperluan dukungan.
     * Dicatat di audit; target hanya akun non-platform; keluar via stop route.
     *
     * Operator TIDAK perlu menjadi member sekolah (tidak perlu peran ganda):
     * cukup izin platform.tenant.impersonate. Target HARUS pemegang peran
     * admin (superadmin, lalu admin-sekolah); bila sekolah tak punya member
     * berperan admin — misalnya hanya staf guru-mapel — hasilnya 404
     * "Sekolah ini belum memiliki admin", bukan login sebagai staf biasa.
     */
    public function impersonate(): void
    {
        $operator = $this->ensurePlatformPermission('platform.tenant.impersonate');

        $target = null;
        foreach (RoleCatalog::ADMIN_ROLES as $role) {
            $target = $this->cariTargetImpersonate($operator, $role);
            if ($target) {
                break;
            }
        }

        abort_if(! $target, 404, 'Sekolah ini belum memiliki admin.');

        session([
            'impersonator_id' => $operator->id,
            'impersonate_school_id' => $this->school->id,
            'active_school_id' => $this->school->id,
        ]);

        AuditLog::create([
            'school_id' => $this->school->id,
            'user_id' => $operator->id,
            'aksi' => 'impersonate.start',
            'model_type' => User::class,
            'model_id' => (string) $target->id,
            'after' => ['target' => $target->name, 'via' => 'platform'],
            'ip_address' => request()->ip(),
        ]);

        auth()->login($target);

        $this->redirect(route('dashboard'), navigate: true);
    }

    /**
     * Cari kandidat target impersonate di sekolah ini.
     *
     * Hanya member pemegang peran itu (guard web, tim sekolah ini) yang
     * dicari. Kueri langsung ke pivot model_has_roles karena relasi
     * roles() Spatie selalu terskop tim aktif saat ini — di panel
     * platform tim = 0, sehingga peran tim sekolah tak terlihat lewat
     * whereHas('roles'). Operator sendiri boleh menjadi kandidat bila
     * memang anggota sekolah dan memegang peran admin (kasus peran
     * ganda); operator platform lain dikecualikan.
     */
    private function cariTargetImpersonate(User $operator, string $role): ?User
    {
        return User::whereHas('memberships', fn ($q) => $q->where('school_id', $this->school->id)->aktif())
            ->where(fn ($q) => $q->where('is_platform', false)->orWhere('id', $operator->id))
            ->whereIn('users.id', function ($sub) use ($role) {
                $sub->select('model_has_roles.model_id')
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('model_has_roles.model_type', (new User)->getMorphClass())
                    ->where('model_has_roles.team_id', $this->school->id)
                    ->where('roles.guard_name', 'web')
                    ->where('roles.name', $role);
            })
            ->orderByRaw('is_platform asc')
            ->orderBy('id')
            ->first();
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

    private function ensurePlatformPermission(string $permission): User
    {
        $operator = auth()->user();

        abort_unless(
            $operator instanceof User
                && $operator->is_platform
                && $operator->hasPermissionTo($permission, 'platform'),
            403,
        );

        return $operator;
    }
}
