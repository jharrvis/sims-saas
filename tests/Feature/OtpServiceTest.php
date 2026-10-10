<?php

declare(strict_types=1);

use App\Domains\Identity\Contracts\WaSender;
use App\Domains\Identity\Services\OtpRateLimited;
use App\Domains\Identity\Services\OtpService;
use App\Models\OtpCode;

class FakeWaSender implements WaSender
{
    public static ?string $lastCode = null;

    public function sendOtp(string $phone, string $code, string $schoolName): bool
    {
        self::$lastCode = $code;

        return true;
    }
}

beforeEach(function (): void {
    $this->app->bind(WaSender::class, FakeWaSender::class);
    FakeWaSender::$lastCode = null;
});

it('mengirim dan memverifikasi OTP dengan benar (satu kali pakai)', function (): void {
    $otp = app(OtpService::class);

    $otp->request('081234567890', 'login', 'Sekolah A');
    $code = FakeWaSender::$lastCode;

    expect($code)->not->toBeNull()
        ->and(strlen($code))->toBe(6);

    expect($otp->verify('081234567890', 'login', $code))->toBeTrue();

    // Kode hangus setelah dipakai
    expect($otp->verify('081234567890', 'login', $code))->toBeFalse();
});

it('menolak kode salah dan mengunci setelah 5x salah', function (): void {
    $otp = app(OtpService::class);

    $otp->request('081234567891', 'login', 'Sekolah A');
    $benar = FakeWaSender::$lastCode;

    for ($i = 0; $i < 5; $i++) {
        expect($otp->verify('081234567891', 'login', '000000'))->toBeFalse();
    }

    // Kode benar pun ditolak setelah 5x salah
    expect($otp->verify('081234567891', 'login', $benar))->toBeFalse();
});

it('menerapkan cooldown 60 detik antar pengiriman', function (): void {
    $otp = app(OtpService::class);

    $otp->request('081234567892', 'login', 'Sekolah A');

    expect(fn () => $otp->request('081234567892', 'login', 'Sekolah A'))
        ->toThrow(OtpRateLimited::class);
});

it('menyimpan kode sebagai hash, bukan plaintext', function (): void {
    $otp = app(OtpService::class);

    $record = $otp->request('081234567893', 'login', 'Sekolah A');
    $code = FakeWaSender::$lastCode;

    $stored = OtpCode::find($record->id);

    expect($stored->code_hash)->not->toBe($code)
        ->and(strlen($stored->code_hash))->toBeGreaterThan(20);
});
