<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Langganan</span>
            <h2 class="management-page-title">Langganan Saya</h2>
            <p>Paket aktif sekolah beserta tagihan dan status pembayarannya.</p>
        </div>
    </div>

    @if (session('error'))
        <div class="rounded-xl border border-danger/30 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-danger" role="alert">{{ session('error') }}</div>
    @endif

    <section class="card" aria-labelledby="langganan-paket-title">
        <h2 id="langganan-paket-title" class="font-bold mb-3">Paket Aktif</h2>
        @if ($subscription)
            <dl class="text-sm space-y-1.5">
                <div class="flex justify-between"><dt class="text-slate-500">Paket</dt><dd class="font-semibold">{{ $subscription->plan?->nama }} ({{ $subscription->periode }})</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd class="font-semibold capitalize">{{ str_replace('_', ' ', $subscription->status) }}</dd></div>
                @if ($subscription->status === 'trial' && $subscription->trial_berakhir_pada)
                    <div class="flex justify-between"><dt class="text-slate-500">Trial berakhir</dt><dd class="font-semibold text-amber-600">{{ $subscription->trial_berakhir_pada->format('d M Y') }}</dd></div>
                @endif
                @if ($subscription->periode_berakhir_pada)
                    <div class="flex justify-between"><dt class="text-slate-500">Periode berakhir</dt><dd class="font-semibold">{{ $subscription->periode_berakhir_pada->format('d M Y') }}</dd></div>
                @endif
            </dl>
        @else
            <p class="text-sm text-slate-400">Belum ada langganan.</p>
        @endif
    </section>

    <section class="card" aria-labelledby="langganan-tagihan-title">
        <h2 id="langganan-tagihan-title" class="font-bold mb-3">Tagihan</h2>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Nomor</th>
                        <th scope="col">Periode</th>
                        <th scope="col">Total</th>
                        <th scope="col">Jatuh Tempo</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $inv)
                        <tr wire:key="tagihan-{{ $inv->id }}">
                            <td class="font-mono text-xs">{{ $inv->nomor }}</td>
                            <td class="text-xs">{{ $inv->periode_dari?->format('M Y') }}</td>
                            <td class="font-semibold">Rp{{ number_format($inv->total, 0, ',', '.') }}</td>
                            <td class="text-xs">{{ $inv->jatuh_tempo?->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $inv->status === 'paid' ? 'badge-green' : ($inv->status === 'overdue' ? 'badge-red' : 'badge-orange') }}">
                                    {{ $inv->status === 'paid' ? 'Lunas' : ($inv->status === 'overdue' ? 'Jatuh tempo' : 'Menunggu bayar') }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if (in_array($inv->status, ['issued', 'overdue']) && auth()->user()->can('langganan.bayar'))
                                    <button wire:click="bayar({{ $inv->id }})" wire:loading.attr="disabled" class="btn btn-sm btn-primary" wire:target="bayar">
                                        Bayar
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-500">Belum ada tagihan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <p class="text-xs text-slate-400 mt-3">Pembayaran diproses aman via Midtrans (VA bank, QRIS, e-wallet, kartu).</p>
</div>
