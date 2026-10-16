<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | 2FA wajib untuk operator platform
    |--------------------------------------------------------------------------
    |
    | Secara default 2FA (TOTP) bersifat OPSIONAL / opt-in untuk SEMUA pengguna,
    | termasuk operator platform. Set MFA_REQUIRED_FOR_PLATFORM=true untuk
    | memaksa akun `is_platform` mengaktifkan 2FA sebelum bisa mengakses
    | area /platform.
    |
    | Catatan keamanan: akun platform paling berkuasa (bisa impersonate admin
    | sekolah & melihat data lintas tenant). Disarankan mengaktifkan flag ini
    | di produksi.
    |
    */

    'require_for_platform' => env('MFA_REQUIRED_FOR_PLATFORM', false),

];
