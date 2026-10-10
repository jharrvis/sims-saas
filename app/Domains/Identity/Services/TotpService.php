<?php

declare(strict_types=1);

namespace App\Domains\Identity\Services;

use App\Models\MfaSecret;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * MFA TOTP — wajib untuk peran platform (FR-904, Dok 09).
 */
final class TotpService
{
    public function __construct(private readonly Google2FA $google2fa) {}

    /**
     * Mulai setup: buat secret baru + 8 recovery code (sesuai design setup-2fa).
     *
     * @return array{secret: string, qr_url: string, recovery_codes: array<int,string>}
     */
    public function startSetup(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)))
            ->all();

        MfaSecret::updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret' => $secret,
                'confirmed_at' => null,
                'recovery_codes' => array_map(fn (string $c) => Hash::make($c), $recoveryCodes),
            ]
        );

        return [
            'secret' => $secret,
            'qr_url' => $this->google2fa->getQRCodeUrl('SIMS', $user->email ?? $user->username, $secret),
            'recovery_codes' => $recoveryCodes, // plaintext HANYA ditampilkan sekali
        ];
    }

    /**
     * Konfirmasi setup dengan kode dari authenticator.
     */
    public function confirmSetup(User $user, string $code): bool
    {
        $mfa = $user->mfaSecret;

        if (! $mfa || $mfa->isConfirmed()) {
            return false;
        }

        if (! $this->google2fa->verifyKey($mfa->secret, $code)) {
            return false;
        }

        $mfa->update(['confirmed_at' => now()]);

        return true;
    }

    /**
     * Verifikasi kode TOTP saat login.
     */
    public function verify(User $user, string $code): bool
    {
        $mfa = $user->mfaSecret;

        if (! $mfa || ! $mfa->isConfirmed()) {
            return false;
        }

        return $this->google2fa->verifyKey($mfa->secret, $code);
    }

    /**
     * Pakai recovery code (sekali pakai — dihapus setelah dipakai).
     */
    public function useRecoveryCode(User $user, string $code): bool
    {
        $mfa = $user->mfaSecret;

        if (! $mfa || ! $mfa->isConfirmed()) {
            return false;
        }

        /** @var array<int,string> $codes */
        $codes = $mfa->recovery_codes ?? [];

        foreach ($codes as $i => $hash) {
            if (Hash::check($code, $hash)) {
                unset($codes[$i]);
                $mfa->update(['recovery_codes' => array_values($codes)]);

                return true;
            }
        }

        return false;
    }
}
