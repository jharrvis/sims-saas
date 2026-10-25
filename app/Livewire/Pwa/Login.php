<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Identity\Services\OtpRateLimited;
use App\Domains\Identity\Services\OtpService;
use App\Domains\Pwa\Services\PwaIdentityService;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Login PWA via WA OTP (P-01, Dok 09 §3.1).
 * Setelah OTP valid: buat/cari user, tautkan ke siswa/wali via nomor WA.
 */
class Login extends Component
{
    public string $tab = 'otp';

    public string $phone = '';

    public string $code = '';

    public int $step = 1;

    public string $username = '';

    public string $password = '';

    public ?string $error = null;

    public ?string $info = null;

    public function requestOtp(OtpService $otp, PwaIdentityService $identity): void
    {
        $this->validate(['phone' => ['required', 'string', 'max:20']]);
        $this->error = null;

        // Gerbang: OTP hanya dikirim ke nomor yang terdaftar sebagai nomor
        // WA siswa/wali. Nomor acak ditolak sebelum ada pesan WA terkirim.
        if (! $identity->isPhoneRegistered($this->phone)) {
            $this->error = 'Nomor WhatsApp tidak terdaftar di sekolah manapun. Hubungi sekolah untuk mendaftarkan nomor Anda.';

            return;
        }

        try {
            $otp->request($this->phone, 'login', config('app.name', 'SIMS'));
        } catch (OtpRateLimited $e) {
            $this->error = $e->getMessage();

            return;
        }

        $this->step = 2;
        $this->info = 'Kode OTP dikirim via WhatsApp. Berlaku 5 menit.';
    }

    public function verify(OtpService $otp, PwaIdentityService $identity): void
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

        $phone = PwaIdentityService::normalize($this->phone);

        $user = User::where('phone', $phone)->first();

        if (! $user) {
            $user = User::create([
                'username' => 'pwa-'.$phone,
                'name' => 'Pengguna PWA',
                'phone' => $phone,
            ]);
        }

        // Tautkan ke siswa/wali + membership + peran (idempoten).
        $identity->linkByPhone($user);

        if ($user->mfaSecret?->isConfirmed()) {
            session([
                'mfa_pending_user_id' => $user->id,
                'pwa_login_pending' => true,
            ]);

            $this->redirect(route('mfa.verify'), navigate: true);

            return;
        }

        Auth::login($user, remember: true);
        session()->forget('pwa_login_pending');

        AuditLog::create([
            'user_id' => $user->id,
            'aksi' => 'login_pwa',
            'model_type' => User::class,
            'model_id' => (string) $user->id,
            'after' => ['via' => 'wa_otp'],
            'ip_address' => request()->ip(),
        ]);

        $this->redirect(route('pwa.home'), navigate: true);
    }

    public function back(): void
    {
        $this->step = 1;
        $this->code = '';
        $this->error = null;
        $this->info = null;
    }

    /**
     * Fallback username + password untuk pengguna PWA yang sudah mengatur
     * password di Keamanan Akun (mis. saat WhatsApp bermasalah).
     */
    public function loginPassword(PwaIdentityService $identity): void
    {
        $this->validate([
            'username' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string'],
        ]);
        $this->error = null;

        $key = 'pwa-login-password:'.strtolower($this->username).':'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->error = 'Terlalu banyak percobaan gagal. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.';

            return;
        }

        $phone = PwaIdentityService::normalize($this->username);
        $user = User::where('username', $this->username)
            ->orWhere('phone', $phone)
            ->first();

        if (! $user || ! $user->password || ! Hash::check($this->password, $user->password)) {
            RateLimiter::hit($key, 900);

            AuditLog::create([
                'user_id' => $user?->id,
                'aksi' => 'login_gagal',
                'model_type' => User::class,
                'model_id' => (string) ($user?->id ?? 0),
                'after' => ['via' => 'pwa_password', 'username' => $this->username],
                'ip_address' => request()->ip(),
            ]);

            $this->error = 'Username atau password salah.';

            return;
        }

        RateLimiter::clear($key);

        if ($user->phone) {
            $identity->linkByPhone($user);
        }

        if ($user->mfaSecret?->isConfirmed()) {
            session([
                'mfa_pending_user_id' => $user->id,
                'pwa_login_pending' => true,
            ]);

            $this->redirect(route('mfa.verify'), navigate: true);

            return;
        }

        Auth::login($user, remember: true);
        session()->forget('pwa_login_pending');

        AuditLog::create([
            'user_id' => $user->id,
            'aksi' => 'login_pwa',
            'model_type' => User::class,
            'model_id' => (string) $user->id,
            'after' => ['via' => 'password'],
            'ip_address' => request()->ip(),
        ]);

        $this->redirect(route('pwa.home'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.pwa.login')->layout('layouts.auth', [
            'title' => 'Masuk',
            'variant' => 'pwa',
            'eyebrow' => 'SIMS MOBILE',
            'storyTitle' => 'Sekolah dalam genggaman.',
            'storyText' => 'Masuk untuk melihat jadwal, nilai, kehadiran, dan informasi sekolah Anda.',
        ]);
    }
}
