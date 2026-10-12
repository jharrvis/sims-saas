<?php

declare(strict_types=1);

it('menghormati X-Forwarded-Proto dari proxy untuk scheme URL', function (): void {
    // Simulasi request via Cloudflare (TLS terminasi di edge).
    $this->get('/login', ['X-Forwarded-Proto' => 'https']);

    expect(request()->getScheme())->toBe('https')
        ->and(route('dashboard'))->toStartWith('https://');
});

it('tanpa header proxy, scheme mengikuti request', function (): void {
    $this->get('/login');

    expect(request()->getScheme())->toBe('http');
});
