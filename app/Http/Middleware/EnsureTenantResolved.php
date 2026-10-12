<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Melengkapi ResolveTenant untuk request API bertoken (Bearer).
 *
 * auth:sanctum berjalan di route middleware — SETELAH middleware grup web —
 * sehingga fallback membership di ResolveTenant (grup web) belum sempat
 * melihat user API. Middleware ini (dipasang setelah auth:sanctum)
 * menjalankan ulang resolusi dari request saat ini. Idempoten: di produksi
 * TenantContext selalu kosong di awal request sehingga hasilnya sama
 * seperti resolusi tunggal.
 */
final class EnsureTenantResolved
{
    public function handle(Request $request, Closure $next): Response
    {
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
