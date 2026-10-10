<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SIMS">
    <title>{{ $title ?? 'SIMS' }} — SIMS</title>
    <link rel="manifest" href="/pwa/manifest.webmanifest">
    <link rel="icon" type="image/png" sizes="192x192" href="/pwa/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/pwa/icons/icon-192.png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    @livewireStyles
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased">
<div class="mx-auto max-w-md min-h-dvh bg-white flex flex-col">
    @if (! ($hideHeader ?? false))
        <header class="sticky top-0 z-10 bg-primary text-white px-4 pt-4 pb-3 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-white/70">{{ $schoolName ?? config('app.name', 'SIMS') }}</p>
                    <h1 class="text-lg font-bold leading-tight">{{ $title ?? 'SIMS' }}</h1>
                </div>
                @if (! empty($showChildSwitcher) && count($children ?? []) > 1)
                    <form method="POST" action="{{ route('pwa.switch-child') }}">
                        @csrf
                        <select name="student_id" onchange="this.form.submit()" aria-label="Pilih anak"
                                class="rounded-lg bg-white/15 border border-white/25 text-sm font-semibold px-2 py-1.5 outline-none">
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

    <main class="flex-1 px-4 py-4">
        {{ $slot }}
    </main>

    @if (! ($hideNav ?? false))
        <nav class="sticky bottom-0 z-10 bg-white border-t border-slate-200 pb-safe">
            <div class="grid grid-cols-5 text-[10px] font-semibold">
                <a href="{{ route('pwa.home') }}" class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('pwa.home') ? 'text-primary' : 'text-slate-400' }}">
                    <i data-lucide="house" class="w-5 h-5"></i>Beranda
                </a>
                <a href="{{ route('pwa.nilai') }}" class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('pwa.nilai') ? 'text-primary' : 'text-slate-400' }}">
                    <i data-lucide="award" class="w-5 h-5"></i>Nilai
                </a>
                <a href="{{ route('pwa.jadwal') }}" class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('pwa.jadwal') ? 'text-primary' : 'text-slate-400' }}">
                    <i data-lucide="calendar-days" class="w-5 h-5"></i>Jadwal
                </a>
                <a href="{{ route('pwa.pengumuman') }}" class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('pwa.pengumuman*') ? 'text-primary' : 'text-slate-400' }}">
                    <i data-lucide="megaphone" class="w-5 h-5"></i>Info
                </a>
                <a href="{{ route('pwa.profil') }}" class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('pwa.profil') ? 'text-primary' : 'text-slate-400' }}">
                    <i data-lucide="user" class="w-5 h-5"></i>Profil
                </a>
            </div>
        </nav>
    @endif
</div>

@livewireScripts
<script>
    lucide.createIcons();
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/pwa/sw.js').catch(() => {});
        });
    }
</script>
</body>
</html>
