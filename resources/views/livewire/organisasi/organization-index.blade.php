<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Organisasi</span>
            <h2 class="management-page-title">Organisasi</h2>
            <p>Daftar organisasi siswa beserta jenis, pembina, dan jumlah pengurusnya.</p>
        </div>
        <div class="page-actions">
            @can('organisasi.create')
                <a href="{{ route('organisasi.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Organisasi
                </a>
            @endcan
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($items as $item)
            <article class="card" wire:key="organisasi-{{ $item->id }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold">{{ $item->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ \App\Models\Organization::JENIS[$item->jenis] }}
                            @if ($item->pembina) · Pembina: {{ $item->pembina->nama }} @endif
                        </p>
                    </div>
                    <span class="badge badge-blue shrink-0">
                        {{ $item->members_count }} pengurus
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <a href="{{ route('organisasi.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Kelola</a>
                    @can('organisasi.update')
                        <a href="{{ route('organisasi.edit', $item) }}" class="text-xs font-semibold text-slate-500 hover:text-primary">Ubah</a>
                    @endcan
                </div>
            </article>
        @empty
            <div class="empty-state col-span-full">
                <p>Belum ada organisasi.</p>
            </div>
        @endforelse
    </div>
</div>
