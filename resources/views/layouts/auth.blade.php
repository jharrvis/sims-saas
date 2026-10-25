@php
    $variant = $variant ?? 'desktop';
    $title = $title ?? 'Masuk';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $variant === 'desktop' ? '#0d1525' : '#4f46e5' }}">
    <title>{{ $title }} — SIMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/auth.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="auth-page">
@if ($variant === 'desktop')
    <div class="auth-desktop">
        <div class="auth-mobile-brand">
            <a href="{{ route('login') }}" class="auth-brand">
                <span class="auth-brand-mark"><x-icon name="graduation-cap" class="h-5 w-5" /></span>
                <span>
                    <strong>SIMS</strong>
                    <small>Sistem Informasi Manajemen Sekolah</small>
                </span>
            </a>
            <span class="auth-online" data-network-status>Online</span>
        </div>

        <aside class="auth-art" aria-label="Tentang SIMS">
            <div class="auth-art-inner">
                <div>
                    <a href="{{ route('login') }}" class="auth-brand">
                        <span class="auth-brand-mark auth-art-mark"><x-icon name="graduation-cap" class="h-5 w-5" /></span>
                        <span>
                            <strong>SIMS</strong>
                            <small>Sistem Informasi Manajemen Sekolah</small>
                        </span>
                    </a>

                    <div class="auth-story" style="margin-top: 64px;">
                        <span class="eyebrow">Satu platform, seluruh ekosistem sekolah</span>
                        <h1>Kelola sekolah<br><span>lebih tenang.</span></h1>
                        <p>Data siswa, akademik, keuangan, dan komunikasi wali murid dalam satu tempat yang aman dan terhubung.</p>

                        <div class="auth-illustration">
                            <div class="auth-illustration-bars" aria-hidden="true">
                                <span></span><span></span><span></span>
                            </div>
                            <div>
                                <strong>Operasional sekolah, terukur.</strong>
                                <p>Pantau kehadiran, nilai, dan tagihan tanpa berpindah aplikasi.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="auth-art-footer">
                    Aman untuk data pendidikan · Akses berbasis peran · Audit trail aktif
                </div>
            </div>
        </aside>

        <main class="auth-form-side" id="main-content">
            <div class="auth-form-wrap">
                {!! $slot !!}
            </div>
        </main>
    </div>
@else
    <div class="auth-pwa">
        <header class="app-header">
            <a href="{{ route('pwa.login') }}" class="auth-brand">
                <span class="auth-brand-mark"><x-icon name="graduation-cap" class="h-5 w-5" /></span>
                <span>
                    <strong>SIMS</strong>
                    <small>Untuk siswa, wali, guru & karyawan</small>
                </span>
            </a>
            <span class="auth-online" data-network-status>Online</span>
        </header>

        <div class="screen auth-screen">
            <section class="auth-intro">
                <div class="auth-hero">
                    @if (! empty($eyebrow))
                        <span class="eyebrow">{{ $eyebrow }}</span>
                    @endif
                    <h1>{{ $storyTitle ?? 'Sekolah dalam genggaman.' }}</h1>
                    <p>{{ $storyText ?? 'Masuk untuk melihat jadwal, nilai, kehadiran, dan informasi sekolah Anda.' }}</p>
                </div>

                <div class="identity-note">
                    <span class="identity-note__icon"><x-icon name="shield-check" class="h-5 w-5" /></span>
                    <span>
                        <strong>Akses otomatis sesuai akun</strong>
                        <small>Siswa, wali, guru, dan pegawai tidak perlu memilih peran.</small>
                    </span>
                </div>
            </section>

            <section class="auth-panel">
                {!! $slot !!}
            </section>
        </div>
    </div>
@endif

@livewireScripts
</body>
</html>
