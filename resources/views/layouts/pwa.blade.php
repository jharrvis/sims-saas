@php
    $title = $title ?? 'SIMS';
    $hideHeader = $hideHeader ?? false;
    $hideNav = $hideNav ?? false;
    $schoolName = $schoolName ?? null;
    $children = $children ?? collect();
    $activeStudentId = $activeStudentId ?? null;
    $showChildSwitcher = $showChildSwitcher ?? false;
    $isHome = request()->routeIs('pwa.home');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SIMS">
    <title>{{ $title }} — SIMS</title>
    <link rel="manifest" href="/pwa-assets/manifest.webmanifest">
    <link rel="icon" type="image/png" sizes="192x192" href="/pwa-assets/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/pwa-assets/icons/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/pwa.css', 'resources/js/app.js', 'resources/js/pwa.js'])
    @livewireStyles
</head>
<body class="pwa-body" data-pwa-shell data-service-worker-url="{{ route('pwa.service-worker') }}">
<div class="offline-banner" data-pwa-offline role="status" hidden>
    <x-icon name="wifi-off" class="h-4 w-4" />
    <span>Anda sedang offline. Data baru dan perubahan memerlukan koneksi internet.</span>
</div>

<div class="phone-shell pwa-shell">
    @if (! $hideHeader)
        <header class="app-header">
            @if ($isHome)
                <a href="{{ route('pwa.home') }}" class="brand">
                    <span class="brand-mark"><x-icon name="graduation-cap" class="h-6 w-6" /></span>
                    <span>
                        <strong>SIMS</strong>
                        <small>{{ $schoolName ?: 'Portal sekolah' }}</small>
                    </span>
                </a>
            @else
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('pwa.home') }}" class="sims-icon-btn" aria-label="Kembali ke beranda">
                        <x-icon name="arrow-left" class="h-5 w-5" />
                    </a>
                    <div class="min-w-0">
                        <h1 class="truncate text-base font-extrabold tracking-tight text-slate-800">{{ $title }}</h1>
                        @if ($schoolName)
                            <p class="truncate text-[10px] font-medium text-slate-400">{{ $schoolName }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="header-actions">
                <span class="network-badge" data-network-status>Online</span>
                <button type="button" class="sims-icon-btn pwa-install-button" data-pwa-install hidden aria-label="Instal aplikasi SIMS">
                    <x-icon name="download" class="h-5 w-5" />
                </button>
                <a href="{{ route('pwa.profil') }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-xs font-extrabold text-primary" aria-label="Buka profil">
                    {{ mb_substr(auth()->user()?->name ?? 'S', 0, 1) }}
                </a>
            </div>
        </header>
    @endif

    <main class="pwa-main">
        {{ $slot }}
    </main>

    @if (! $hideNav)
        <nav class="bottom-nav five" aria-label="Navigasi utama PWA">
            @if ($showChildSwitcher)
                <a href="{{ route('pwa.home') }}" class="{{ request()->routeIs('pwa.home') ? 'active' : '' }}" @if(request()->routeIs('pwa.home')) aria-current="page" @endif>
                    <x-icon name="house" class="h-5 w-5" />
                    <span>Beranda</span>
                </a>
                <a href="{{ route('pwa.home') }}#anak" class="{{ request()->routeIs('pwa.home') ? '' : '' }}">
                    <x-icon name="users" class="h-5 w-5" />
                    <span>Anak</span>
                </a>
                <a href="{{ route('pwa.tagihan') }}" class="{{ request()->routeIs('pwa.tagihan') ? 'active' : '' }}" @if(request()->routeIs('pwa.tagihan')) aria-current="page" @endif>
                    <x-icon name="wallet" class="h-5 w-5" />
                    <span>Keuangan</span>
                </a>
                <a href="{{ route('pwa.pengumuman') }}" class="{{ request()->routeIs('pwa.pengumuman*') ? 'active' : '' }}" @if(request()->routeIs('pwa.pengumuman*')) aria-current="page" @endif>
                    <x-icon name="message-square" class="h-5 w-5" />
                    <span>Pesan</span>
                </a>
                <a href="{{ route('pwa.profil') }}" class="{{ request()->routeIs('pwa.profil') ? 'active' : '' }}" @if(request()->routeIs('pwa.profil')) aria-current="page" @endif>
                    <x-icon name="user" class="h-5 w-5" />
                    <span>Profil</span>
                </a>
            @else
                <a href="{{ route('pwa.home') }}" class="{{ request()->routeIs('pwa.home') ? 'active' : '' }}" @if(request()->routeIs('pwa.home')) aria-current="page" @endif>
                    <x-icon name="house" class="h-5 w-5" />
                    <span>Beranda</span>
                </a>
                <a href="{{ route('pwa.jadwal') }}" class="{{ request()->routeIs('pwa.jadwal') ? 'active' : '' }}" @if(request()->routeIs('pwa.jadwal')) aria-current="page" @endif>
                    <x-icon name="calendar-days" class="h-5 w-5" />
                    <span>Jadwal</span>
                </a>
                <a href="{{ route('pwa.nilai') }}" class="{{ request()->routeIs('pwa.nilai') ? 'active' : '' }}" @if(request()->routeIs('pwa.nilai')) aria-current="page" @endif>
                    <x-icon name="chart-column" class="h-5 w-5" />
                    <span>Nilai</span>
                </a>
                <a href="{{ route('pwa.tagihan') }}" class="{{ request()->routeIs('pwa.tagihan') ? 'active' : '' }}" @if(request()->routeIs('pwa.tagihan')) aria-current="page" @endif>
                    <x-icon name="wallet" class="h-5 w-5" />
                    <span>Keuangan</span>
                </a>
                <a href="{{ route('pwa.profil') }}" class="{{ request()->routeIs('pwa.profil') ? 'active' : '' }}" @if(request()->routeIs('pwa.profil')) aria-current="page" @endif>
                    <x-icon name="user" class="h-5 w-5" />
                    <span>Profil</span>
                </a>
            @endif
        </nav>
    @endif
</div>

<aside class="pwa-update-banner" data-pwa-update role="status" hidden>
    <span class="row-icon blue"><x-icon name="refresh-cw" class="h-5 w-5" /></span>
    <span>
        <strong>Pembaruan SIMS tersedia</strong>
        <small>Perbarui sekarang untuk menggunakan versi terbaru.</small>
    </span>
    <div class="pwa-update-actions">
        <button type="button" data-pwa-update-dismiss>Nanti</button>
        <button type="button" class="primary" data-pwa-update-action>Perbarui</button>
    </div>
</aside>

<div class="sims-toast-tray" data-sims-toasts aria-live="polite"></div>

@livewireScripts
</body>
</html>
