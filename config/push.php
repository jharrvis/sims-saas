<?php

declare(strict_types=1);

return [
    // Matikan total via PUSH_ENABLED=false (mis. environment tanpa VAPID).
    'enabled' => env('PUSH_ENABLED', true),

    // Diaktifkan di test hanya bila eksplisit (agar test lain tak mengirim).
    'enabled_in_tests' => env('PUSH_ENABLED_IN_TESTS', false),

    'vapid' => [
        'subject' => env('PUSH_VAPID_SUBJECT', 'mailto:admin@sims.id'),
        'public_key' => env('PUSH_VAPID_PUBLIC_KEY'),
        'private_key' => env('PUSH_VAPID_PRIVATE_KEY'),
    ],
];
