@php
    $title = $title ?? 'SIMS';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <title>{{ $title }} — SIMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/auth.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="auth-page">
<div class="auth-guest-shell">
    <header class="auth-guest-header">
        <a href="{{ route('login') }}" class="auth-brand">
            <span class="auth-brand-mark"><x-icon name="graduation-cap" class="h-5 w-5" /></span>
            <span>
                <strong>SIMS</strong>
                <small>Sistem Informasi Manajemen Sekolah</small>
            </span>
        </a>
        <a href="{{ route('login') }}" class="sims-btn sims-btn-soft sims-btn-sm">Kembali ke login</a>
    </header>

    <main class="auth-guest-main">
        {!! $slot !!}
    </main>

    <footer class="auth-guest-footer">
        <span>&copy; {{ now()->year }} SIMS</span>
        <span>Aman · Terhubung · Terukur</span>
    </footer>
</div>
@livewireScripts
</body>
</html>
