<?php

declare(strict_types=1);

namespace App\Livewire\Pengaturan;

use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\User;
use App\Support\RoleCatalog;
use App\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

/**
 * Kelola pengguna sekolah (Dok 05 §4.3): daftar anggota, pencarian,
 * filter peran/status, serta aktif/nonaktifkan akses per sekolah.
 *
 * Identitas pengguna bersifat GLOBAL (ADR-0004); status yang diubah di sini
 * adalah keanggotaan (membership) pada sekolah aktif saja.
 */
class PenggunaIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $peran = '';

    public string $status = '';

    public ?string $info = null;

    public ?string $error = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('pengguna.view'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPeran(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Aktifkan / nonaktifkan akses pengguna pada sekolah aktif.
     */
    public function toggleStatus(int $userId): void
    {
        abort_unless(auth()->user()->can('pengguna.update'), 403);

        $schoolId = (int) app(TenantContext::class)->schoolId();
        abort_if($schoolId === 0, 403);

        $membership = Membership::query()
            ->where('user_id', $userId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        if ($userId === auth()->id()) {
            $this->error = 'Anda tidak dapat mengubah status akun sendiri.';

            return;
        }

        $sedangAktif = $membership->status === 'aktif';

        if ($sedangAktif && $this->adminAktifTersisa($schoolId, $userId) === 0) {
            $this->error = 'Tidak bisa menonaktifkan admin terakhir sekolah ini. Tetapkan admin lain terlebih dahulu.';

            return;
        }

        $membership->update(['status' => $sedangAktif ? 'dinonaktifkan' : 'aktif']);

        AuditLog::create([
            'school_id' => $schoolId,
            'user_id' => auth()->id(),
            'aksi' => 'pengguna.status',
            'model_type' => User::class,
            'model_id' => (string) $userId,
            'before' => ['status' => $sedangAktif ? 'aktif' : 'dinonaktifkan'],
            'after' => ['status' => $sedangAktif ? 'dinonaktifkan' : 'aktif'],
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);

        $this->reset('error');
        $this->info = $sedangAktif
            ? 'Akses pengguna dinonaktifkan. Identitas globalnya tetap tersimpan.'
            : 'Akses pengguna diaktifkan kembali.';
    }

    public function render(): View
    {
        $schoolId = (int) app(TenantContext::class)->schoolId();
        abort_if($schoolId === 0, 403);

        $users = User::query()
            ->whereHas('memberships', fn ($q) => $q->where('school_id', $schoolId))
            ->when($this->search !== '', fn ($q) => $q->where(function ($w): void {
                $w->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('username', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            }))
            ->when($this->peran !== '', fn ($q) => $q->whereHas(
                'roles',
                fn ($r) => $r->where('name', $this->peran),
            ))
            ->when($this->status !== '', fn ($q) => $q->whereHas(
                'memberships',
                fn ($m) => $m->where('school_id', $schoolId)->where('status', $this->status),
            ))
            ->with([
                'roles',
                'memberships' => fn ($m) => $m->where('school_id', $schoolId),
            ])
            ->orderBy('name')
            ->paginate(15);

        /** @var Collection<int, Role> $roles */
        $roles = Role::query()
            ->where('team_id', $schoolId)
            ->where('guard_name', 'web')
            ->get()
            ->sortBy(fn (Role $role) => RoleCatalog::sortIndex($role->name).'-'.$role->name)
            ->values();

        return view('livewire.pengaturan.pengguna-index', [
            'users' => $users,
            'roles' => $roles,
            'jumlahAktif' => User::query()
                ->whereHas('memberships', fn ($m) => $m->where('school_id', $schoolId)->where('status', 'aktif'))
                ->count(),
            'jumlahNonaktif' => User::query()
                ->whereHas('memberships', fn ($m) => $m->where('school_id', $schoolId)->where('status', 'dinonaktifkan'))
                ->count(),
        ])->layout('layouts.app', ['title' => 'Kelola Pengguna']);
    }

    private function adminAktifTersisa(int $schoolId, int $kecualiUserId): int
    {
        return User::query()
            ->whereKeyNot($kecualiUserId)
            ->whereHas('memberships', fn ($m) => $m->where('school_id', $schoolId)->where('status', 'aktif'))
            ->whereHas('roles', fn ($r) => $r->whereIn('name', RoleCatalog::ADMIN_ROLES))
            ->count();
    }
}
