<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / Transportasi</span>
            <h2 class="management-page-title">Rute Antar-Jemput</h2>
            <p>Daftar rute beserta kendaraan, sopir, dan jumlah penumpangnya.</p>
        </div>
        <div class="page-actions">
            @can('transportasi.create')
                <a href="{{ route('transportasi.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Rute
                </a>
            @endcan
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($items as $item)
            <article class="card" wire:key="rute-{{ $item->id }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold">{{ $item->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            @if ($item->no_kendaraan) {{ $item->no_kendaraan }} · @endif
                            @if ($item->sopir) Sopir: {{ $item->sopir }} @endif
                        </p>
                    </div>
                    <span class="badge shrink-0 {{ $item->aktif ? 'badge-green' : 'badge-gray' }}">
                        {{ $item->members_count }}{{ $item->kapasitas > 0 ? '/'.$item->kapasitas : '' }} penumpang
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <a href="{{ route('transportasi.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Kelola Rute</a>
                    @can('transportasi.update')
                        <a href="{{ route('transportasi.edit', $item) }}" class="text-xs font-semibold text-slate-500 hover:text-primary">Ubah</a>
                    @endcan
                </div>
            </article>
        @empty
            <div class="empty-state col-span-full">
                <p>Belum ada rute antar-jemput.</p>
            </div>
        @endforelse
    </div>
</div>
