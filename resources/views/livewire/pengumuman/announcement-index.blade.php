<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari judul…"
                       aria-label="Cari pengumuman"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <select wire:model.live="status" aria-label="Filter status"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua status</option>
                @foreach (\App\Models\Announcement::STATUS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @can('pengumuman.create')
            <a href="{{ route('pengumuman.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Buat Pengumuman
            </a>
        @endcan
    </div>

    <div class="space-y-3">
        @forelse ($items as $item)
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold text-slate-800 dark:text-white">{{ $item->judul }}</h3>
                            @if ($item->penting)
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-danger/10 text-danger">PENTING</span>
                            @endif
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $item->status === 'tayang' ? 'bg-success/10 text-success' : 'bg-slate-100 text-slate-500 dark:bg-slate-800' }}">
                                {{ \App\Models\Announcement::STATUS[$item->status] }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5">
                            {{ $item->target_label }} ·
                            Tayang {{ $item->tayang_dari->format('d M Y') }}@if($item->tayang_sampai) – {{ $item->tayang_sampai->format('d M Y') }}@endif
                            @if($item->pembuat) · oleh {{ $item->pembuat->name }}@endif
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 line-clamp-2">{{ Str::limit($item->isi, 220) }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    @can('pengumuman.update')
                        <a href="{{ route('pengumuman.edit', $item) }}" class="text-xs font-semibold text-primary hover:underline">Ubah</a>
                    @endcan
                    @can('pengumuman.publish')
                        @if ($item->status === 'draf')
                            <button wire:click="publish({{ $item->id }})" class="text-xs font-semibold text-success hover:underline">Tayangkan</button>
                        @else
                            <button wire:click="unpublish({{ $item->id }})" class="text-xs font-semibold text-warning hover:underline">Tarik ke Draf</button>
                        @endif
                    @endcan
                    @can('pengumuman.delete')
                        <button wire:click="destroy({{ $item->id }})" wire:confirm="Hapus pengumuman ini?" class="text-xs font-semibold text-danger hover:underline">Hapus</button>
                    @endcan
                    <a href="{{ route('bantuan.show', 'pengumuman') }}" class="ml-auto text-xs text-slate-400 hover:text-primary inline-flex items-center gap-1">
                        <i data-lucide="circle-help" class="w-3.5 h-3.5"></i> Bantuan
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-sm text-slate-500">
                Belum ada pengumuman. <a href="{{ route('bantuan.show', 'pengumuman') }}" class="text-primary hover:underline">Pelajari cara membuat pengumuman</a>.
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
