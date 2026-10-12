<?php

declare(strict_types=1);

namespace App\Domains\Identity\Services;

use App\Domains\Identity\Contracts\WaSender;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pengirim WhatsApp via Fonnte (keputusan pemilik produk 12 Okt 2026).
 * API: POST {base_url}/send, header Authorization: {token},
 * field form: target, message.
 */
final class FonnteWaSender implements WaSender
{
    public function sendOtp(string $phone, string $code, string $schoolName): bool
    {
        return $this->sendText(
            $phone,
            "Kode OTP SIMS {$schoolName}: {$code}. Berlaku 5 menit. Jangan bagikan ke siapa pun."
        );
    }

    public function sendText(string $phone, string $message): bool
    {
        $token = (string) config('wa.fonnte.token');

        if ($token === '') {
            Log::warning('fonnte: FONNTE_TOKEN kosong, pesan tidak dikirim.', ['phone' => $phone]);

            return false;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Authorization' => $token])
                ->asForm()
                ->post(rtrim((string) config('wa.fonnte.base_url'), '/').'/send', [
                    'target' => $phone,
                    'message' => $message,
                ]);

            $ok = $response->successful() && (bool) $response->json('status', false);

            if (! $ok) {
                Log::warning('fonnte: pengiriman gagal.', [
                    'phone' => $phone,
                    'http' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $ok;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
