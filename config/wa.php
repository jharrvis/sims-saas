<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp gateway
    |--------------------------------------------------------------------------
    | driver: `log` (dev, hanya catat) atau `fonnte` (produksi).
    | Keputusan pemilik produk 12 Okt 2026: Fonnte.
    */
    'driver' => env('WA_DRIVER', 'log'),

    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
        // Endpoint dapat dioverride bila Fonnte merilis versi API baru.
        'base_url' => env('FONNTE_BASE_URL', 'https://api.fonnte.com'),
    ],
];
