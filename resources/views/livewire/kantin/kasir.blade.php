<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif
    @error('keranjang') <div class="mb-4 rounded-lg bg-danger/10 text-danger px-4 py-2.5 text-sm">{{ $message }}</div> @enderror

    @if ($terakhir)
        <div class="mb-5 rounded-xl border border-success/30 bg-success/10 p-5">
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
            <div class="relative mb-4">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari produk…"
                       aria-label="Cari produk"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach ($products as $p)
                    <button wire:click="tambah({{ $p->id }})" @disabled($p->stok <= 0)
                            class="text-left rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-3 hover:border-primary disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="block font-semibold text-sm text-slate-800 dark:text-white">{{ $p->nama }}</span>
                        <span class="block text-xs text-slate-500 mt-0.5">Rp {{ number_format((float) $p->harga, 0, ',', '.') }} · stok {{ $p->stok }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 h-fit">
            <h3 class="font-bold text-slate-800 dark:text-white mb-3">Keranjang</h3>

            <div class="mb-3">
                <input type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Siswa (opsional)…"
                       aria-label="Cari siswa"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white mb-1.5">
                <select wire:model="student_id" aria-label="Pilih siswa"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm dark:text-white">
                    <option value="">— Umum —</option>
                    @foreach ($siswaList as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                    @endforeach
                </select>
            </div>

            @forelse ($detail as $d)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <span class="block text-sm font-semibold text-slate-800 dark:text-white">{{ $d['product']->nama }}</span>
                        <span class="text-xs text-slate-500">Rp {{ number_format($d['subtotal'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button wire:click="kurang({{ $d['product']->id }})" class="w-6 h-6 rounded bg-slate-100 dark:bg-slate-700 text-sm font-bold">−</button>
                        <span class="text-sm font-bold w-6 text-center">{{ $d['qty'] }}</span>
                        <button wire:click="tambah({{ $d['product']->id }})" class="w-6 h-6 rounded bg-slate-100 dark:bg-slate-700 text-sm font-bold">+</button>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Keranjang kosong.</p>
            @endforelse

            <div class="flex items-center justify-between mt-4 mb-3">
                <span class="font-semibold text-slate-600 dark:text-slate-300 text-sm">Total</span>
                <span class="font-bold text-lg text-slate-800 dark:text-white">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Bayar (Rp)</label>
            <input type="number" wire:model="bayar" min="0"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white mb-3">
            @error('bayar') <p class="text-xs text-danger mb-2">{{ $message }}</p> @enderror

            <button wire:click="checkout" @disabled(empty($detail))
                    class="w-full rounded-lg bg-primary py-2.5 text-sm font-semibold text-white hover:bg-primary/90 disabled:opacity-50">
                Bayar
            </button>
        </div>
    </div>
</div>
