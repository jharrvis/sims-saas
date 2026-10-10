<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari judul / kode…"
                   aria-label="Cari buku"
                   class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
        </div>
        <div class="flex items-center gap-2">
            @can('perpus.pinjam')
                <a href="{{ route('perpus.pinjam') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Peminjaman
                </a>
            @endcan
            @can('perpus.buku')
                <a href="{{ route('perpus.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                    <i data-lucide="plus" class="w-4 h-4"></i> Tambah Buku
                </a>
            @endcan
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($books as $book)
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-slate-800 dark:text-white truncate">{{ $book->judul }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $book->kode }}@if($book->pengarang) · {{ $book->pengarang }}@endif</p>
                        @if ($book->kategori)
                            <span class="inline-block mt-1.5 text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800">{{ $book->kategori }}</span>
                        @endif
                    </div>
                    <span class="shrink-0 text-[11px] font-bold px-2 py-1 rounded-lg {{ $book->tersedia > 0 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                        {{ $book->tersedia }}/{{ $book->jumlah }} tersedia
                    </span>
                </div>
                @can('perpus.buku')
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <a href="{{ route('perpus.edit', $book) }}" class="text-xs font-semibold text-primary hover:underline">Ubah</a>
                    </div>
                @endcan
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-sm text-slate-500">
                Katalog masih kosong.
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $books->links() }}</div>
</div>
