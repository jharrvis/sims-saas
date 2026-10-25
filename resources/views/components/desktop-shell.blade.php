@props([
    'title' => 'Dashboard',
    'surface' => 'school',
    'groups' => [],
    'user' => null,
    'workspaceName' => null,
    'workspaceSub' => null,
    'homeRoute' => null,
    'logoutRoute' => null,
    'quickActions' => [],
])

@php
    $user = $user ?? auth()->user();
    $surface = $surface === 'platform' ? 'platform' : 'school';
    $groups = $groups ?: ($surface === 'platform' ? \App\Support\Navigation::platform() : \App\Support\Navigation::school());
    $commands = \App\Support\Navigation::commands($groups);
    $initials = collect(explode(' ', trim((string) ($user?->name ?? 'SIMS'))))
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $homeUrl = $homeRoute && \Illuminate\Support\Facades\Route::has($homeRoute)
        ? route($homeRoute)
        : ($surface === 'platform' ? route('platform.dashboard') : route('dashboard'));
    $settingsRoute = $surface === 'school'
        ? (\Illuminate\Support\Facades\Route::has('pengaturan.profil') ? route('pengaturan.profil') : $homeUrl)
        : null;
    $helpRoute = \Illuminate\Support\Facades\Route::has('bantuan.index') ? route('bantuan.index') : null;
    $announcementRoute = \Illuminate\Support\Facades\Route::has('pengumuman.index') ? route('pengumuman.index') : null;
    $logoutUrl = $logoutRoute && \Illuminate\Support\Facades\Route::has($logoutRoute)
        ? route($logoutRoute)
        : ($surface === 'platform' ? route('platform.logout') : route('logout'));
    $roleLabel = $surface === 'platform' ? 'Operator Platform' : 'Administrator Sekolah';
    $workspaceName = $workspaceName ?: ($surface === 'platform' ? 'SIMS Platform' : (app(\App\Tenancy\TenantContext::class)->school()?->nama ?? 'Workspace Sekolah'));
    $workspaceSub = $workspaceSub ?: ($surface === 'platform' ? 'Operator Platform' : 'Workspace Sekolah');
    $accent = $surface === 'platform' ? 'PLATFORM' : null;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d1525">
    <title>{{ $title }} — SIMS{{ $surface === 'platform' ? ' Platform' : '' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/desktop.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="surface-{{ $surface }}"
      x-data="simsShell({ commands: {!! \Illuminate\Support\Js::from($commands) !!} })"
      x-init="init()">
<div class="app-frame">
    <div class="sims-progress" data-sims-progress aria-hidden="true"><span></span></div>

    <div class="mobile-scrim" x-show="mobileOpen" x-cloak @click="mobileOpen = false"></div>

    <aside class="sidebar" :class="{ 'sidebar-mini': collapsed, 'sidebar-mobile-open': mobileOpen }" aria-label="Navigasi utama">
        <div class="sidebar-logo">
            <a href="{{ $homeUrl }}" class="logo-symbol" aria-label="SIMS — {{ $title }}">
                <x-icon name="graduation-cap" class="h-[27px] w-[27px]" />
            </a>
            <div class="logo-copy" x-show="!collapsed">
                <strong>SIMS<span class="logo-dot">.</span></strong>
                <small>{{ $accent ?? 'School Management' }}</small>
            </div>
            <button type="button" class="sidebar-collapse" @click="toggleSidebar()"
                    :title="collapsed ? 'Perluas sidebar' : 'Perkecil sidebar'"
                    :aria-label="collapsed ? 'Perluas sidebar' : 'Perkecil sidebar'">
                <x-icon name="panel-left-open" class="h-[17px] w-[17px]" x-show="collapsed" x-cloak />
                <x-icon name="panel-left-close" class="h-[17px] w-[17px]" x-show="!collapsed" />
            </button>
        </div>

        <div class="school-switch" x-show="!collapsed">
            <div class="school-mark">{{ mb_substr($workspaceName, 0, 1) }}</div>
            <div class="school-text">
                <strong>{{ $workspaceName }}</strong>
                <span>{{ $workspaceSub }}</span>
            </div>
            <x-icon name="chevrons-up-down" class="h-[14px] w-[14px]" />
        </div>

        <nav class="sidebar-scroll" aria-label="Menu utama">
            @foreach ($groups as $group)
                <div class="nav-group">
                    <div class="nav-label" x-show="!collapsed">{{ $group['label'] }}</div>
                    @foreach ($group['items'] as $item)
                        <a href="{{ $item['href'] }}"
                           class="nav-link {{ $item['active'] ? 'active' : '' }}"
                           title="{{ $item['label'] }}"
                           @if ($item['active']) aria-current="page" @endif
                           @click="mobileOpen = false">
                            <x-icon :name="$item['icon']" class="h-[19px] w-[19px]" />
                            <span class="nav-link-text" x-show="!collapsed">{{ $item['label'] }}</span>
                            @if (! empty($item['badge']))
                                <span class="nav-count" x-show="!collapsed">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="sidebar-footer">
            @if ($helpRoute)
                <div class="sidebar-help" x-show="!collapsed">
                    <x-icon name="sparkles" class="h-[17px] w-[17px]" />
                    <strong>Butuh bantuan?</strong>
                    <p>{{ $surface === 'platform' ? 'Panduan operasional platform tersedia untuk tim operator.' : 'Panduan penggunaan SIMS tersedia untuk tim sekolah Anda.' }}</p>
                    <a href="{{ $helpRoute }}">Buka panduan <x-icon name="arrow-up-right" class="h-[13px] w-[13px]" /></a>
                </div>
            @endif
            @if ($settingsRoute)
                <a class="nav-link footer-settings" href="{{ $settingsRoute }}">
                    <x-icon name="settings" class="h-[19px] w-[19px]" />
                    <span x-show="!collapsed">Pengaturan</span>
                </a>
            @endif
            @if ($user)
                <div class="sidebar-user" x-show="!collapsed">
                    <div class="profile-avatar">{{ $initials }}</div>
                    <div class="sidebar-user-copy">
                        <strong>{{ $user->name }}</strong>
                        <small>{{ $roleLabel }}</small>
                    </div>
                    <form method="POST" action="{{ $logoutUrl }}">
                        @csrf
                        <button type="submit" class="table-action" title="Keluar" aria-label="Keluar">
                            <x-icon name="log-out" class="h-[15px] w-[15px]" />
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </aside>

    <div class="app-main" :class="{ 'main-expanded': collapsed }">
        <header class="topbar flex items-center justify-between">
            <div class="top-left">
                <button type="button" class="icon-btn top-mobile-menu" @click="mobileOpen = true" aria-label="Buka menu">
                    <x-icon name="menu" class="h-5 w-5" />
                </button>
                <div class="page-ident">
                    <div class="breadcrumb">
                        <span>{{ $surface === 'platform' ? 'Platform' : 'Workspace' }}</span>
                        <x-icon name="chevron-right" class="h-[13px] w-[13px]" />
                        <span>{{ $title }}</span>
                    </div>
                    <div class="heading-line">
                        <h1>{{ $title }}</h1>
                        <span class="year-chip">{{ $workspaceSub }}</span>
                    </div>
                </div>
            </div>

            <div class="top-right">
                <button type="button" class="global-search" @click="openCommand()" aria-label="Cari menu">
                    <x-icon name="search" class="h-[18px] w-[18px]" />
                    <span>Cari siswa, guru, halaman...</span>
                    <kbd>Ctrl K</kbd>
                </button>

                <button type="button" class="icon-btn theme-toggle" :title="dark ? 'Mode terang' : 'Mode gelap'"
                        @click="toggleTheme()" :aria-label="dark ? 'Mode terang' : 'Mode gelap'">
                    <x-icon name="sun" class="h-[19px] w-[19px]" x-show="dark" x-cloak />
                    <x-icon name="moon" class="h-[19px] w-[19px]" x-show="!dark" />
                </button>

                <div class="relative">
                    <button type="button" class="icon-btn notification-trigger" @click="notificationsOpen = !notificationsOpen" title="Notifikasi" aria-label="Notifikasi">
                        <x-icon name="bell" class="h-5 w-5" />
                    </button>
                    <div class="popover notif-popover" x-show="notificationsOpen" x-cloak @click.outside="notificationsOpen = false">
                        <div class="popover-head">
                            <strong>Notifikasi</strong>
                            <span class="badge badge-blue">Terbaru</span>
                        </div>
                        <div class="notification-item">
                            <span class="mini-icon blue"><x-icon name="bell" class="h-4 w-4" /></span>
                            <span>
                                <strong>Belum ada notifikasi baru</strong>
                                <small>Pembaruan penting akan muncul di sini.</small>
                            </span>
                        </div>
                        @if ($announcementRoute)
                            <a class="popover-footer" href="{{ $announcementRoute }}">Lihat pengumuman <x-icon name="arrow-right" class="h-[13px] w-[13px]" /></a>
                        @endif
                    </div>
                </div>

                @if (! empty($quickActions))
                    <div class="relative">
                        <button type="button" class="btn btn-primary quick-top" @click="quickOpen = !quickOpen">
                            <x-icon name="plus" class="h-[17px] w-[17px]" />
                            <span>Aksi Cepat</span>
                            <x-icon name="chevron-down" class="h-[14px] w-[14px]" />
                        </button>
                        <div class="popover quick-popover" x-show="quickOpen" x-cloak @click.outside="quickOpen = false">
                            @foreach ($quickActions as $action)
                                <a href="{{ $action['href'] ?? '#' }}">
                                    <x-icon :name="$action['icon'] ?? 'plus'" class="h-[17px] w-[17px]" />
                                    <span>{{ $action['label'] ?? 'Aksi' }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="relative">
                    <button type="button" class="profile-button" @click="profileOpen = !profileOpen">
                        <span class="profile-avatar">{{ $initials }}</span>
                        <span class="profile-text">
                            <strong>{{ $user?->name ?? 'Pengguna' }}</strong>
                            <small>{{ $roleLabel }}</small>
                        </span>
                        <x-icon name="chevron-down" class="h-[15px] w-[15px]" />
                    </button>
                    <div class="popover profile-popover" x-show="profileOpen" x-cloak @click.outside="profileOpen = false">
                        <div class="profile-pop-head">
                            <div class="profile-avatar">{{ $initials }}</div>
                            <div>
                                <strong>{{ $user?->name ?? 'Pengguna' }}</strong>
                                <small>{{ $user?->email ?? $user?->phone ?? '' }}</small>
                            </div>
                        </div>
                        @if ($settingsRoute)
                            <a href="{{ $settingsRoute }}">
                                <x-icon name="settings" class="h-4 w-4" /> Pengaturan akun
                            </a>
                        @endif
                        @if ($surface === 'school' && \Illuminate\Support\Facades\Route::has('pengaturan.keamanan'))
                            <a href="{{ route('pengaturan.keamanan') }}">
                                <x-icon name="shield-check" class="h-4 w-4" /> Keamanan akun
                            </a>
                        @endif
                        <form method="POST" action="{{ $logoutUrl }}">
                            @csrf
                            <button type="submit">
                                <x-icon name="log-out" class="h-4 w-4" /> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="content-wrap w-full">
            @if (session('impersonator_id'))
                <div class="impersonate-banner">
                    <span>Mode impersonate — Anda masuk sebagai {{ auth()->user()->name }} ({{ app(\App\Tenancy\TenantContext::class)->school()?->nama ?? '' }}).</span>
                    <form method="POST" action="{{ route('platform.impersonate.stop') }}">
                        @csrf
                        <button class="btn btn-soft btn-sm" type="submit">Kembali ke Panel Platform</button>
                    </form>
                </div>
            @endif

            <main id="page-content" class="page-enter" aria-live="polite">
                {{ $slot }}
            </main>

            <footer class="main-footer">
                <span>&copy; {{ now()->year }} SIMS <span class="footer-dot">•</span> Sistem Informasi Manajemen Sekolah</span>
                <span>{{ config('app.version', 'v1.0.0') }} <span class="footer-dot">•</span> {{ $surface === 'platform' ? 'Platform' : 'Sekolah' }}</span>
            </footer>
        </div>
    </div>

    <div class="command-scrim" x-show="commandOpen" x-cloak @click.self="commandOpen = false" @keydown.escape.window="commandOpen = false">
        <div class="command-panel" role="dialog" aria-modal="true" aria-label="Pencarian menu">
            <div class="command-input">
                <x-icon name="search" class="h-[21px] w-[21px]" />
                <input x-ref="commandInput" placeholder="Cari halaman, data, atau tindakan..." x-model="commandQuery" @keydown.enter="runFirstCommand()">
                <kbd>ESC</kbd>
            </div>
            <div class="command-results">
                <small class="command-label">HALAMAN & MENU</small>
                <template x-for="item in filteredCommands()" :key="item.href">
                    <a class="command-result" :href="item.href" @click="commandOpen = false; commandQuery = ''">
                        <x-icon name="search" class="h-[18px] w-[18px]" />
                        <span x-text="item.title"></span>
                        <x-icon name="corner-down-left" class="h-[14px] w-[14px]" />
                    </a>
                </template>
                <p class="empty-mini" x-show="filteredCommands().length === 0">Tidak ada menu yang cocok.</p>
            </div>
            <div class="command-help">Gunakan <kbd>Enter</kbd> untuk memilih <span>•</span> <kbd>Esc</kbd> untuk menutup</div>
        </div>
    </div>

    <div class="sims-toast-tray" data-sims-toasts aria-live="polite"></div>
</div>

@livewireScripts
</body>
</html>
