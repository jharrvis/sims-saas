<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Domains\Identity\Services\OtpRateLimited;
use App\Domains\Identity\Services\OtpService;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Login WA OTP — metode utama (Dok 09 §3.1).
 * Langkah 1: nomor WA → kirim OTP. Langkah 2: kode 6 digit → login.
 */
class Login extends Component
{
    public string $phone = '';

    public string $code = '';

    public int $step = 1;

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

        // 2FA opt-in: bila user sudah mengaktifkan 2FA → minta verifikasi TOTP
        // (semua peran, termasuk platform yang mengaktifkannya sendiri).
        if ($user->hasMfaEnabled()) {
            session(['mfa_pending_user_id' => $user->id]);

            $this->redirect(route('mfa.verify'), navigate: true);

            return;
        }

        Auth::login($user, remember: true);
        AuditLog::create([
            'user_id' => $user->id, 'aksi' => 'login', 'model_type' => User::class,
            'model_id' => (string) $user->id, 'after' => ['via' => 'wa_otp'],
            'ip_address' => request()->ip(),
        ]);

        // Operator platform langsung ke panel platform, bukan dashboard sekolah.
        // Akun peran ganda (platform + sekolah) memilih dulu di pilih-peran.
        $this->redirect(route($user->ruteSetelahLogin()), navigate: true);
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
        return view('livewire.auth.login')->layout('layouts.guest', ['title' => 'Masuk']);
    }
}
