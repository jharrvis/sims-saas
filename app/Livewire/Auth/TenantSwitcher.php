<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\AuditLog;
use App\Models\School;
use App\Tenancy\TenantContext;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Pengalih tenant aktif — untuk akun global dengan banyak membership.
 * Setiap perpindahan dicatat di audit_logs.
 */
class TenantSwitcher extends Component
{
    public ?string $error = null;

    public function switch(int $schoolId, TenantContext $tenant): void
    {
        $user = auth()->user();
        $this->error = null;

        $membership = $user->activeMemberships()->where('school_id', $schoolId)->first();

        if (! $membership) {
            $this->error = 'Anda bukan anggota aktif sekolah tersebut.';

            return;
        }

        $tenant->set($membership->school);
        session(['active_school_id' => $schoolId]);

        AuditLog::create([
            'school_id' => $schoolId,
            'user_id' => $user->id,
            'aksi' => 'switch_tenant',
            'model_type' => School::class,
            'model_id' => (string) $schoolId,
            'ip_address' => request()->ip(),
        ]);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render(): View
    {
        $schools = auth()->user()->activeMemberships()
            ->with('school:id,nama,slug,jenjang,status_langganan')
            ->get()
            ->map(fn ($m) => $m->school);

        return view('livewire.auth.tenant-switcher', ['schools' => $schools])
            ->layout('layouts.app', ['title' => 'Pilih Sekolah']);
    }
}
