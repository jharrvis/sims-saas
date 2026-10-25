<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / UKS</span>
            <h2 class="management-page-title">Obat UKS</h2>
            <p>Stok obat UKS beserta pemakaiannya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('uks.index') }}" class="back-link">← Kunjungan UKS</a>
            @can('uks.obat')
                <button wire:click="$set('showForm', true)" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Obat
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
            <label class="field-search management-search-field" for="obat-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari obat</span>
                <input id="obat-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari obat…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
        </div>
    </div>

    @if ($showForm)
        @can('uks.obat')
            <section class="card management-form-card" aria-labelledby="obat-form-title">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon blue"><x-icon name="pill" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3 id="obat-form-title">Tambah / Ubah Obat</h3>
                            <p>Stok minimum dipakai untuk menandai stok rendah.</p>
                        </div>
                    </div>
                </div>

                <form wire:submit="save" class="form-grid management-form-grid">
                    <div class="form-group">
                        <label for="obat-nama">Nama <span class="required">*</span></label>
                        <input id="obat-nama" type="text" wire:model="nama" maxlength="150" class="field">
                        @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="obat-satuan">Satuan</label>
                        <input id="obat-satuan" type="text" wire:model="satuan" maxlength="30" class="field">
                        @error('satuan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="obat-stok">Stok</label>
                        <input id="obat-stok" type="number" wire:model="stok" min="0" class="field">
                        @error('stok') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="obat-stok-minimum">Stok Minimum</label>
                        <input id="obat-stok-minimum" type="number" wire:model="stok_minimum" min="0" class="field">
                        @error('stok_minimum') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
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
            <article class="card" wire:key="obat-{{ $item->id }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold">{{ $item->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Satuan: {{ $item->satuan }}</p>
                    </div>
                    <span class="badge shrink-0 {{ $item->isStokRendah() ? 'badge-red' : 'badge-green' }}">
                        {{ $item->stok }}
                    </span>
                </div>
                @if ($item->isStokRendah())
                    <p class="text-xs text-danger mt-1">Stok rendah (minimum {{ $item->stok_minimum }})</p>
                @endif
                @can('uks.obat')
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center gap-2">
                        <input type="number" wire:model="pakai_jumlah.{{ $item->id }}" min="1" max="{{ $item->stok }}" placeholder="Jumlah"
                               aria-label="Jumlah pemakaian" class="field w-24">
                        <button wire:click="pakai({{ $item->id }})" class="text-xs font-semibold text-primary hover:underline">Catat Pakai</button>
                    </div>
                @endcan
            </article>
        @empty
            <div class="empty-state col-span-full">
                <p>Belum ada obat.</p>
            </div>
        @endforelse
    </div>
</div>
