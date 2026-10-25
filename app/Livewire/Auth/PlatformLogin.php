<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Domains\Identity\Services\OtpRateLimited;
use App\Domains\Identity\Services\OtpService;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Pintu login khusus operator platform (D-1).
 * Hanya akun is_platform yang bisa masuk lewat sini.
 * Metode: WA OTP atau username+password (D-3), lalu TOTP bila aktif (D-2 opt-in).
 */
class PlatformLogin extends Component
{
    public string $tab = 'wa';

    public string $phone = '';

    public string $code = '';

    public int $step = 1;

    public string $username = '';

    public string $password = '';

    public ?string $error = null;

    public ?string $info = null;

    public function requestOtp(OtpService $otp): void
    {
        $this->validate(['phone' => ['required', 'string', 'max:20']]);
        $this->error = null;

        $user = User::where('phone', $this->normalize($this->phone))->where('is_platform', true)->first();

        if (! $user) {
            $this->error = 'Nomor ini tidak terdaftar sebagai operator platform.';

            return;
        }

        try {
            $otp->request($this->phone, 'login', config('app.name', 'SIMS'));
        } catch (OtpRateLimited $e) {
            $this->error = $e->getMessage();

            return;
        }

        $this->step = 2;
        $this->info = 'Kode OTP telah dikirim via WhatsApp. Berlaku 5 menit.';
    }

    public function verify(OtpService $otp): void
    {
        $this->validate([
            'phone' => ['required', 'string', 'max:20'],
            'code' => ['required', 'string', 'size:6'],
        ]);
        $this->error = null;

        if (! $otp->verify($this->phone, 'login', $this->code)) {
            $this->error = 'Kode salah atau kedaluwarsa.';

            return;
        }

        $user = User::where('phone', $this->normalize($this->phone))->where('is_platform', true)->first();

        if (! $user) {
            $this->error = 'Nomor ini tidak terdaftar sebagai operator platform.';

            return;
        }

        $this->masuk($user, 'wa_otp');
    }

    public function loginPassword(): void
    {
        $this->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);
        $this->error = null;

        $key = 'platform-login:'.strtolower($this->username).':'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $detik = RateLimiter::availableIn($key);
            $this->error = "Terlalu banyak percobaan gagal. Coba lagi dalam {$detik} detik.";

            return;
        }

        $user = User::where('username', $this->username)->where('is_platform', true)->first();

        if (! $user || ! $user->password || ! Hash::check($this->password, $user->password)) {
            RateLimiter::hit($key, 900);
            $this->error = 'Username atau password salah.';

            return;
        }

        RateLimiter::clear($key);
        $this->masuk($user, 'password');
    }

    private function masuk(User $user, string $via): void
    {
        // TOTP opt-in (D-2): hanya bila user mengaktifkan.
        if ($user->mfaSecret?->isConfirmed()) {
            session(['mfa_pending_user_id' => $user->id]);
            $this->redirect(route('mfa.verify'), navigate: false);

            return;
        }

        Auth::login($user, remember: true);
        session(['pintu_login' => 'platform']);
        AuditLog::create([
            'user_id' => $user->id, 'aksi' => 'login', 'model_type' => User::class,
            'model_id' => (string) $user->id, 'after' => ['via' => $via, 'pintu' => 'platform'],
            'ip_address' => request()->ip(),
        ]);

        $this->redirect(route('platform.dashboard'), navigate: false);
    }

    public function back(): void
    {
        $this->step = 1;
        $this->code = '';
        $this->error = null;
        $this->info = null;
    }

    private function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }

    public function render(): View
    {
        return view('livewire.auth.platform-login')->layout('layouts.auth', [
            'variant' => 'desktop',
            'title' => 'Masuk Platform',
        ]);
    }
}
