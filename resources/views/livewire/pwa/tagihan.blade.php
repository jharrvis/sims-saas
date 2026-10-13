<div class="px-4 pt-4 pb-28">
    @if ($totalAktif > 0)
        {{-- Tagihan aktif (design ortu/keuangan.html: kartu tagihan mendatang) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5 border-l-4 border-l-warning">
            <div class="flex justify-between items-start gap-3 mb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Total Tagihan Aktif</h3>
                    <p class="text-xs text-warning font-medium mt-0.5">Bayar via transfer ke rekening sekolah, lalu konfirmasi ke bendahara.</p>
                </div>
                <p class="text-xl font-extrabold text-slate-800 whitespace-nowrap">Rp{{ number_format($totalAktif, 0, ',', '.') }}</p>
            </div>
        </div>
    @elseif ($items->count() > 0)
        {{-- Semua lunas (design ortu/keuangan.html: kartu status) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-800 text-sm">Status Tagihan</h3>
                <span class="text-xs bg-success/10 text-success px-2 py-1 rounded-full font-bold">Lunas</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-success/5 rounded-xl border border-success/20">
                <x-icon name="circle-check-big" class="w-8 h-8 text-success shrink-0" />
                <div>
                    <p class="font-bold text-slate-800 text-sm">Semua tagihan sudah lunas</p>
                    <p class="text-xs text-slate-500">Tidak ada tagihan aktif saat ini.</p>
                </div>
            </div>
        </div>
    @endif

    <h3 class="font-bold text-slate-800 text-sm mb-3">Riwayat Tagihan</h3>
    <div class="space-y-2">
        @forelse ($items as $t)
            <div class="bg-white rounded-xl p-3.5 shadow-sm border border-slate-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg {{ $t->status === 'paid' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }} flex items-center justify-center flex-shrink-0">
                    <x-icon :name="$t->status === 'paid' ? 'check' : 'receipt'" class="w-4 h-4" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $t->feeType?->nama ?? 'Tagihan' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $t->periode_label }}{{ $t->jatuh_tempo ? ' · jatuh tempo '.$t->jatuh_tempo->format('d M Y') : '' }}</p>
                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full mt-1
                        {{ $t->status === 'paid' ? 'bg-success/10 text-success' : ($t->status === 'overdue' ? 'bg-danger/10 text-danger' : 'bg-warning/15 text-amber-600') }}">
                        {{ $t->labelStatus() }}
                    </span>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold {{ in_array($t->status, ['unpaid', 'partial', 'overdue'], true) ? 'text-danger' : 'text-slate-700' }}">
                        Sisa Rp{{ number_format($t->sisa(), 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400">dari Rp{{ number_format($t->nominal, 0, ',', '.') }}</p>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto">
                    <x-icon name="wallet" class="w-8 h-8 text-slate-300" />
                </div>
                <p class="text-sm text-slate-400 mt-3">Belum ada tagihan.</p>
            </div>
        @endforelse
    </div>
</div>
