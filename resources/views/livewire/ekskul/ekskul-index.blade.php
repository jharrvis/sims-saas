<div>
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            @can('prestasi.view')
                <a href="{{ route('ekskul.prestasi') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary">
                    <i data-lucide="trophy" class="w-4 h-4"></i> Prestasi
                </a>
            @endcan
        </div>
        @can('ekskul.create')
            <a href="{{ route('ekskul.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Ekstrakurikuler
            </a>
        @endcan
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($items as $item)
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold text-slate-800 dark:text-white">{{ $item->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            @if ($item->jadwal) {{ $item->jadwal }} · @endif
                            @if ($item->pembina) Pembina: {{ $item->pembina->nama }} @endif
                        </p>
                    </div>
                    <span class="shrink-0 text-[11px] font-bold px-2 py-1 rounded-lg {{ $item->aktif ? 'bg-success/10 text-success' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                        {{ $item->members_count }} anggota
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <a href="{{ route('ekskul.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Kelola Anggota</a>
                    @can('ekskul.update')
                        <a href="{{ route('ekskul.edit', $item) }}" class="text-xs font-semibold text-slate-500 hover:text-primary">Ubah</a>
                    @endcan
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-sm text-slate-500">
                Belum ada ekstrakurikuler.
            </div>
        @endforelse
    </div>
</div>
