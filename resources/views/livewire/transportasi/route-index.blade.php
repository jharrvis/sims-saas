<div>
    <div class="flex justify-end mb-5">
        @can('transportasi.create')
            <a href="{{ route('transportasi.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Rute
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
                            @if ($item->no_kendaraan) {{ $item->no_kendaraan }} · @endif
                            @if ($item->sopir) Sopir: {{ $item->sopir }} @endif
                        </p>
                    </div>
                    <span class="shrink-0 text-[11px] font-bold px-2 py-1 rounded-lg {{ $item->aktif ? 'bg-success/10 text-success' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                        {{ $item->members_count }}{{ $item->kapasitas > 0 ? '/'.$item->kapasitas : '' }} penumpang
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <a href="{{ route('transportasi.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Kelola Rute</a>
                    @can('transportasi.update')
                        <a href="{{ route('transportasi.edit', $item) }}" class="text-xs font-semibold text-slate-500 hover:text-primary">Ubah</a>
                    @endcan
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-sm text-slate-500">
                Belum ada rute antar-jemput.
            </div>
        @endforelse
    </div>
</div>
