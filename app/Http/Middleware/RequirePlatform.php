<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gerbang area operator platform: hanya akun `is_platform`.
 * Berjalan TANPA tenant (ResolveTenant mengecualikan akun platform).
 * Parameter opsional: permission guard `platform`, mis. `platform:platform.tenant.view`.
 */
class RequirePlatform
{
    public function handle(Request $request, Closure $next, string $permission = ''): Response
    {
        $user = $request->user();

        if ($user === null || ! (bool) $user->is_platform) {
            abort(403, 'Khusus operator platform.');
        }

        if ($permission !== '' && ! $user->hasPermissionTo($permission, 'platform')) {
            abort(403, 'Tidak memiliki izin platform.');
        }

        return $next($request);
    }
}
