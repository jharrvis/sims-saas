<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menyetel konteks team Spatie dari tenant aktif (teams = school_id).
 * Dijalankan setelah ResolveTenant.
 */
final class SetSpatieTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        // Spatie teams mewajibkan team_id non-null pada pivot; operator
        // platform (tanpa tenant) memakai sentinel 0.
        $schoolId = app(TenantContext::class)->schoolId();

        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId ?? 0);

        return $next($request);
    }
}
