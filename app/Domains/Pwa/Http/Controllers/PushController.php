<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Http\Controllers;

use App\Domains\Pwa\Services\PushService;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Web Push subscription (Dok 03 §5.3/5.4): dipakai PWA (sesi web)
 * maupun klien API (Bearer token) — format respons menyesuaikan.
 */
class PushController extends Controller
{
    public function vapidKey(PushService $push): JsonResponse
    {
        return response()->json(['key' => $push->publicKey()]);
    }

    public function subscribe(Request $request, PushService $push): JsonResponse
    {
        /** @var array{endpoint: string, keys: array{p256dh: string, auth: string}, device_id?: int} $data */
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:500', 'url'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'device_id' => ['nullable', 'integer'],
        ]);

        if (! $push->enabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan push belum dikonfigurasi.',
            ], 503);
        }

        $sub = $push->subscribe(
            $request->user(),
            app(TenantContext::class)->requireSchoolId(),
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
            $request->userAgent(),
        );

        if ($request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Perangkat berhasil didaftarkan untuk notifikasi push.',
                'data' => ['subscription_id' => $sub->id, 'endpoint_terdaftar' => true],
                'meta' => null,
            ], $sub->wasRecentlyCreated ? 201 : 200);
        }

        return response()->json(['ok' => true], $sub->wasRecentlyCreated ? 201 : 200);
    }

    public function unsubscribe(Request $request, PushService $push): JsonResponse
    {
        /** @var array{endpoint: string} $data */
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
        ]);

        $deleted = $push->unsubscribe($request->user(), $data['endpoint']);

        if ($request->is('api/*')) {
            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'code' => 'SUBSCRIPTION_TIDAK_DITEMUKAN',
                    'message' => 'Push endpoint tidak terdaftar.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi push untuk perangkat ini dimatikan.',
            ]);
        }

        return response()->json(['ok' => $deleted]);
    }
}
