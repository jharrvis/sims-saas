<?php

declare(strict_types=1);

namespace App\Livewire\Pengaturan;

use App\Domains\Identity\Services\OtpRateLimited;
use App\Domains\Identity\Services\OtpService;
use App\Domains\Identity\Services\TotpService;
use App\Models\AuditLog;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Pengaturan keamanan akun — aktifkan/nonaktifkan 2FA (opt-in, default OFF).
 *
 * - Mengaktifkan: diarahkan ke halaman setup 2FA (`2fa.setup`).
 * - Menonaktifkan: diverifikasi ulang via OTP WhatsApp.
 */
class Keamanan extends Component
{
    public ?string $error = null;

    public ?string $info = null;

    /** Langkah nonaktifkan: 0 = belum mulai, 2 = OTP terkirim. */
    public int $disableStep = 0;

    public string $otpCode = '';

    /**
     * Kirim OTP WhatsApp untuk konfirmasi penonaktifan 2FA.
     */
    public function mulaiNonaktifkan(OtpService $otp): void
    {
        $this->reset(['error', 'info', 'otpCode']);

        $user = auth()->user();

        if (! $user->hasMfaEnabled()) {
            $this->error = '2FA tidak sedang aktif.';

            return;
        }

        $phone = (string) $user->phone;

        if ($phone === '') {
            $this->error = 'Nomor WhatsApp belum terdaftar pada akun Anda.';

            return;
        }

        try {
            $otp->request($phone, 'mfa_disable', config('app.name', 'SIMS'));
        } catch (OtpRateLimited $e) {
            $this->error = $e->getMessage();

            return;
        }

        $this->disableStep = 2;
        $this->info = 'Kode OTP telah dikirim ke WhatsApp Anda. Berlaku 5 menit.';
    }

    /**
     * Verifikasi OTP → nonaktifkan 2FA.
     */
    public function nonaktifkan(OtpService $otp, TotpService $totp): void
    {
        $this->validate(
            ['otpCode' => ['required', 'string', 'size:6']],
            [],
            ['otpCode' => 'kode OTP'],
        );
        $this->error = null;

        $user = auth()->user();

        if (! $otp->verify((string) $user->phone, 'mfa_disable', $this->otpCode)) {
            $this->error = 'Kode OTP salah atau kedaluwarsa.';

            return;
        }

        $totp->disable($user);

        // Bersihkan penanda verifikasi sesi lama.
        session()->forget('mfa_verified_'.$user->id);

        AuditLog::create([
            'school_id' => app(TenantContext::class)->schoolId(),
            'user_id' => $user->id,
            'aksi' => 'mfa.disable',
            'model_type' => User::class,
            'model_id' => (string) $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);

        $this->reset(['disableStep', 'otpCode']);
        $this->info = '2FA berhasil dinonaktifkan.';
    }

    public function batalNonaktifkan(): void
    {
        $this->reset(['disableStep', 'otpCode', 'error', 'info']);
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.pengaturan.keamanan', [
            'enabled' => $user->hasMfaEnabled(),
            'required' => $user->is_platform && (bool) config('mfa.require_for_platform', false),
        ])->layout('layouts.app', ['title' => 'Keamanan Akun']);
    }
}
