<div>
    @if ($totalAktif > 0)
        <div class="rounded-2xl bg-danger text-white p-4 mb-4">
            <p class="text-[11px] uppercase tracking-wide text-white/70">Total tagihan aktif</p>
            <p class="text-2xl font-extrabold">Rp{{ number_format($totalAktif, 0, ',', '.') }}</p>
            <p class="text-[11px] text-white/70 mt-1">Bayar via transfer ke rekening sekolah, lalu konfirmasi ke bendahara.</p>
        </div>
    @endif

    @forelse ($items as $t)
        <div class="rounded-2xl border border-slate-100 p-4 mb-2">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-bold text-sm">{{ $t->feeType?->nama ?? 'Tagihan' }}</p>
                    <p class="text-[11px] text-slate-400">{{ $t->periode_label }}{{ $t->jatuh_tempo ? ' · jatuh tempo '.$t->jatuh_tempo->format('d M Y') : '' }}</p>
                </div>
                <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-full
                    {{ $t->status === 'paid' ? 'bg-success/10 text-success' : ($t->status === 'overdue' ? 'bg-danger/10 text-danger' : 'bg-warning/15 text-amber-600') }}">
                    {{ $t->labelStatus() }}
                </span>
            </div>
            <div class="flex items-end justify-between mt-2">
                <p class="text-xs text-slate-400">Rp{{ number_format($t->nominal, 0, ',', '.') }}</p>
                <p class="text-sm font-extrabold {{ in_array($t->status, ['unpaid', 'partial', 'overdue'], true) ? 'text-danger' : 'text-slate-400' }}">
                    Sisa Rp{{ number_format($t->sisa(), 0, ',', '.') }}
                </p>
            </div>
        </div>
    @empty
        <div class="text-center py-12">
            <i data-lucide="wallet" class="w-10 h-10 mx-auto text-slate-200"></i>
            <p class="text-sm text-slate-400 mt-2">Belum ada tagihan.</p>
        </div>
    @endforelse
</div>
