<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / Kantin</span>
            <h2 class="management-page-title">Riwayat Transaksi</h2>
            <p>Transaksi kasir per tanggal beserta omzetnya.</p>
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="management-filter-field" for="transaksi-tanggal"><span class="sr-only">Tanggal</span>
                <input id="transaksi-tanggal" type="date" wire:model.live="tanggal" class="field">
            </label>
        </div>
        <div class="toolbar-right management-directory-actions">
            <span class="badge badge-blue">Omzet: Rp {{ number_format((float) $omzet, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Waktu</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Item</th>
                        <th scope="col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="transaksi-{{ $item->id }}">
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->tgl->format('H:i') }}</td>
                            <td class="font-mono text-xs text-slate-600 dark:text-slate-300">{{ $item->kode }}</td>
                            <td class="text-slate-600 dark:text-slate-300">
                                @foreach ($item->items as $it)
                                    <span class="block">{{ $it->qty }}× {{ $it->nama }}</span>
                                @endforeach
                            </td>
                            <td class="font-semibold">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-500">Tidak ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
