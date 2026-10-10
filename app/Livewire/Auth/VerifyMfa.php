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

    public function verify(TotpService $totp): void
    {
        $this->validate(['code' => ['required', 'string']]);
        $this->error = null;

        $userId = session('mfa_pending_user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
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

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.verify-mfa')->layout('layouts.guest', ['title' => 'Verifikasi 2FA']);
    }
}
