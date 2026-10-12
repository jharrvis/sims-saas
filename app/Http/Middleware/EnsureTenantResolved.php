<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Melengkapi ResolveTenant untuk request API.
 *
 * Dipasang di AWAL grup api (sebelum SubstituteBindings) agar route model
 * binding sudah berjalan dalam konteks tenant yang benar, dan diulang di
 * route middleware setelah auth:sanctum (idempoten).
 *
 * Urutan masalah yang dipecahkan: auth:sanctum adalah route middleware
 * (setelah binding), sehingga user Bearer tidak tersedia saat binding.
 * Middleware ini mencoba autentikasi sanctum secara non-enforcing lebih
 * awal — tanpa menggagalkan request tanpa token (itu tugas auth:sanctum).
 */
final class EnsureTenantResolved
{
    public function handle(Request $request, Closure $next): Response
    {
        // Autentikasi Bearer <redacted> non-enforcing sebelum binding: tanpa ini,
        // $request->user() null saat ResolveTenant berjalan di grup api.
        if ($request->bearerToken() && ! auth()->check()) {
            $sanctumUser = auth('sanctum')->user();

            if ($sanctumUser) {
                auth()->setUser($sanctumUser);
            }
        }

        // Selalu hitung ulang dari request saat ini (domain → user),
        // menimpa konteks apa pun yang terlanjur ter-set.
        app(ResolveTenant::class)->handle($request, fn () => response()->noContent());

        // Selalu sinkronkan team Spatie dari tenant aktif (paritas dengan
        // SetSpatieTeam di grup web; sentinel 0 bila tanpa tenant).
        app(PermissionRegistrar::class)
            ->setPermissionsTeamId(app(TenantContext::class)->schoolId() ?? 0);

        return $next($request);
    }
}
