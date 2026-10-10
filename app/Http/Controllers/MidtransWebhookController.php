<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Billing\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Webhook Midtrans (HTTP notification). Tanpa auth — keamanan via
 * verifikasi signature_key SHA512 di MidtransService.
 */
class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans): JsonResponse
    {
        $payload = $request->all();

        if (! $midtrans->verifySignature($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'Signature tidak valid.',
                'error_code' => 'INVALID_SIGNATURE',
            ], 403);
        }

        $midtrans->handleNotification($payload);

        return response()->json(['success' => true]);
    }
}
