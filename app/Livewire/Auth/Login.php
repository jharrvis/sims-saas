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
 * Login — dua metode (D-3):
 * - Tab "WhatsApp": WA OTP (metode utama, Dok 09 §3.1).
 * - Tab "Password": username + password (fallback bila WA bermasalah).
 * Kebijakan FR-903: min. 8 karakter, kunci 5x gagal per username+IP.
 */
class Login extends Component
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
            AuditLog::create([
                'user_id' => null, 'aksi' => 'login_gagal', 'model_type' => User::class,
                'model_id' => '0', 'after' => ['phone' => $this->phone],
                'ip_address' => request()->ip(),
            ]);

            return;
        }

        $user = User::where('phone', $this->normalize($this->phone))->first();

        if (! $user) {
            $this->error = 'Nomor belum terdaftar di SIMS.';

            return;
        }

        // 2FA opt-in: minta verifikasi TOTP bila user mengaktifkannya.
        if ($user->mfaSecret?->isConfirmed()) {
            session(['mfa_pending_user_id' => $user->id]);

            $this->redirect(route('mfa.verify'), navigate: false);

            return;
        }

        Auth::login($user, remember: true);
        session(['pintu_login' => 'sekolah']);
        AuditLog::create([
            'user_id' => $user->id, 'aksi' => 'login', 'model_type' => User::class,
            'model_id' => (string) $user->id, 'after' => ['via' => 'wa_otp'],
            'ip_address' => request()->ip(),
        ]);

        // Operator platform langsung ke panel platform, bukan dashboard sekolah.
        // Akun peran ganda (platform + sekolah) memilih dulu di pilih-peran.
        $this->redirect(route($user->ruteSetelahLogin()), navigate: false);
    }

    public function back(): void
    {
        $this->step = 1;
        $this->code = '';
        $this->error = null;
        $this->info = null;
    }

    /**
     * Login username + password (D-3, fallback bila WA bermasalah).
     * FR-903: kunci 5x gagal per username+IP selama 15 menit.
     */
    public function loginPassword(): void
    {
        $this->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);
        $this->error = null;

        $key = 'login-password:'.strtolower($this->username).':'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $detik = RateLimiter::availableIn($key);
            $this->error = "Terlalu banyak percobaan gagal. Coba lagi dalam {$detik} detik.";

            return;
        }

        $user = User::where('username', $this->username)->first();

        if (! $user || ! $user->password || ! Hash::check($this->password, $user->password)) {
            RateLimiter::hit($key, 900); // 15 menit
            $this->error = 'Username atau password salah.';
            AuditLog::create([
                'user_id' => $user?->id, 'aksi' => 'login_gagal', 'model_type' => User::class,
                'model_id' => (string) ($user->id ?? 0), 'after' => ['via' => 'password', 'username' => $this->username],
                'ip_address' => request()->ip(),
            ]);

            return;
        }

        RateLimiter::clear($key);

        // 2FA opt-in: minta verifikasi TOTP bila user mengaktifkannya.
        if ($user->mfaSecret?->isConfirmed()) {
            session(['mfa_pending_user_id' => $user->id]);
            $this->redirect(route('mfa.verify'), navigate: false);

            return;
        }

        Auth::login($user, remember: true);
        session(['pintu_login' => 'sekolah']);
        AuditLog::create([
            'user_id' => $user->id, 'aksi' => 'login', 'model_type' => User::class,
            'model_id' => (string) $user->id, 'after' => ['via' => 'password'],
            'ip_address' => request()->ip(),
        ]);

        $this->redirect(route($user->ruteSetelahLogin()), navigate: false);
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
        return view('livewire.auth.login')->layout('layouts.auth', [
            'title' => 'Masuk',
            'variant' => 'desktop',
        ]);
    }
}
