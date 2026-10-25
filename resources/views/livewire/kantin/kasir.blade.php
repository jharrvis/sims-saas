<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / Kantin</span>
            <h2 class="management-page-title">Kasir</h2>
            <p>Pilih produk, atur keranjang, lalu bayar. Siswa bersifat opsional.</p>
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif
    @error('keranjang')
        <div class="rounded-xl border border-danger/30 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-danger" role="alert">{{ $message }}</div>
    @enderror

    @if ($terakhir)
        <div class="card">
            <h3 class="font-bold text-success">Transaksi {{ $terakhir->kode }} berhasil</h3>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                Total Rp {{ number_format((float) $terakhir->total, 0, ',', '.') }} ·
                Bayar Rp {{ number_format((float) $terakhir->bayar, 0, ',', '.') }} ·
                Kembali Rp {{ number_format((float) $terakhir->kembalian, 0, ',', '.') }}
            </p>
            <ul class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                @foreach ($terakhir->items as $it)
                    <li>{{ $it->qty }}× {{ $it->nama }} — Rp {{ number_format((float) $it->subtotal, 0, ',', '.') }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2">
            <label class="field-search management-search-field mb-4" for="kasir-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari produk</span>
                <input id="kasir-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari produk…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach ($products as $p)
                    <button wire:click="tambah({{ $p->id }})" @disabled($p->stok <= 0) wire:key="kasir-produk-{{ $p->id }}"
                            class="card text-left hover:border-primary disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="block font-semibold text-sm">{{ $p->nama }}</span>
                        <span class="block text-xs text-slate-500 mt-0.5">Rp {{ number_format((float) $p->harga, 0, ',', '.') }} · stok {{ $p->stok }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="card h-fit">
            <h3 class="font-bold mb-3">Keranjang</h3>

            <div class="mb-3">
                <input type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Siswa (opsional)…"
                       aria-label="Cari siswa"
                       class="field mb-1.5">
                <select wire:model="student_id" aria-label="Pilih siswa" class="select-field w-full">
                    <option value="">— Umum —</option>
                    @foreach ($siswaList as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                    @endforeach
                </select>
            </div>

            @forelse ($detail as $d)
                <div wire:key="kasir-item-{{ $d['product']->id }}" class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <span class="block text-sm font-semibold">{{ $d['product']->nama }}</span>
                        <span class="text-xs text-slate-500">Rp {{ number_format($d['subtotal'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button wire:click="kurang({{ $d['product']->id }})" class="w-11 h-11 rounded-lg bg-slate-100 dark:bg-slate-700 text-sm font-bold" aria-label="Kurangi">−</button>
                        <span class="text-sm font-bold w-6 text-center">{{ $d['qty'] }}</span>
                        <button wire:click="tambah({{ $d['product']->id }})" class="w-11 h-11 rounded-lg bg-slate-100 dark:bg-slate-700 text-sm font-bold" aria-label="Tambah">+</button>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Keranjang kosong.</p>
            @endforelse

            <div class="flex items-center justify-between mt-4 mb-3">
                <span class="font-semibold text-slate-600 dark:text-slate-300 text-sm">Total</span>
                <span class="font-bold text-lg">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <div class="form-group">
                <label for="kasir-bayar">Bayar (Rp)</label>
                <input id="kasir-bayar" type="number" wire:model="bayar" min="0" class="field mb-3">
                @error('bayar') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <button wire:click="checkout" @disabled(empty($detail))
                    class="btn btn-primary w-full" wire:loading.attr="disabled" wire:target="checkout">
                <span wire:loading.remove wire:target="checkout">Bayar</span>
                <span wire:loading wire:target="checkout">Memproses...</span>
            </button>
        </div>
    </div>
</div>
