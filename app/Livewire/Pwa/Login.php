<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Identity\Services\OtpRateLimited;
use App\Domains\Identity\Services\OtpService;
use App\Domains\Pwa\Services\PwaIdentityService;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Login PWA via WA OTP (P-01, Dok 09 §3.1).
 * Setelah OTP valid: buat/cari user, tautkan ke siswa/wali via nomor WA.
 */
class Login extends Component
{
    public string $phone = '';

    public string $code = '';

    public int $step = 1;

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

        Auth::login($user, remember: true);

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

    public function render(): View
    {
        return view('livewire.pwa.login')->layout('layouts.pwa', [
            'title' => 'Masuk',
            'hideHeader' => true,
            'hideNav' => true,
        ]);
    }
}
