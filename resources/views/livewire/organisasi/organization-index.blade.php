<div>
    <div class="flex justify-end mb-5">
        @can('organisasi.create')
            <a href="{{ route('organisasi.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Organisasi
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
                            {{ \App\Models\Organization::JENIS[$item->jenis] }}
                            @if ($item->pembina) · Pembina: {{ $item->pembina->nama }} @endif
                        </p>
                    </div>
                    <span class="shrink-0 text-[11px] font-bold px-2 py-1 rounded-lg bg-primary/10 text-primary">
                        {{ $item->members_count }} pengurus
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <a href="{{ route('organisasi.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Kelola</a>
                    @can('organisasi.update')
                        <a href="{{ route('organisasi.edit', $item) }}" class="text-xs font-semibold text-slate-500 hover:text-primary">Ubah</a>
                    @endcan
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-sm text-slate-500">
                Belum ada organisasi.
            </div>
        @endforelse
    </div>
</div>
