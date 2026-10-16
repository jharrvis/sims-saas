<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Domains\Identity\Services\TotpService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Setup 2FA TOTP — OPSIONAL (opt-in). Tersedia bagi semua pengguna;
 * operator platform dipaksa ke sini hanya bila `mfa.require_for_platform`
 * aktif. Menampilkan QR + 8 kode pemulihan (sekali tampil).
 */
class SetupTwoFactor extends Component
{
    public string $code = '';

    public ?string $error = null;

    public ?string $success = null;

    /** @var array<int,string>|null */
    public ?array $recoveryCodes = null;

    public string $qrSvg = '';

    public string $secret = '';

    public function mount(TotpService $totp): void
    {
        $user = auth()->user();

        if ($user->mfaSecret?->isConfirmed()) {
            $this->success = '2FA sudah aktif di akun Anda.';

            return;
        }

        $setup = $totp->startSetup($user);

        $this->secret = $setup['secret'];
        $this->recoveryCodes = $setup['recovery_codes'];
        $this->qrSvg = $this->qrSvg($setup['qr_url']);
    }

    public function confirm(TotpService $totp): void
    {
        $this->validate(['code' => ['required', 'string', 'size:6']]);
        $this->error = null;

        if (! $totp->confirmSetup(auth()->user(), $this->code)) {
            $this->error = 'Kode salah. Periksa aplikasi autentikator Anda.';

            return;
        }

        $this->success = '2FA berhasil diaktifkan. Simpan kode pemulihan Anda di tempat aman.';
        $this->qrSvg = '';
        $this->secret = '';

        // Tandai sesi ini sudah terverifikasi agar tidak langsung diminta
        // verifikasi ulang oleh middleware `mfa` pada request berikutnya.
        session(['mfa_verified_'.auth()->id() => true]);
    }

    private function qrSvg(string $content): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(176),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($content);
    }

    public function render(): View
    {
        return view('livewire.auth.setup-two-factor')->layout('layouts.app', ['title' => 'Autentikasi Dua Faktor']);
    }
}
