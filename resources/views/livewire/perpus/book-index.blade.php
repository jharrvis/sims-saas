<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / Perpustakaan</span>
            <h2 class="management-page-title">Katalog Buku</h2>
            <p>Daftar buku beserta ketersediaan stoknya.</p>
        </div>
        <div class="page-actions">
            @can('perpus.pinjam')
                <a href="{{ route('perpus.pinjam') }}" class="btn btn-outline">
                    <x-icon name="book-open" class="h-4 w-4" /> Peminjaman
                </a>
            @endcan
            @can('perpus.buku')
                <a href="{{ route('perpus.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Buku
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
            <label class="field-search management-search-field" for="buku-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari buku</span>
                <input id="buku-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari judul / kode…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($books as $book)
            <article class="card" wire:key="buku-{{ $book->id }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="font-semibold truncate">{{ $book->judul }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $book->kode }}@if($book->pengarang) · {{ $book->pengarang }}@endif</p>
                        @if ($book->kategori)
                            <span class="badge badge-gray mt-1.5">{{ $book->kategori }}</span>
                        @endif
                    </div>
                    <span class="badge shrink-0 {{ $book->tersedia > 0 ? 'badge-green' : 'badge-red' }}">
                        {{ $book->tersedia }}/{{ $book->jumlah }} tersedia
                    </span>
                </div>
                @can('perpus.buku')
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <a href="{{ route('perpus.edit', $book) }}" class="text-xs font-semibold text-primary hover:underline">Ubah</a>
                    </div>
                @endcan
            </article>
        @empty
            <div class="empty-state col-span-full">
                <p>Katalog masih kosong.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $books->links() }}</div>
</div>
