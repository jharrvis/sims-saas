<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / Kantin</span>
            <h2 class="management-page-title">Produk Kantin</h2>
            <p>Produk yang dijual di kasir beserta harga dan stoknya.</p>
        </div>
        <div class="page-actions">
            @can('kantin.kasir')
                <a href="{{ route('kantin.kasir') }}" class="btn btn-outline">
                    <x-icon name="shopping-cart" class="h-4 w-4" /> Kasir
                </a>
                <a href="{{ route('kantin.transaksi') }}" class="btn btn-outline">
                    <x-icon name="receipt" class="h-4 w-4" /> Riwayat
                </a>
            @endcan
            @can('kantin.produk')
                <button wire:click="$set('showForm', true)" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Produk
                </button>
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
            <label class="field-search management-search-field" for="produk-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari produk</span>
                <input id="produk-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari produk…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
        </div>
    </div>

    @if ($showForm)
        @can('kantin.produk')
            <section class="card management-form-card" aria-labelledby="produk-form-title">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon blue"><x-icon name="store" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3 id="produk-form-title">Tambah / Ubah Produk</h3>
                            <p>Harga dan stok tampil di kasir setelah disimpan.</p>
                        </div>
                    </div>
                </div>

                <form wire:submit="save" class="form-grid management-form-grid">
                    <div class="form-group">
                        <label for="produk-nama">Nama <span class="required">*</span></label>
                        <input id="produk-nama" type="text" wire:model="nama" maxlength="150" class="field">
                        @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="produk-kategori">Kategori</label>
                        <input id="produk-kategori" type="text" wire:model="kategori" maxlength="100" class="field">
                        @error('kategori') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="produk-harga">Harga (Rp)</label>
                        <input id="produk-harga" type="number" wire:model="harga" min="0" class="field">
                        @error('harga') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="produk-stok">Stok</label>
                        <input id="produk-stok" type="number" wire:model="stok" min="0" class="field">
                        @error('stok') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-actions management-field-span">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">Simpan</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="$set('showForm', false)" class="btn btn-outline">Batal</button>
                    </div>
                </form>
            </section>
        @endcan
    @endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($items as $item)
            <article class="card {{ $item->aktif ? '' : 'opacity-60' }}" wire:key="produk-{{ $item->id }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold">{{ $item->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}@if($item->kategori) · {{ $item->kategori }}@endif</p>
                    </div>
                    <span class="badge shrink-0 {{ $item->stok <= 0 ? 'badge-red' : 'badge-gray' }}">
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
            </article>
        @empty
            <div class="empty-state col-span-full">
                <p>Belum ada produk.</p>
            </div>
        @endforelse
    </div>
</div>
