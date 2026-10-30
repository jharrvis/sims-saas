<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Informasi &amp; Sistem / Pengumuman</span>
            <h2 class="management-page-title">Pengumuman</h2>
            <p>Pengumuman untuk siswa, orang tua, dan staf — draf, tayang, atau ditarik kembali.</p>
        </div>
        <div class="page-actions">
            @can('pengumuman.create')
                <a href="{{ route('pengumuman.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Buat Pengumuman
                </a>
            @endcan
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="pengumuman-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari pengumuman</span>
                <input id="pengumuman-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari judul…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <label class="management-filter-field" for="pengumuman-status"><span class="sr-only">Filter status</span>
                <select id="pengumuman-status" wire:model.live="status" class="select-field">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Announcement::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div class="page-stack">
        @forelse ($items as $item)
            <article class="card" wire:key="pengumuman-{{ $item->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold">{{ $item->judul }}</h3>
                            @if ($item->penting)
                                <span class="badge badge-red">PENTING</span>
                            @endif
                            <span class="badge {{ $item->status === 'tayang' ? 'badge-green' : 'badge-gray' }}">
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
                        <button wire:click="destroy({{ $item->id }})" data-sims-confirm="Hapus pengumuman ini?" class="text-xs font-semibold text-danger hover:underline">Hapus</button>
                    @endcan
                    <a href="{{ route('bantuan.show', 'pengumuman') }}" class="ml-auto text-xs text-slate-400 hover:text-primary inline-flex items-center gap-1">
                        <x-icon name="circle-help" class="h-4 w-4" /> Bantuan
                    </a>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <p>Belum ada pengumuman. <a href="{{ route('bantuan.show', 'pengumuman') }}" class="text-primary hover:underline">Pelajari cara membuat pengumuman</a>.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
