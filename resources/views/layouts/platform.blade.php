<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Platform' }} — SIMS Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-800 font-sans">
@php $u = auth()->user(); @endphp
<div class="flex h-screen overflow-hidden">
    <aside class="hidden md:flex w-64 shrink-0 flex-col bg-slate-900 text-slate-300">
        <a href="{{ route('platform.dashboard') }}" class="flex items-center gap-2.5 px-5 h-16 font-bold text-white">
            <span class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center text-slate-900 font-extrabold">S</span>
            <span>SIMS <span class="text-amber-400 text-xs font-semibold">PLATFORM</span></span>
        </a>
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm">
            @if ($u->hasPermissionTo('platform.metrics.view', 'platform'))
                <x-nav-link href="{{ route('platform.dashboard') }}" icon="layout-dashboard" label="Dashboard" :active="request()->routeIs('platform.dashboard')" />
            @endif
            @if ($u->hasPermissionTo('platform.tenant.view', 'platform'))
                <x-nav-link href="{{ route('platform.tenants') }}" icon="school" label="Tenant" :active="request()->routeIs('platform.tenants*')" />
            @endif
            @if ($u->hasPermissionTo('platform.plan.view', 'platform'))
                <x-nav-link href="{{ route('platform.plans') }}" icon="package" label="Paket" :active="request()->routeIs('platform.plans')" />
            @endif
            @if ($u->hasPermissionTo('platform.module.view', 'platform'))
                <x-nav-link href="{{ route('platform.modules') }}" icon="puzzle" label="Modul" :active="request()->routeIs('platform.modules')" />
            @endif
            <x-nav-link href="{{ route('platform.subscriptions') }}" icon="repeat" label="Langganan" :active="request()->routeIs('platform.subscriptions')" />
            <x-nav-link href="{{ route('platform.invoices') }}" icon="receipt" label="Tagihan" :active="request()->routeIs('platform.invoices')" />
            @if ($u->hasPermissionTo('platform.audit.view', 'platform'))
                <x-nav-link href="{{ route('platform.audit') }}" icon="scroll-text" label="Audit Log" :active="request()->routeIs('platform.audit')" />
            @endif
            @if ($u->activeMemberships()->exists())
                <x-nav-link href="{{ route('dashboard') }}" icon="school" label="Dashboard Sekolah" :active="false" />
            @endif
        </nav>
        <div class="px-5 py-4 border-t border-slate-700/50 text-xs">
            <p class="font-semibold text-white">{{ $u->name }}</p>
            <p class="text-slate-400">Operator Platform</p>
            <form method="POST" action="{{ route('platform.logout') }}" class="mt-2">
                @csrf
                <button class="text-slate-400 hover:text-white">Keluar</button>
            </form>
        </div>
    </aside>
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="md:hidden bg-slate-900 text-white px-4 h-14 flex items-center justify-between">
            <span class="font-bold">SIMS <span class="text-amber-400 text-xs">PLATFORM</span></span>
        </header>
        <main class="flex-1 overflow-y-auto p-4 md:p-8">
            {{ $slot }}
        </main>
    </div>
</div>
@livewireScripts
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
