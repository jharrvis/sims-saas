<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FR-904: MFA wajib untuk peran platform (web).
 *
 * - Pengguna platform yang belum mengonfirmasi MFA → dipaksa ke 2fa.setup.
 * - Pengguna platform yang MFA-nya sudah aktif tetapi sesi belum terverifikasi
 *   (mis. login ulang via cookie remember-me) → dipaksa ke mfa.verify.
 */
class RequireMfa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! (bool) $user->is_platform) {
            return $next($request);
        }

        // Belum setup/konfirmasi MFA → wajib setup dulu.
        if (! $user->mfaSecret?->isConfirmed()) {
            if ($request->routeIs('2fa.setup')) {
                return $next($request);
            }

            return redirect()->route('2fa.setup');
        }

        // MFA aktif tetapi sesi ini belum terverifikasi → wajib verifikasi.
        if (! $request->session()->get('mfa_verified_'.$user->id, false)) {
            return redirect()->route('mfa.verify');
        }

        return $next($request);
    }
}
