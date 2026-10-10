<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5">
        <a href="{{ route('uks.index') }}" class="text-sm text-slate-500 hover:text-primary">← Kunjungan UKS</a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari obat…"
                   aria-label="Cari obat"
                   class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
        </div>
        @can('uks.obat')
            <button wire:click="$set('showForm', true)" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Obat
            </button>
        @endcan
    </div>

    @if ($showForm)
        @can('uks.obat')
            <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5 grid sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama <span class="text-danger">*</span></label>
                    <input type="text" wire:model="nama" maxlength="150"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Satuan</label>
                    <input type="text" wire:model="satuan" maxlength="30"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Stok</label>
                    <input type="number" wire:model="stok" min="0"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Stok Minimum</label>
                    <input type="number" wire:model="stok_minimum" min="0"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
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
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold text-slate-800 dark:text-white">{{ $item->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Satuan: {{ $item->satuan }}</p>
                    </div>
                    <span class="shrink-0 text-sm font-bold px-2.5 py-1 rounded-lg {{ $item->isStokRendah() ? 'bg-danger/10 text-danger' : 'bg-success/10 text-success' }}">
                        {{ $item->stok }}
                    </span>
                </div>
                @if ($item->isStokRendah())
                    <p class="text-xs text-danger mt-1">Stok rendah (minimum {{ $item->stok_minimum }})</p>
                @endif
                @can('uks.obat')
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center gap-2">
                        <input type="number" wire:model="pakai_jumlah.{{ $item->id }}" min="1" max="{{ $item->stok }}" placeholder="Jumlah"
                               aria-label="Jumlah pemakaian"
                               class="w-24 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                        <button wire:click="pakai({{ $item->id }})"
                                class="text-xs font-semibold text-primary hover:underline">Catat Pakai</button>
                    </div>
                @endcan
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-sm text-slate-500">
                Belum ada obat.
            </div>
        @endforelse
    </div>
</div>
