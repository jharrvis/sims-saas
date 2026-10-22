<?php

declare(strict_types=1);

namespace App\Livewire\Pengaturan;

use App\Models\AuditLog;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Atur / ganti kata sandi akun sendiri (D-3).
 *
 * Kata sandi menjadi cadangan bila login WhatsApp bermasalah: pengguna dapat
 * masuk dengan username + kata sandi. Minimal 8 karakter (FR-903).
 */
class PasswordSetup extends Component
{
    public string $password = '';

    public string $password_confirmation = '';

    public ?string $sukses = null;

    public ?string $error = null;

    public function simpan(): void
    {
        abort_unless(auth()->check(), 403);

        $this->validate(
            [
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            [],
            ['password' => 'password baru'],
        );

        $this->reset(['sukses', 'error']);

        /** @var User $user */
        $user = auth()->user();

        $user->password = $this->password;
        $user->save();

        AuditLog::create([
            'school_id' => app(TenantContext::class)->schoolId(),
            'user_id' => $user->id,
            'aksi' => 'pengguna.password',
            'model_type' => User::class,
            'model_id' => (string) $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);

        $this->reset(['password', 'password_confirmation']);
        $this->sukses = 'Password berhasil disimpan. Anda dapat masuk dengan username + password.';
    }

    public function render(): View
    {
        return view('livewire.pengaturan.password-setup', [
            'punyaPassword' => filled(auth()->user()?->password),
        ]);
    }
}
