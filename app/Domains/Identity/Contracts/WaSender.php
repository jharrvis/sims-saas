<?php

declare(strict_types=1);

namespace App\Domains\Identity\Contracts;

/**
 * Abstraksi pengirim WhatsApp.
 * Implementasi produksi: Fonnte (keputusan 12 Okt 2026);
 * implementasi dev (LogWaSender) hanya mencatat ke log.
 */
interface WaSender
{
    /**
     * Kirim kode OTP via template authentication Meta.
     *
     * @param  string  $phone  Nomor tujuan format internasional (628xx)
     * @param  string  $code  Kode OTP plaintext (hanya tersedia saat kirim)
     * @param  string  $schoolName  Nama sekolah untuk template
     */
    public function sendOtp(string $phone, string $code, string $schoolName): bool;

    /**
     * Kirim pesan teks bebas (notifikasi sistem, pengingat tagihan).
     *
     * @param  string  $phone  Nomor tujuan format internasional (628xx)
     */
    public function sendText(string $phone, string $message): bool;
}
