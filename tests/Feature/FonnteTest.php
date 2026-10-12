<?php

declare(strict_types=1);

use App\Domains\Identity\Services\FonnteWaSender;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config()->set('wa.fonnte.token', 'token-rahasia');
    config()->set('wa.fonnte.base_url', 'https://api.fonnte.com');
});

it('mengirim teks via API Fonnte dengan format yang benar', function (): void {
    Http::fake([
        'api.fonnte.com/send' => Http::response(['status' => true], 200),
    ]);

    $ok = app(FonnteWaSender::class)->sendText('6281234567890', 'Halo, ini tes.');

    expect($ok)->toBeTrue();

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://api.fonnte.com/send'
            && $request->header('Authorization')[0] === 'token-rahasia'
            && $request['target'] === '6281234567890'
            && $request['message'] === 'Halo, ini tes.';
    });
});

it('mengembalikan false bila Fonnte menolak', function (): void {
    Http::fake([
        'api.fonnte.com/send' => Http::response(['status' => false, 'reason' => 'invalid token'], 200),
    ]);

    expect(app(FonnteWaSender::class)->sendText('6281234567890', 'Halo'))->toBeFalse();
});

it('mengembalikan false bila token kosong', function (): void {
    config()->set('wa.fonnte.token', '');

    expect(app(FonnteWaSender::class)->sendText('6281234567890', 'Halo'))->toBeFalse();
});

it('sendOtp memakai template kode OTP', function (): void {
    Http::fake([
        'api.fonnte.com/send' => Http::response(['status' => true], 200),
    ]);

    $ok = app(FonnteWaSender::class)->sendOtp('6281234567890', '123456', 'SDN Contoh');

    expect($ok)->toBeTrue();

    Http::assertSent(function ($request): bool {
        return str_contains((string) $request['message'], '123456')
            && str_contains((string) $request['message'], 'SDN Contoh');
    });
});
