<div class="max-w-4xl mx-auto space-y-5">
    @if (session('paid'))
        <div class="rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('paid') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-mono text-xs text-slate-500">{{ $invoice->no_invoice }}</p>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white mt-1">{{ $invoice->student->nama }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ $invoice->feeType->nama }} · Periode {{ $invoice->periode_label }}</p>
            </div>
            <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-primary/10 text-primary">
                {{ $invoice->labelStatus() }}
            </span>
        </div>

        <div class="grid grid-cols-3 gap-4 mt-5 text-sm">
            <div>
                <p class="text-xs text-slate-500">Nominal</p>
                <p class="font-bold">Rp {{ number_format($invoice->nominal, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Dibayar</p>
                <p class="font-bold text-success">Rp {{ number_format($invoice->dibayar, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Sisa</p>
                <p class="font-bold text-danger">Rp {{ number_format($invoice->sisa(), 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    @if ($invoice->sisa() > 0)
        @can('keuangan.bayar.create')
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
                <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Catat Pembayaran</h2>
                <form wire:submit="pay" class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nominal" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nominal (Rp) <span class="text-danger">*</span></label>
                        <input id="nominal" type="number" wire:model="nominal" min="1" max="{{ $invoice->sisa() }}" required
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm dark:text-white">
                        @error('nominal') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tgl_bayar" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Tanggal Bayar <span class="text-danger">*</span></label>
                        <input id="tgl_bayar" type="date" wire:model="tgl_bayar" required
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm dark:text-white">
                    </div>
                    <div>
                        <label for="metode" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Metode <span class="text-danger">*</span></label>
                        <select id="metode" wire:model="metode" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm dark:text-white dark:bg-boxdark">
                            @foreach (\App\Models\Payment::METODES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="catatan" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Catatan</label>
                        <input id="catatan" type="text" wire:model="catatan"
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm dark:text-white">
                    </div>
                    <div class="sm:col-span-2 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-lg bg-success text-sm font-medium text-white hover:opacity-90 disabled:opacity-60">
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        @endcan
    @endif

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Riwayat Pembayaran</h2>
        @if ($invoice->payments->isEmpty())
            <p class="text-sm text-slate-500">Belum ada pembayaran.</p>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach ($invoice->payments as $p)
                    <li class="py-3 flex items-center justify-between">
                        <div>
                            <p class="font-mono text-xs text-slate-500">{{ $p->no_kuitansi }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $p->tgl_bayar->format('d M Y') }} · {{ \App\Models\Payment::METODES[$p->metode] }}</p>
                            @if ($p->catatan)<p class="text-xs text-slate-500">{{ $p->catatan }}</p>@endif
                        </div>
                        <p class="font-bold text-sm text-success">Rp {{ number_format($p->nominal, 0, ',', '.') }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <a href="{{ route('keuangan.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
        ← Kembali ke daftar tagihan
    </a>
</div>
