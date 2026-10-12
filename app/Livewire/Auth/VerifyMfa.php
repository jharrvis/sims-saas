<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Domains\Identity\Services\TotpService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Verifikasi MFA saat login — langkah ke-2 untuk peran platform (FR-904).
 */
class VerifyMfa extends Component
{
    public string $code = '';

    public ?string $error = null;

    public function mount(): void
    {
        $user = $this->resolveUser();

        // Tidak ada sesi verifikasi → kembali ke login.
        if (! $user) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        // Tidak butuh verifikasi MFA → ke dashboard.
        if (! $user->is_platform || ! $user->mfaSecret?->isConfirmed()) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    /**
     * User yang sedang diverifikasi: dari sesi pra-login, atau user
     * terautentikasi yang sesinya belum terverifikasi (remember-me).
     */
    private function resolveUser(): ?User
    {
        $pendingId = session('mfa_pending_user_id');

        if ($pendingId) {
            return User::find($pendingId);
        }

        /** @var User|null $authed */
        $authed = auth()->user();

        return $authed;
    }

    public function verify(TotpService $totp): void
    {
        $this->validate(['code' => ['required', 'string']]);
        $this->error = null;

        $user = $this->resolveUser();

        if (! $user || ! $user->is_platform || ! $user->mfaSecret?->isConfirmed()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $valid = $totp->verify($user, $this->code)
            || $totp->useRecoveryCode($user, $this->code);

        if (! $valid) {
            $this->error = 'Kode MFA salah. Coba lagi atau pakai kode pemulihan.';

            return;
        }

        session()->forget('mfa_pending_user_id');
        session(['mfa_verified_'.$user->id => true]);

        Auth::login($user, remember: true);

        $this->redirect(route($user->ruteSetelahLogin()), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.verify-mfa')->layout('layouts.guest', ['title' => 'Verifikasi 2FA']);
    }
}
