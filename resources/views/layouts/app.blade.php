<!DOCTYPE html>
<html lang="id" x-data="{ dark: localStorage.getItem('sims-theme') === 'dark' }"
      x-init="$watch('dark', v => { localStorage.setItem('sims-theme', v ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', v) });
              document.documentElement.classList.toggle('dark', dark)">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SIMS' }} — SIMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 font-sans dark:bg-boxdark2 dark:text-slate-200">

<div class="flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <aside class="hidden md:flex w-64 shrink-0 flex-col bg-boxdark text-slate-300">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-5 h-16 font-bold text-white">
            <span class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white font-extrabold">S</span>
            <span>SIMS</span>
        </a>
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm">
            <x-nav-link href="{{ route('dashboard') }}" icon="layout-dashboard" label="Dashboard" :active="request()->routeIs('dashboard')" />
            @if (auth()->user()?->is_platform)
                <x-nav-link href="{{ route('platform.dashboard') }}" icon="shield-check" label="Panel Platform" :active="request()->routeIs('platform.*')" />
            @endif
            @if (auth()->user()?->activeMemberships()->count() > 1)
                <x-nav-link href="{{ route('tenant.switch') }}" icon="repeat" label="Ganti Sekolah" :active="request()->routeIs('tenant.switch')" />
            @endif
            @can('siswa.view')
                <x-nav-link href="{{ route('siswa.index') }}" icon="users" label="Data Siswa" :active="request()->routeIs('siswa.*')" />
            @endcan
            @can('qr.view')
                <x-nav-link href="{{ route('qr.kartu') }}" icon="qr-code" label="QR Login" :active="request()->routeIs('qr.*')" />
            @endcan
            @can('guru.view')
                <x-nav-link href="{{ route('guru.index') }}" icon="graduation-cap" label="Data Guru" :active="request()->routeIs('guru.*')" />
            @endcan
            @can('kelas.view')
                <x-nav-link href="{{ route('kelas.index') }}" icon="school" label="Data Kelas" :active="request()->routeIs('kelas.*')" />
            @endcan
            @can('jadwal.view')
                <x-nav-link href="{{ route('jadwal.index') }}" icon="calendar-days" label="Jadwal Pelajaran" :active="request()->routeIs('jadwal.*', 'mapel.*')" />
            @endcan
            @can('absensi.view')
                <x-nav-link href="{{ route('absensi.index') }}" icon="clipboard-check" label="Absensi Siswa" :active="request()->routeIs('absensi.*')" />
            @endcan
            @can('nilai.input')
                <x-nav-link href="{{ route('nilai.input') }}" icon="award" label="Data Nilai" :active="request()->routeIs('nilai.*')" />
            @endcan
            @can('ppdb.periode.view')
                <x-nav-link href="{{ route('ppdb.index') }}" icon="user-plus" label="PPDB" :active="request()->routeIs('ppdb.*')" />
            @endcan
            @can('keuangan.tagihan.view')
                <x-nav-link href="{{ route('keuangan.index') }}" icon="wallet" label="Keuangan SPP" :active="request()->routeIs('keuangan.*')" />
            @endcan
            @can('pengumuman.view')
                <x-nav-link href="{{ route('pengumuman.index') }}" icon="megaphone" label="Pengumuman" :active="request()->routeIs('pengumuman.*')" />
            @endcan
            @can('laporan.view')
                <x-nav-link href="{{ route('laporan.index') }}" icon="bar-chart-3" label="Laporan" :active="request()->routeIs('laporan.*')" />
            @endcan
            @can('perpus.view')
                <x-nav-link href="{{ route('perpus.index') }}" icon="library" label="Perpustakaan" :active="request()->routeIs('perpus.*')" />
            @endcan
            @can('ekskul.view')
                <x-nav-link href="{{ route('ekskul.index') }}" icon="medal" label="Ekstrakurikuler" :active="request()->routeIs('ekskul.*')" />
            @endcan
            @can('lms.view')
                <x-nav-link href="{{ route('lms.index') }}" icon="pen-line" label="Tugas Online" :active="request()->routeIs('lms.*')" />
            @endcan
            @can('broadcast.view')
                <x-nav-link href="{{ route('broadcast.index') }}" icon="send" label="Broadcast" :active="request()->routeIs('broadcast.*')" />
            @endcan
            @can('aset.view')
                <x-nav-link href="{{ route('aset.index') }}" icon="package" label="Inventaris" :active="request()->routeIs('aset.*')" />
            @endcan
            @can('uks.view')
                <x-nav-link href="{{ route('uks.index') }}" icon="heart-pulse" label="UKS" :active="request()->routeIs('uks.*')" />
            @endcan
            @can('transportasi.view')
                <x-nav-link href="{{ route('transportasi.index') }}" icon="bus" label="Antar-Jemput" :active="request()->routeIs('transportasi.*')" />
            @endcan
            @can('kantin.view')
                <x-nav-link href="{{ route('kantin.index') }}" icon="store" label="Kantin" :active="request()->routeIs('kantin.*')" />
            @endcan
            @can('survei.view')
                <x-nav-link href="{{ route('survei.index') }}" icon="clipboard-list" label="Survei" :active="request()->routeIs('survei.*')" />
            @endcan
            @can('alumni.view')
                <x-nav-link href="{{ route('alumni.index') }}" icon="briefcase" label="Alumni" :active="request()->routeIs('alumni.*')" />
            @endcan
            @can('organisasi.view')
                <x-nav-link href="{{ route('organisasi.index') }}" icon="users-round" label="Organisasi" :active="request()->routeIs('organisasi.*')" />
            @endcan
            @can('bk.view')
                <x-nav-link href="{{ route('bk.index') }}" icon="heart-handshake" label="BK & Konseling" :active="request()->routeIs('bk.*')" />
            @endcan
            @can('tatib.view')
                <x-nav-link href="{{ route('tatib.index') }}" icon="shield-alert" label="Tata Tertib" :active="request()->routeIs('tatib.*')" />
            @endcan
            @can('erapor.view')
                <x-nav-link href="{{ route('erapor.index') }}" icon="file-text" label="E-Rapor" :active="request()->routeIs('erapor.*')" />
            @endcan
            @can('sekolah.view')
                <x-nav-link href="{{ route('pengaturan.profil') }}" icon="settings" label="Pengaturan" :active="request()->routeIs('pengaturan.*')" />
            @endcan
            <x-nav-link href="{{ route('pengaturan.keamanan') }}" icon="shield-check" label="Keamanan Akun" :active="request()->routeIs('pengaturan.keamanan')" />
            @can('langganan.view')
                <x-nav-link href="{{ route('langganan') }}" icon="credit-card" label="Langganan" :active="request()->routeIs('langganan')" />
            @endcan
            <x-nav-link href="{{ route('bantuan.index') }}" icon="circle-help" label="Pusat Bantuan" :active="request()->routeIs('bantuan.*')" />
        </nav>
        <div class="px-5 py-4 text-xs text-slate-500 border-t border-slate-700/50">
            SIMS v1.0
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 shrink-0 flex items-center justify-between px-4 md:px-6 bg-white dark:bg-boxdark border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <button class="md:hidden p-2 -ml-2 text-slate-500" aria-label="Buka menu navigasi">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <h1 class="text-lg font-semibold text-slate-800 dark:text-white">{{ $title ?? 'Dashboard' }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <button @click="dark = !dark" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700"
                        :aria-label="dark ? 'Mode terang' : 'Mode gelap'">
                    <i data-lucide="sun" class="w-5 h-5" x-show="dark"></i>
                    <i data-lucide="moon" class="w-5 h-5" x-show="!dark"></i>
                </button>
                <a href="#" class="hidden sm:inline-flex text-xs px-3 py-1.5 bg-warning text-white font-semibold rounded-lg hover:bg-amber-500">
                    Upgrade Sekarang
                </a>
                <button class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Notifikasi">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </button>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-6">
            @if (session('impersonator_id'))
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white">
                    <span>Mode impersonate — Anda masuk sebagai {{ auth()->user()->name }} ({{ app(App\Tenancy\TenantContext::class)->school()?->nama ?? '' }}).</span>
                    <form method="POST" action="{{ route('platform.impersonate.stop') }}">
                        @csrf
                        <button class="rounded-lg bg-white/20 px-3 py-1 text-xs font-bold hover:bg-white/30">Kembali ke Panel Platform</button>
                    </form>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
<script src="https://unpkg.com/lucide@0.475.0"></script>
<script>lucide.createIcons();</script>
</body>
</html>
