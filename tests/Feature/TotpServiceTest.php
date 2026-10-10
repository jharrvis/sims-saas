<?php

declare(strict_types=1);

use App\Domains\Identity\Services\TotpService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;

function buatUser(string $username, bool $platform = false): User
{
    return User::create([
        'username' => $username,
        'name' => 'Test '.$username,
        'email' => $username.'@test.id',
        'phone' => '628'.random_int(1000000000, 1999999999),
        'password' => 'password',
        'is_platform' => $platform,
    ]);
}

it('setup TOTP menghasilkan secret + 8 recovery code', function (): void {
    $user = buatUser('mfa1');
    $totp = app(TotpService::class);

    $setup = $totp->startSetup($user);

    expect($setup['secret'])->not->toBeEmpty()
        ->and($setup['recovery_codes'])->toHaveCount(8)
        ->and($setup['qr_url'])->toContain('otpauth://');

    // Secret tersimpan terenkripsi, bukan plaintext di DB mentah
    $raw = DB::table('mfa_secrets')
        ->where('user_id', $user->id)->value('secret');
    expect($raw)->not->toBe($setup['secret']);
});

it('konfirmasi setup hanya dengan kode TOTP valid', function (): void {
    $user = buatUser('mfa2');
    $totp = app(TotpService::class);
    $google2fa = app(Google2FA::class);

    $setup = $totp->startSetup($user);

    expect($totp->confirmSetup($user, '000000'))->toBeFalse();

    $valid = $google2fa->getCurrentOtp($setup['secret']);
    expect($totp->confirmSetup($user, $valid))->toBeTrue();
    expect($user->mfaSecret->isConfirmed())->toBeTrue();
});

it('recovery code hanya berlaku satu kali', function (): void {
    $user = buatUser('mfa3');
    $totp = app(TotpService::class);
    $google2fa = app(Google2FA::class);

    $setup = $totp->startSetup($user);
    $totp->confirmSetup($user, $google2fa->getCurrentOtp($setup['secret']));

    $rc = $setup['recovery_codes'][0];

    expect($totp->useRecoveryCode($user, $rc))->toBeTrue();
    // Dipakai kedua kali → ditolak
    expect($totp->useRecoveryCode($user->fresh(), $rc))->toBeFalse();
});
