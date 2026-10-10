<div>
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-danger/10 text-danger px-4 py-2.5 text-sm">{{ session('error') }}</div>
    @endif

    <h1 class="text-2xl font-extrabold mb-5">Langganan Saya</h1>

    <div class="rounded-2xl bg-white p-5 shadow-sm mb-6">
        <h2 class="font-bold mb-3">Paket Aktif</h2>
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
    </div>

    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <h2 class="font-bold px-5 pt-5 pb-3">Tagihan</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-5 py-2.5 font-semibold text-slate-600">Nomor</th>
                    <th class="px-5 py-2.5 font-semibold text-slate-600">Periode</th>
                    <th class="px-5 py-2.5 font-semibold text-slate-600">Total</th>
                    <th class="px-5 py-2.5 font-semibold text-slate-600">Jatuh Tempo</th>
                    <th class="px-5 py-2.5 font-semibold text-slate-600">Status</th>
                    <th class="px-5 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $inv)
                    <tr class="border-t border-slate-100">
                        <td class="px-5 py-2.5 font-mono text-xs">{{ $inv->nomor }}</td>
                        <td class="px-5 py-2.5 text-xs">{{ $inv->periode_dari?->format('M Y') }}</td>
                        <td class="px-5 py-2.5 font-semibold">Rp{{ number_format($inv->total, 0, ',', '.') }}</td>
                        <td class="px-5 py-2.5 text-xs">{{ $inv->jatuh_tempo?->format('d M Y') }}</td>
                        <td class="px-5 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full
                                {{ $inv->status === 'paid' ? 'bg-success/10 text-success' : '' }}
                                {{ $inv->status === 'overdue' ? 'bg-danger/10 text-danger' : '' }}
                                {{ in_array($inv->status, ['issued']) ? 'bg-amber-100 text-amber-700' : '' }}">
                                {{ $inv->status === 'paid' ? 'Lunas' : ($inv->status === 'overdue' ? 'Jatuh tempo' : 'Menunggu bayar') }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5 text-right">
                            @if (in_array($inv->status, ['issued', 'overdue']) && auth()->user()->can('langganan.bayar'))
                                <button wire:click="bayar({{ $inv->id }})" wire:loading.attr="disabled"
                                        class="rounded-lg bg-primary px-4 py-1.5 text-xs font-semibold text-white disabled:opacity-50">
                                    Bayar
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada tagihan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="text-xs text-slate-400 mt-3">Pembayaran diproses aman via Midtrans (VA bank, QRIS, e-wallet, kartu).</p>
</div>
