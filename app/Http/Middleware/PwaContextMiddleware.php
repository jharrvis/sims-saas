<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domains\Pwa\Services\PwaContext;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Konteks PWA: memastikan akun terhubung ke siswa/wali, lalu mengunci
 * tenant ke sekolah milik siswa aktif (mendukung multi-anak lintas sekolah).
 */
class PwaContextMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        /** @var PwaContext $pwa */
        $pwa = app(PwaContext::class);
        $pwa->setUser($user);

        if (! $pwa->isLinked()) {
            if (! $request->routeIs('pwa.unlinked')) {
                return redirect()->route('pwa.unlinked');
            }

            return $next($request);
        }

        $student = $pwa->activeStudent();

        if ($student !== null) {
            app(TenantContext::class)->set($student->school);
        }

        return $next($request);
    }
}
