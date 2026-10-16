<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 2FA (TOTP) bersifat OPSIONAL / opt-in — default nonaktif untuk semua pengguna.
 *
 * Aturan:
 * - Bila user TIDAK mewajibkan 2FA → dilewatkan (login cukup WA OTP).
 * - Bila user sudah mengaktifkan 2FA sendiri → sesi baru wajib diverifikasi
 *   lewat mfa.verify (termasuk login ulang via cookie remember-me).
 * - Operator platform hanya dipaksa bila config `mfa.require_for_platform`
 *   (env MFA_REQUIRED_FOR_PLATFORM) aktif; tanpa flag → tidak dipaksa setup.
 */
class RequireMfa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        // Default: 2FA nonaktif → langsung lanjut.
        if (! $user->mfaRequired()) {
            return $next($request);
        }

        // Wajib 2FA tetapi belum diaktifkan (mis. platform + flag aktif)
        // → arahkan ke halaman setup.
        if (! $user->hasMfaEnabled()) {
            if ($request->routeIs('2fa.setup')) {
                return $next($request);
            }

            return redirect()->route('2fa.setup');
        }

        // 2FA aktif tetapi sesi ini belum terverifikasi → minta verifikasi.
        if (! $request->session()->get('mfa_verified_'.$user->id, false)) {
            return redirect()->route('mfa.verify');
        }

        return $next($request);
    }
}
