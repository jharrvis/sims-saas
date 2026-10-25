<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Ekstrakurikuler</span>
            <h2 class="management-page-title">Ekstrakurikuler</h2>
            <p>Daftar ekstrakurikuler beserta jadwal, pembina, dan jumlah anggotanya.</p>
        </div>
        <div class="page-actions">
            @can('prestasi.view')
                <a href="{{ route('ekskul.prestasi') }}" class="btn btn-outline">
                    <x-icon name="trophy" class="h-4 w-4" /> Prestasi
                </a>
            @endcan
            @can('ekskul.create')
                <a href="{{ route('ekskul.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Ekstrakurikuler
                </a>
            @endcan
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($items as $item)
            <article class="card" wire:key="ekskul-{{ $item->id }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold">{{ $item->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            @if ($item->jadwal) {{ $item->jadwal }} · @endif
                            @if ($item->pembina) Pembina: {{ $item->pembina->nama }} @endif
                        </p>
                    </div>
                    <span class="badge shrink-0 {{ $item->aktif ? 'badge-green' : 'badge-gray' }}">
                        {{ $item->members_count }} anggota
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <a href="{{ route('ekskul.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Kelola Anggota</a>
                    @can('ekskul.update')
                        <a href="{{ route('ekskul.edit', $item) }}" class="text-xs font-semibold text-slate-500 hover:text-primary">Ubah</a>
                    @endcan
                </div>
            </article>
        @empty
            <div class="empty-state col-span-full">
                <p>Belum ada ekstrakurikuler.</p>
            </div>
        @endforelse
    </div>
</div>
