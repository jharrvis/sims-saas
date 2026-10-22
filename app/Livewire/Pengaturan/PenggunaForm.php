<?php

declare(strict_types=1);

namespace App\Livewire\Pengaturan;

use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\User;
use App\Support\RoleCatalog;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Role;

/**
 * Form tambah/ubah pengguna sekolah.
 *
 * Satu user = identitas global (ADR-0004). Menambah pengguna dari sini
 * membuat membership pada sekolah aktif + menetapkan peran pada tim sekolah
 * tersebut, sehingga hak akses tidak bocor ke sekolah lain.
 */
class PenggunaForm extends Component
{
    #[Locked]
    public ?User $user = null;

    public string $name = '';

    public string $username = '';

    public string $phone = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $status = 'aktif';

    /** @var array<int,string> */
    public array $roles = [];

    public ?string $error = null;

    public bool $identitasGlobal = false;

    public function mount(?User $user = null): void
    {
        abort_unless(auth()->user()?->can($user?->exists ? 'pengguna.update' : 'pengguna.create'), 403);

        $schoolId = $this->schoolId();
        $this->user = $user?->exists ? $user : null;
        $this->status = 'aktif';

        if ($this->user === null) {
            return;
        }

        abort_unless(
            $this->user->memberships()->where('school_id', $schoolId)->exists(),
            404,
        );

        $this->name = $this->user->name;
        $this->username = $this->user->username;
        $this->phone = (string) $this->user->phone;
        $this->email = (string) $this->user->email;
        $this->status = (string) ($this->user->memberships()
            ->where('school_id', $schoolId)
            ->value('status') ?? 'aktif');
        $this->roles = $this->user->getRoleNames()->all();

        $this->identitasGlobal = $this->user->memberships()
            ->where('school_id', '!=', $schoolId)
            ->exists();
    }

    public function simpan(): void
    {
        $schoolId = $this->schoolId();
        $this->phone = $this->normalisasiTelepon($this->phone);
        $this->error = null;

        $this->validate();

        $isSelf = $this->user !== null && $this->user->id === auth()->id();

        if ($isSelf && $this->status !== 'aktif') {
            $this->addError('status', 'Anda tidak dapat menonaktifkan akses akun sendiri.');

            return;
        }

        if ($this->user === null && $this->phone === '' && $this->password === '') {
            $this->addError('phone', 'Isi nomor WhatsApp atau kata sandi agar pengguna dapat masuk.');

            return;
        }

        if ($this->password !== '' && ! auth()->user()->can('pengguna.reset-password')) {
            $this->addError('password', 'Anda tidak berhak mengubah kata sandi pengguna.');

            return;
        }

        if ($isSelf && ! $this->masihAdminSetelahSimpan($schoolId)) {
            $this->addError('roles', 'Sekolah harus memiliki minimal satu admin aktif. Tetapkan admin lain terlebih dahulu.');

            return;
        }

        DB::transaction(function () use ($schoolId): void {
            if ($this->user !== null) {
                $this->perbarui($schoolId);

                return;
            }

            $this->buat($schoolId);
        });

        session()->flash('ok', $this->user !== null
            ? 'Data pengguna berhasil diperbarui.'
            : 'Pengguna baru berhasil dibuat dan dapat langsung masuk.');

        $this->redirectRoute('pengaturan.pengguna');
    }

    public function render(): View
    {
        $schoolId = $this->schoolId();

        $roles = Role::query()
            ->where('team_id', $schoolId)
            ->where('guard_name', 'web')
            ->get()
            ->sortBy(fn (Role $role) => RoleCatalog::sortIndex($role->name).'-'.$role->name)
            ->values();

        return view('livewire.pengaturan.pengguna-form', [
            'rolesTersedia' => $roles,
            'isSelf' => $this->user !== null && $this->user->id === auth()->id(),
        ])->layout('layouts.app', [
            'title' => $this->user !== null ? 'Ubah Pengguna' : 'Tambah Pengguna',
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        $schoolId = $this->schoolId();
        $userId = $this->user?->id;

        $username = Rule::unique('users', 'username');
        $phone = Rule::unique('users', 'phone');
        $email = Rule::unique('users', 'email');

        if ($userId !== null) {
            $username->ignore($userId);
            $phone->ignore($userId);
            $email->ignore($userId);
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', $username],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^62[0-9]{8,15}$/', $phone],
            'email' => ['nullable', 'email', 'max:150', $email],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:aktif,dinonaktifkan'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')
                    ->where('team_id', $schoolId)
                    ->where('guard_name', 'web'),
            ],
        ];
    }

    /**
     * @return array<string,string>
     */
    protected function messages(): array
    {
        return [
            'phone.regex' => 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.',
            'roles.required' => 'Pilih minimal satu peran untuk pengguna ini.',
            'roles.min' => 'Pilih minimal satu peran untuk pengguna ini.',
        ];
    }

    private function buat(int $schoolId): void
    {
        /** @var User $user */
        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'phone' => $this->phone !== '' ? $this->phone : null,
            'email' => $this->email !== '' ? $this->email : null,
            'password' => $this->password !== '' ? $this->password : null,
            'is_platform' => false,
        ]);

        Membership::create([
            'user_id' => $user->id,
            'school_id' => $schoolId,
            'status' => $this->status,
        ]);

        $user->assignRole($this->roles);

        $this->catatAudit($schoolId, 'pengguna.create', $user->id, null, [
            'name' => $user->name,
            'username' => $user->username,
            'phone' => $user->phone,
            'status' => $this->status,
            'roles' => $this->roles,
        ]);
    }

    private function perbarui(int $schoolId): void
    {
        /** @var User $user */
        $user = $this->user;

        $sebelum = [
            'name' => $user->name,
            'username' => $user->username,
            'phone' => $user->phone,
            'email' => $user->email,
            'status' => $user->memberships()->where('school_id', $schoolId)->value('status'),
            'roles' => $user->getRoleNames()->all(),
        ];

        $user->name = $this->name;
        $user->username = $this->username;
        $user->phone = $this->phone !== '' ? $this->phone : null;
        $user->email = $this->email !== '' ? $this->email : null;

        if ($this->password !== '') {
            $user->password = $this->password;
        }

        $user->save();

        $membership = Membership::firstOrNew([
            'user_id' => $user->id,
            'school_id' => $schoolId,
        ]);
        $membership->status = $this->status;
        $membership->save();

        $user->syncRoles($this->roles);

        $this->catatAudit($schoolId, 'pengguna.update', $user->id, $sebelum, [
            'name' => $user->name,
            'username' => $user->username,
            'phone' => $user->phone,
            'email' => $user->email,
            'status' => $this->status,
            'roles' => $this->roles,
        ]);
    }

    private function masihAdminSetelahSimpan(int $schoolId): bool
    {
        /** @var User $user */
        $user = $this->user;

        $tetapAdmin = $this->status === 'aktif'
            && count(array_intersect(RoleCatalog::ADMIN_ROLES, $this->roles)) > 0;

        if ($tetapAdmin) {
            return true;
        }

        return $this->adminAktifTersisa($schoolId, $user->id) > 0;
    }

    private function adminAktifTersisa(int $schoolId, int $kecualiUserId): int
    {
        return User::query()
            ->whereKeyNot($kecualiUserId)
            ->whereHas('memberships', fn ($m) => $m->where('school_id', $schoolId)->where('status', 'aktif'))
            ->whereHas('roles', fn ($r) => $r->whereIn('name', RoleCatalog::ADMIN_ROLES))
            ->count();
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
            'model_type' => User::class,
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

    private function normalisasiTelepon(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }
}
