<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5">
        <a href="{{ route('broadcast.index') }}" class="text-sm text-slate-500 hover:text-primary">← Semua Broadcast</a>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $broadcast->judul }}</h2>
                <p class="text-sm text-slate-500 mt-1">
                    {{ \App\Models\Broadcast::CHANNEL[$broadcast->channel] }} · {{ \App\Models\Broadcast::TARGET[$broadcast->target_type] }}
                    @if ($broadcast->dijadwalkan_pada) · Jadwal {{ $broadcast->dijadwalkan_pada->format('d M Y H:i') }} @endif
                </p>
                <div class="mt-3 text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $broadcast->pesan }}</div>
            </div>
            @if ($broadcast->isSiapKirim())
                @can('broadcast.kirim')
                    <button wire:click="kirimSekarang" wire:confirm="Kirim broadcast ke semua penerima sekarang?"
                            class="shrink-0 rounded-lg bg-success px-4 py-2.5 text-sm font-semibold text-white hover:bg-success/90">
                        Kirim Sekarang
                    </button>
                @endcan
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 font-semibold text-slate-800 dark:text-white text-sm">
            Log Pengiriman ({{ $logs->total() }})
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Penerima</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Status</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Dikirim</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 text-slate-700 dark:text-slate-200">
                            {{ $log->penerima }}
                            @if ($log->error) <span class="block text-xs text-danger">{{ $log->error }}</span> @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $log->status === 'terkirim' ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                {{ $log->status === 'terkirim' ? 'Terkirim' : 'Gagal' }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $log->dikirim_pada?->format('d M Y H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Belum ada pengiriman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $logs->links() }}</div>
</div>
