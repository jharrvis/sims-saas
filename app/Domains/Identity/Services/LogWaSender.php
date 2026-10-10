<?php

declare(strict_types=1);

namespace App\Domains\Identity\Services;

use App\Domains\Identity\Contracts\WaSender;
use Illuminate\Support\Facades\Log;

/**
 * Implementasi dev: tidak benar-benar mengirim WA, hanya mencatat ke log.
 * Provider produksi (Meta Cloud API) didaftarkan di AppServiceProvider
 * berdasarkan config saat APP_ENV=production (Dok 11).
 */
final class LogWaSender implements WaSender
{
    public function sendOtp(string $phone, string $code, string $schoolName): bool
    {
        Log::info('[WA-OTP] Kode masuk SIMS {sekolah}: {kode}. Berlaku 5 menit.', [
            'sekolah' => $schoolName,
            'kode' => $code,
            'phone' => $phone,
        ]);

        return true;
    }

    public function sendText(string $phone, string $message): bool
    {
        Log::info('[WA-TEXT] ke {phone}: {message}', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return true;
    }
}
