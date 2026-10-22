<?php

declare(strict_types=1);

namespace App\Livewire\Pengaturan;

use App\Models\AuditLog;
use App\Support\RoleCatalog;
use App\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Spatie\Permission\Models\Role;

/**
 * Daftar peran & hak akses pada sekolah aktif (Spatie teams).
 *
 * Peran bawaan sistem tidak dapat dihapus/di-rename; hak aksesnya masih dapat
 * disesuaikan (kecuali `superadmin`) agar sekolah bisa menyesuaikan kebutuhan.
 */
class PeranIndex extends Component
{
    public ?string $info = null;

    public ?string $error = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('peran.view'), 403);
    }

    public function hapus(int $roleId): void
    {
        abort_unless(auth()->user()->can('peran.delete'), 403);

        $schoolId = $this->schoolId();

        /** @var Role $role */
        $role = Role::query()
            ->where('team_id', $schoolId)
            ->where('guard_name', 'web')
            ->findOrFail($roleId);

        if (RoleCatalog::isSystem($role->name)) {
            $this->error = 'Peran bawaan sistem tidak dapat dihapus.';

            return;
        }

        if ($role->users()->exists()) {
            $this->error = 'Peran masih dipakai pengguna. Pindahkan mereka ke peran lain terlebih dahulu.';

            return;
        }

        $nama = $role->name;
        $role->delete();

        AuditLog::create([
            'school_id' => $schoolId,
            'user_id' => auth()->id(),
            'aksi' => 'peran.delete',
            'model_type' => Role::class,
            'model_id' => (string) $roleId,
            'before' => ['name' => $nama],
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);

        $this->reset('error');
        $this->info = 'Peran "'.RoleCatalog::label($nama).'" dihapus.';
    }

    public function render(): View
    {
        $schoolId = $this->schoolId();

        /** @var Collection<int, Role> $roles */
        $roles = Role::query()
            ->where('team_id', $schoolId)
            ->where('guard_name', 'web')
            ->withCount(['permissions', 'users'])
            ->get()
            ->sortBy(fn (Role $role) => RoleCatalog::sortIndex($role->name).'-'.$role->name)
            ->values();

        return view('livewire.pengaturan.peran-index', [
            'roles' => $roles,
        ])->layout('layouts.app', ['title' => 'Peran & Hak Akses']);
    }

    private function schoolId(): int
    {
        $schoolId = (int) app(TenantContext::class)->schoolId();
        abort_if($schoolId === 0, 403);

        return $schoolId;
    }
}
