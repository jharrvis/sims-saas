<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\QrLoginCard;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batas sesi QR login (Dok 09 §3.3): sesi perangkat bersama selalu pendek
 * (8 jam, tanpa remember-me). Sesi kedaluwarsa atau kartunya dicabut →
 * logout paksa dan kembali ke login PWA.
 */
class QrSessionLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('qr_limited', false)) {
            return $next($request);
        }

        $valid = true;

        // Kedaluwarsa?
        if (now()->timestamp > (int) $request->session()->get('qr_expires_at', 0)) {
            $valid = false;
        }

        // Kartu dicabut setelah sesi dibuat?
        if ($valid && ($cardId = $request->session()->get('qr_card_id'))) {
            $card = QrLoginCard::find($cardId);

            if ($card === null || ! $card->isValid()) {
                $valid = false;
            } else {
                /** @var Carbon|null $revokedAt */
                $revokedAt = $card->revoked_at;

                if ($revokedAt !== null
                    && (int) $request->session()->get('qr_redeemed_at', 0) < $revokedAt->timestamp
                ) {
                    $valid = false;
                }
            }
        }

        if (! $valid) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('pwa.login')
                ->with('error', 'Sesi QR telah berakhir. Pindai ulang kartu QR Anda.');
        }

        return $next($request);
    }
}
