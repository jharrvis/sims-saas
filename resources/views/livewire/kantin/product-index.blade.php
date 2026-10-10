<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari produk…"
                       aria-label="Cari produk"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            @can('kantin.kasir')
                <a href="{{ route('kantin.kasir') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i> Kasir
                </a>
                <a href="{{ route('kantin.transaksi') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary">
                    <i data-lucide="receipt" class="w-4 h-4"></i> Riwayat
                </a>
            @endcan
        </div>
        @can('kantin.produk')
            <button wire:click="$set('showForm', true)" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Produk
            </button>
        @endcan
    </div>

    @if ($showForm)
        @can('kantin.produk')
            <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5 grid sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama <span class="text-danger">*</span></label>
                    <input type="text" wire:model="nama" maxlength="150"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kategori</label>
                    <input type="text" wire:model="kategori" maxlength="100"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Harga (Rp)</label>
                    <input type="number" wire:model="harga" min="0"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Stok</label>
                    <input type="number" wire:model="stok" min="0"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div class="sm:col-span-4 flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">Simpan</button>
                    <button type="button" wire:click="$set('showForm', false)" class="text-sm text-slate-500 hover:text-primary">Batal</button>
                </div>
            </form>
        @endcan
    @endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($items as $item)
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-4 {{ $item->aktif ? '' : 'opacity-60' }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold text-slate-800 dark:text-white">{{ $item->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}@if($item->kategori) · {{ $item->kategori }}@endif</p>
                    </div>
                    <span class="shrink-0 text-sm font-bold px-2.5 py-1 rounded-lg {{ $item->stok <= 0 ? 'bg-danger/10 text-danger' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                        {{ $item->stok }}
                    </span>
                </div>
                @can('kantin.produk')
                    <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <button wire:click="edit({{ $item->id }})" class="text-xs font-semibold text-primary hover:underline">Ubah</button>
                        <button wire:click="toggle({{ $item->id }})" class="text-xs font-semibold text-slate-500 hover:text-primary">
                            {{ $item->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </div>
                @endcan
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-sm text-slate-500">
                Belum ada produk.
            </div>
        @endforelse
    </div>
</div>
