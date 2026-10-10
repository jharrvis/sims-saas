<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\TenantDomain;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolusi tenant dari domain — dijalankan SEBELUM auth (P0-02, ADR-0002).
 *
 * Aturan:
 * 1. Hanya domain yang terverifikasi DAN aktif yang boleh me-resolve tenant.
 * 2. Setelah auth, user non-platform WAJIB punya membership aktif pada tenant
 *    hasil resolusi (jika tidak → 403 BUKAN_ANGGOTA_TENANT).
 * 3. Operator platform (is_platform) berjalan TANPA tenant; pilihan tenant via
 *    switcher dicatat di audit_logs.
 * 4. Tenant suspended/cancelled: tulis diblokir (402), kecuali route billing.*
 */
final class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app(TenantContext::class);
        $school = null;

        // 1. Resolusi utama via domain — HANYA yang terverifikasi & aktif
        $domain = TenantDomain::query()
            ->where('domain', $request->getHost())
            ->where('terverifikasi', true)
            ->where('aktif', true)
            ->first();

        if ($domain) {
            $school = $domain->school;
        }

        // 2. Fallback: membership aktif user (akses via domain pusat app.sims.id)
        if (! $school && $request->user()) {
            $membership = $request->user()->activeMemberships()->latest()->first();
            $school = $membership?->school;
        }

        // 3. Validasi membership SETELAH auth (P0-02)
        if ($school && $request->user() && ! $request->user()->is_platform) {
            abort_unless(
                $request->user()->activeMemberships()->where('school_id', $school->id)->exists(),
                403,
                'BUKAN_ANGGOTA_TENANT'
            );
        }

        if ($school) {
            // Guard langganan: tenant suspended/cancelled tidak bisa tulis data
            if (in_array($school->status_langganan, ['suspended', 'cancelled'], true)
                && ! $request->routeIs('billing.*')) {
                abort(402, 'Langganan tidak aktif. Selesaikan pembayaran untuk melanjutkan.');
            }

            $tenant->set($school);
        }

        return $next($request);
    }
}
