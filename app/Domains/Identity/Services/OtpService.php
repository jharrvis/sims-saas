<?php

declare(strict_types=1);

namespace App\Domains\Identity\Services;

use App\Domains\Identity\Contracts\WaSender;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;

/**
 * Layanan OTP WhatsApp (Dok 09 §3.1).
 *
 * Parameter keamanan:
 * - 6 digit acak kriptografis, disimpan sebagai hash, expiry 5 menit
 * - Cooldown kirim ulang 60 detik
 * - Rate limit kirim: 3x/15 menit, 5x/hari per nomor
 * - Rate limit verifikasi: 5x salah per kode → kunci 15 menit
 * - Kode hangus setelah 1x pakai benar
 */
final class OtpService
{
    public function __construct(private readonly WaSender $sender) {}

    /**
     * Minta kode OTP baru. Melempar OtpRateLimited bila kena rate limit.
     */
    public function request(string $phone, string $keperluan, string $schoolName): OtpCode
    {
        $phone = $this->normalize($phone);
        $now = now();

        // Cooldown 60 detik
        $terakhir = OtpCode::where('phone', $phone)
            ->where('keperluan', $keperluan)
            ->latest()
            ->first();

        if ($terakhir && $terakhir->created_at->diffInSeconds($now) < 60) {
            throw new OtpRateLimited('Tunggu 60 detik sebelum meminta kode lagi.');
        }

        // 3x per 15 menit
        $dalam15Menit = OtpCode::where('phone', $phone)
            ->where('created_at', '>=', $now->copy()->subMinutes(15))
            ->count();

        if ($dalam15Menit >= 3) {
            throw new OtpRateLimited('Terlalu banyak permintaan. Coba lagi dalam 15 menit.');
        }

        // 5x per hari
        $hariIni = OtpCode::where('phone', $phone)
            ->where('created_at', '>=', $now->copy()->startOfDay())
            ->count();

        if ($hariIni >= 5) {
            throw new OtpRateLimited('Batas harian tercapai. Coba lagi besok.');
        }

        $code = (string) random_int(100000, 999999);

        $otp = OtpCode::create([
            'phone' => $phone,
            'code_hash' => Hash::make($code),
            'keperluan' => $keperluan,
            'expires_at' => $now->copy()->addMinutes(5),
        ]);

        $this->sender->sendOtp($phone, $code, $schoolName);

        return $otp;
    }

    /**
     * Verifikasi kode. True bila benar (kode langsung dihanguskan).
     */
    public function verify(string $phone, string $keperluan, string $code): bool
    {
        $phone = $this->normalize($phone);

        $otp = OtpCode::where('phone', $phone)
            ->where('keperluan', $keperluan)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otp || $otp->isExpired()) {
            return false;
        }

        // Kunci 15 menit setelah 5x salah
        if ($otp->attempts >= 5) {
            return false;
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            return false;
        }

        // Hanguskan: satu kali pakai
        $otp->update(['used_at' => now()]);

        return true;
    }

    private function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        // 08xx -> 628xx
        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }
}
