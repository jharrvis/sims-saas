<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans — payment gateway langganan SaaS
    |--------------------------------------------------------------------------
    | Keputusan pemilik produk 10 Okt 2026: Midtrans (bukan Xendit).
    | Isi dari environment; jangan commit key asli.
    */
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Snap: redirect (bukan popup) agar konsisten di semua browser.
    'finish_url' => env('MIDTRANS_FINISH_URL', '/langganan'),
];
