<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <input type="date" wire:model.live="tanggal" aria-label="Tanggal"
               class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
        <div class="rounded-lg bg-primary/10 px-4 py-2.5 text-sm font-bold text-primary">
            Omzet: Rp {{ number_format((float) $omzet, 0, ',', '.') }}
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Waktu</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Kode</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Item</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->tgl->format('H:i') }}</td>
                        <td class="px-4 py-2.5 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $item->kode }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">
                            @foreach ($item->items as $it)
                                <span class="block">{{ $it->qty }}× {{ $it->nama }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Tidak ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
