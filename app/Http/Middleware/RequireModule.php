<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Module;
use App\Models\TenantModule;
use App\Scopes\SchoolScope;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guard feature flag modul per tenant (Dok 02 §8.6, Dok 03 §7).
 * Modul nonaktif → 403 dengan kode MODULE_DISABLED.
 * Modul kategori `inti` selalu aktif dan tidak butuh flag.
 */
final class RequireModule
{
    public function handle(Request $request, Closure $next, string $kode): Response
    {
        $tenant = app(TenantContext::class);
        $schoolId = $tenant->schoolId();

        // Tanpa konteks tenant (mis. operator platform) → lewatkan;
        // otorisasi platform diatur guard/policy tersendiri.
        if ($schoolId === null) {
            return $next($request);
        }

        $module = Module::where('kode', $kode)->where('aktif', true)->first();

        if (! $module) {
            abort(404, 'Modul tidak dikenal.');
        }

        // Modul inti selalu aktif
        if ($module->isInti()) {
            return $next($request);
        }

        $flag = TenantModule::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', $schoolId)
            ->where('module_id', $module->id)
            ->where('aktif', true)
            ->exists();

        if (! $flag) {
            abort(response()->json([
                'message' => 'Modul tidak aktif untuk sekolah ini.',
                'code' => 'MODULE_DISABLED',
            ], 403));
        }

        return $next($request);
    }
}
