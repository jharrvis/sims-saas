<div class="page-stack">
    @php
        $activeModules = $modules->where('aktif', true)->count();
        $coreModules = $modules->where('kategori', 'inti')->count();
    @endphp

    <div class="page-header">
        <div><span class="eyebrow">Platform / Produk</span><h2 class="platform-page-title">Katalog Modul</h2><p>Pantau modul inti dan add-on beserta jumlah tenant yang menggunakannya.</p></div>
        <div class="page-actions"><a href="{{ route('platform.tenants') }}" class="btn btn-outline"><x-icon name="school" class="h-4 w-4" /> Atur per Tenant</a></div>
    </div>

    <div class="stats-grid platform-summary-stats">
        <article class="card stat-card"><div class="stat-top"><span class="mini-icon purple"><x-icon name="puzzle" class="h-5 w-5" /></span><span class="badge badge-purple">Katalog</span></div><div class="stat-value">{{ $modules->count() }}</div><div class="stat-caption">Modul ditemukan</div><div class="stat-footer"><span class="stat-note">Sesuai pencarian aktif</span></div></article>
        <article class="card stat-card"><div class="stat-top"><span class="mini-icon blue"><x-icon name="shield-check" class="h-5 w-5" /></span><span class="badge badge-blue">Inti</span></div><div class="stat-value">{{ $coreModules }}</div><div class="stat-caption">Modul inti</div><div class="stat-footer"><span class="stat-note">Fondasi operasional sekolah</span></div></article>
        <article class="card stat-card"><div class="stat-top"><span class="mini-icon green"><x-icon name="check-circle" class="h-5 w-5" /></span><span class="badge badge-green">Aktif</span></div><div class="stat-value">{{ $activeModules }}</div><div class="stat-caption">Modul tersedia</div><div class="stat-footer"><span class="stat-note">Siap digunakan tenant</span></div></article>
    </div>

    <section class="card platform-table-card" aria-labelledby="module-list-title">
        <div class="card-head platform-table-heading"><div class="card-title"><span class="mini-icon orange"><x-icon name="puzzle" class="h-[19px] w-[19px]" /></span><div><h3 id="module-list-title">Direktori Modul</h3><p>Status katalog dan adopsi aktif tenant</p></div></div></div>
        <div class="table-toolbar platform-tenant-toolbar">
            <label class="field-search platform-search-field" for="module-search"><x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari modul</span><input id="module-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama, kode, atau kategori..." autocomplete="off"><span wire:loading wire:target="search" class="platform-inline-spinner" aria-hidden="true"></span></label>
        </div>
        <div class="table-scroll" wire:loading.class="platform-content-loading" wire:target="search">
            <table class="data-table platform-module-table">
                <thead><tr><th scope="col">Modul</th><th scope="col">Kategori</th><th scope="col">Versi</th><th scope="col">Status</th><th scope="col">Tenant Aktif</th></tr></thead>
                <tbody>
                    @forelse ($modules as $module)
                        <tr wire:key="platform-module-{{ $module->id }}">
                            <td><div class="person-cell"><span class="table-avatar"><x-icon name="puzzle" class="h-4 w-4" /></span><span><strong>{{ $module->nama }}</strong><small>{{ $module->kode }}</small></span></div></td>
                            <td><span class="badge {{ $module->kategori === 'inti' ? 'badge-blue' : 'badge-purple' }}">{{ ucfirst($module->kategori) }}</span></td>
                            <td class="platform-tabular">{{ $module->versi ?: '—' }}</td>
                            <td><span class="badge {{ $module->aktif ? 'badge-green' : 'badge-gray' }}">{{ $module->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td><strong class="platform-count-value">{{ number_format((int) ($counts[$module->id] ?? 0), 0, ',', '.') }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><div class="empty-symbol"><x-icon name="search" class="h-7 w-7" /></div><h3>Modul tidak ditemukan</h3><p>Coba gunakan nama, kode, atau kategori lain.</p>@if ($search !== '')<button type="button" class="btn btn-soft btn-sm" wire:click="$set('search', '')">Reset pencarian</button>@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
