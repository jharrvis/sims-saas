<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <title>Offline — SIMS</title>
    @vite(['resources/css/pwa.css', 'resources/js/pwa.js'])
</head>
<body class="pwa-body" data-pwa-offline-page>
<main class="offline-page">
    <span class="offline-page-icon"><x-icon name="wifi-off" class="h-9 w-9" /></span>
    <span class="overline">MODE OFFLINE</span>
    <h1>Tidak ada koneksi</h1>
    <p>Periksa koneksi internet Anda. SIMS akan mencoba kembali secara otomatis saat perangkat online.</p>
    <button type="button" class="sims-btn sims-btn-primary sims-btn-lg" data-pwa-retry>
        <x-icon name="refresh-cw" class="h-4 w-4" /> Coba lagi
    </button>
</main>
</body>
</html>
