<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3c50e0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SIMS">
    <title>{{ $title ?? 'SIMS' }} — SIMS</title>
    <link rel="manifest" href="/pwa/manifest.webmanifest">
    <link rel="icon" type="image/png" sizes="192x192" href="/pwa/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/pwa/icons/icon-192.png">
    @vite(['resources/css/pwa.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 font-sans antialiased">
<div id="offline-banner" class="offline-banner fixed top-0 left-0 right-0 z-[100] bg-slate-800 text-white text-center py-2 text-xs font-semibold">
    Mode Offline — Data terakhir tersimpan
</div>

<div class="app-shell flex flex-col">
    {{-- Header halaman dalam (design). Beranda PWA me-render hero header sendiri di view-nya. --}}
    @if (! ($hideHeader ?? false) && ! request()->routeIs('pwa.home'))
        <header class="bg-[#3c50e0] px-5 pt-12 {{ request()->routeIs('pwa.profil') ? 'pb-16 relative overflow-hidden' : 'pb-6' }}">
            @if (request()->routeIs('pwa.profil'))
                <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/5 -mr-10 -mt-10"></div>
            @endif
            <div class="flex items-start justify-between gap-3 relative z-10">
                <div class="min-w-0">
                    <h1 class="text-white text-xl font-extrabold leading-tight">{{ $title ?? 'SIMS' }}</h1>
                    @if (! empty($schoolName))
                        <p class="text-blue-100 text-sm mt-1 truncate">{{ $schoolName }}</p>
                    @endif
                </div>
                @if (! empty($showChildSwitcher) && count($children ?? []) > 1)
                    <form method="POST" action="{{ route('pwa.switch-child') }}" class="shrink-0">
                        @csrf
                        <select name="student_id" onchange="this.form.submit()" aria-label="Pilih anak"
                                class="rounded-lg bg-white/15 border border-white/25 text-white text-sm font-semibold px-2 py-1.5 outline-none">
                            @foreach ($children as $child)
                                <option value="{{ $child->id }}" @selected($child->id === ($activeStudentId ?? null)) class="text-slate-800">
                                    {{ $child->nama }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </header>
    @endif

    <main class="flex-1">
        {{ $slot }}
    </main>

    @if (! ($hideNav ?? false))
        <nav class="sticky bottom-0 z-50 bg-white border-t border-slate-200 bottom-nav">
            <div class="flex justify-around items-center pt-2 pb-1">
                @if (! empty($showChildSwitcher))
                    {{-- Navigasi orang tua (design ortu): Beranda, Anak, Keuangan, Pesan, Profil --}}
                    <a href="{{ route('pwa.home') }}" class="flex flex-col items-center gap-0.5 px-4 {{ request()->routeIs('pwa.home') ? 'text-[#3c50e0]' : 'text-slate-400' }}">
                        <x-icon name="house" class="w-6 h-6" />
                        <span class="text-[10px] font-medium">Beranda</span>
                    </a>
                    <a href="{{ route('pwa.home') }}#anak" class="flex flex-col items-center gap-0.5 px-4 text-slate-400">
                        <x-icon name="users" class="w-6 h-6" />
                        <span class="text-[10px] font-medium">Anak</span>
                    </a>
                    <a href="{{ route('pwa.tagihan') }}" class="flex flex-col items-center gap-0.5 px-4 {{ request()->routeIs('pwa.tagihan') ? 'text-[#3c50e0]' : 'text-slate-400' }}">
                        <x-icon name="wallet" class="w-6 h-6" />
                        <span class="text-[10px] font-medium">Keuangan</span>
                    </a>
                    <a href="{{ route('pwa.pengumuman') }}" class="flex flex-col items-center gap-0.5 px-4 {{ request()->routeIs('pwa.pengumuman*') ? 'text-[#3c50e0]' : 'text-slate-400' }}">
                        <x-icon name="message-square" class="w-6 h-6" />
                        <span class="text-[10px] font-medium">Pesan</span>
                    </a>
                    <a href="{{ route('pwa.profil') }}" class="flex flex-col items-center gap-0.5 px-4 {{ request()->routeIs('pwa.profil') ? 'text-[#3c50e0]' : 'text-slate-400' }}">
                        <x-icon name="user" class="w-6 h-6" />
                        <span class="text-[10px] font-medium">Profil</span>
                    </a>
                @else
                    {{-- Navigasi siswa (design siswa): Beranda, Jadwal, Absensi, Nilai, Profil --}}
                    <a href="{{ route('pwa.home') }}" class="flex flex-col items-center gap-0.5 px-4 {{ request()->routeIs('pwa.home') ? 'text-[#3c50e0]' : 'text-slate-400' }}">
                        <x-icon name="house" class="w-6 h-6" />
                        <span class="text-[10px] font-medium">Beranda</span>
                    </a>
                    <a href="{{ route('pwa.jadwal') }}" class="flex flex-col items-center gap-0.5 px-4 {{ request()->routeIs('pwa.jadwal') ? 'text-[#3c50e0]' : 'text-slate-400' }}">
                        <x-icon name="calendar-days" class="w-6 h-6" />
                        <span class="text-[10px] font-medium">Jadwal</span>
                    </a>
                    <a href="{{ route('pwa.absensi') }}" class="flex flex-col items-center gap-0.5 px-4 {{ request()->routeIs('pwa.absensi') ? 'text-[#3c50e0]' : 'text-slate-400' }}">
                        <x-icon name="square-check-big" class="w-6 h-6" />
                        <span class="text-[10px] font-medium">Absensi</span>
                    </a>
                    <a href="{{ route('pwa.nilai') }}" class="flex flex-col items-center gap-0.5 px-4 {{ request()->routeIs('pwa.nilai') ? 'text-[#3c50e0]' : 'text-slate-400' }}">
                        <x-icon name="chart-column" class="w-6 h-6" />
                        <span class="text-[10px] font-medium">Nilai</span>
                    </a>
                    <a href="{{ route('pwa.profil') }}" class="flex flex-col items-center gap-0.5 px-4 {{ request()->routeIs('pwa.profil') ? 'text-[#3c50e0]' : 'text-slate-400' }}">
                        <x-icon name="user" class="w-6 h-6" />
                        <span class="text-[10px] font-medium">Profil</span>
                    </a>
                @endif
            </div>
        </nav>
    @endif
</div>

@livewireScripts
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/pwa/sw.js').catch(() => {});
        });
    }
    window.addEventListener('offline', () => { document.getElementById('offline-banner').style.display = 'block'; });
    window.addEventListener('online', () => { document.getElementById('offline-banner').style.display = 'none'; });
</script>
</body>
</html>
