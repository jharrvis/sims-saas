<?php

declare(strict_types=1);

namespace App\Livewire\Pengaturan;

use App\Models\AuditLog;
use App\Support\RoleCatalog;
use App\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Form tambah/ubah peran + matriks hak akses pada sekolah aktif.
 *
 * Peran kustom dibuat dengan `team_id` sekolah aktif sehingga tidak terlihat
 * atau terpakai oleh sekolah lain. `superadmin` bersifat read-only agar tidak
 * ada admin yang tanpa sengaja mencabut akses penuh dari peran tertinggi.
 */
class PeranForm extends Component
{
    #[Locked]
    public ?Role $role = null;

    public string $name = '';

    /** @var array<int,string> */
    public array $permissions = [];

    public bool $sistem = false;

    public ?string $error = null;

    public function mount(?Role $role = null): void
    {
        abort_unless(auth()->user()?->can($role?->exists ? 'peran.update' : 'peran.create'), 403);

        $schoolId = $this->schoolId();
        $this->role = $role?->exists ? $role : null;

        if ($this->role === null) {
            return;
        }

        abort_unless($this->peranMilikSekolah($schoolId), 404);

        $this->name = $this->role->name;
        $this->permissions = $this->role->permissions->pluck('name')->values()->all();
        $this->sistem = RoleCatalog::isSystem($this->role->name);
    }

    public function pilihSemua(): void
    {
        $this->permissions = Permission::query()
            ->where('guard_name', 'web')
            ->pluck('name')
            ->values()
            ->all();
    }

    public function kosongkan(): void
    {
        $this->permissions = [];
    }

    public function pilihGrup(string $prefix): void
    {
        $this->permissions = array_values(array_unique(array_merge(
            $this->permissions,
            $this->namaPermissionGrup($prefix),
        )));
    }

    public function kosongkanGrup(string $prefix): void
    {
        $this->permissions = array_values(array_diff(
            $this->permissions,
            $this->namaPermissionGrup($prefix),
        ));
    }

    public function simpan(): void
    {
        $schoolId = $this->schoolId();
        $sedangUbah = $this->role !== null;

        abort_unless(auth()->user()->can($sedangUbah ? 'peran.update' : 'peran.create'), 403);

        if ($sedangUbah) {
            abort_unless($this->peranMilikSekolah($schoolId), 404);

            // Peran tertinggi tidak boleh dikurangi hak aksesnya lewat UI.
            abort_if($this->role->name === 'superadmin', 403);
        }

        $namaSistem = $sedangUbah && RoleCatalog::isSystem($this->role->name);

        $aturanNama = ['required', 'string', 'max:50'];

        if (! $namaSistem) {
            $unik = Rule::unique('roles', 'name')
                ->where('team_id', $schoolId)
                ->where('guard_name', 'web');

            if ($sedangUbah) {
                $unik->ignore($this->role->id);
            }

            $aturanNama[] = 'alpha_dash';
            $aturanNama[] = $unik;
        }

        $this->validate([
            'name' => $aturanNama,
            'permissions' => ['array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where('guard_name', 'web'),
            ],
        ]);

        $daftarPermission = array_values(array_unique(array_filter(
            $this->permissions,
            fn ($nama) => $nama !== '',
        )));

        if (! $sedangUbah) {
            /** @var Role $role */
            $role = Role::create([
                'name' => $this->name,
                'guard_name' => 'web',
                'team_id' => $schoolId,
            ]);

            $role->syncPermissions($daftarPermission);

            $this->catatAudit($schoolId, 'peran.create', $role->id, null, [
                'name' => $role->name,
                'permissions' => $daftarPermission,
            ]);

            session()->flash('ok', 'Peran "'.RoleCatalog::label($this->name).'" berhasil dibuat.');

            $this->redirectRoute('pengaturan.peran');

            return;
        }

        /** @var Role $role */
        $role = $this->role;

        $sebelum = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        if (! $namaSistem && $role->name !== $this->name) {
            $role->update(['name' => $this->name]);
        }

        $role->syncPermissions($daftarPermission);

        $this->catatAudit($schoolId, 'peran.update', $role->id, $sebelum, [
            'name' => $role->name,
            'permissions' => $daftarPermission,
        ]);

        session()->flash('ok', 'Hak akses peran "'.RoleCatalog::label($role->name).'" disimpan.');

        $this->redirectRoute('pengaturan.peran');
    }

    public function render(): View
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['name']);

        $grup = RoleCatalog::permissionGroups($permissions);

        return view('livewire.pengaturan.peran-form', [
            'grupPermission' => $grup,
            'totalPermission' => $permissions->count(),
            'superadmin' => $this->role?->name === 'superadmin',
        ])->layout('layouts.app', [
            'title' => $this->role !== null ? 'Ubah Peran' : 'Buat Peran',
        ]);
    }

    /**
     * @return array<int,string>
     */
    private function namaPermissionGrup(string $prefix): array
    {
        $kedalaman = substr_count($prefix, '.') + 1;

        return Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', $prefix.'.%')
            ->get(['name'])
            ->map(fn (Permission $permission): string => (string) $permission->name)
            ->filter(fn (string $nama): bool => substr_count($nama, '.') === $kedalaman)
            ->values()
            ->all();
    }

    /**
     * Pastikan peran benar-benar milik tim sekolah aktif (cegah akses lintas tenant).
     */
    private function peranMilikSekolah(int $schoolId): bool
    {
        return Role::query()
            ->whereKey($this->role?->getKey())
            ->where('team_id', $schoolId)
            ->where('guard_name', 'web')
            ->exists();
    }

    /**
     * @param  array<string,mixed>|null  $before
     * @param  array<string,mixed>  $after
     */
    private function catatAudit(int $schoolId, string $aksi, int $modelId, ?array $before, array $after): void
    {
        AuditLog::create([
            'school_id' => $schoolId,
            'user_id' => auth()->id(),
            'aksi' => $aksi,
            'model_type' => Role::class,
            'model_id' => (string) $modelId,
            'before' => $before,
            'after' => $after,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
    }

    private function schoolId(): int
    {
        $schoolId = (int) app(TenantContext::class)->schoolId();
        abort_if($schoolId === 0, 403);

        return $schoolId;
    }
}
