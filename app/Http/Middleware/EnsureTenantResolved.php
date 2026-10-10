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
 * auth:sanctum berjalan di route middleware — SETELAH ResolveTenant global —
 * sehingga fallback membership di ResolveTenant belum sempat melihat user.
 * Middleware ini (dipasang setelah auth:sanctum) menjalankan ulang resolusi
 * bila tenant masih kosong. Idempoten; tidak mengubah perilaku request yang
 * tenant-nya sudah ter-resolve (web session / actingAs).
 */
final class EnsureTenantResolved
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app(TenantContext::class)->schoolId() === null && $request->user() !== null) {
            app(ResolveTenant::class)->handle($request, fn () => response()->noContent());

            app(PermissionRegistrar::class)
                ->setPermissionsTeamId(app(TenantContext::class)->schoolId());
        }

        return $next($request);
    }
}
