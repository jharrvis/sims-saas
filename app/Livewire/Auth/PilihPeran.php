<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\AuditLog;
use App\Models\School;
use App\Tenancy\TenantContext;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Layar pilih peran setelah login untuk akun peran ganda
 * (operator platform yang juga admin satu/beberapa sekolah).
 */
class PilihPeran extends Component
{
    public function mount(): void
    {
        $user = auth()->user();

        if (! $user || $user->ruteSetelahLogin() !== 'pilih-peran') {
            $this->redirect(route($user?->ruteSetelahLogin() ?? 'login'), navigate: true);
        }
    }

    public function kePlatform(): void
    {
        $this->redirect(route('platform.dashboard'), navigate: true);
    }

    public function keSekolah(int $schoolId, TenantContext $tenant): void
    {
        $user = auth()->user();
        $membership = $user->activeMemberships()->with('school')->where('school_id', $schoolId)->first();

        if (! $membership) {
            $this->redirect(route('pilih-peran'), navigate: true);

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
        $sekolah = auth()->user()->activeMemberships()->with('school')->get()->pluck('school');

        return view('livewire.auth.pilih-peran', ['sekolah' => $sekolah])
            ->layout('layouts.guest', ['title' => 'Pilih Peran']);
    }
}
