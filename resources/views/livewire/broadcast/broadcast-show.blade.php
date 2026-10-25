<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('broadcast.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Broadcast
            </a>
            <span class="eyebrow">Informasi &amp; Sistem / Broadcast</span>
            <h2 class="management-page-title">{{ $broadcast->judul }}</h2>
            <p>
                {{ \App\Models\Broadcast::CHANNEL[$broadcast->channel] }} · {{ \App\Models\Broadcast::TARGET[$broadcast->target_type] }}
                @if ($broadcast->dijadwalkan_pada) · Jadwal {{ $broadcast->dijadwalkan_pada->format('d M Y H:i') }} @endif
            </p>
        </div>
        <div class="page-actions">
            @if ($broadcast->isSiapKirim())
                @can('broadcast.kirim')
                    <button wire:click="kirimSekarang" wire:confirm="Kirim broadcast ke semua penerima sekarang?"
                            class="btn btn-success">
                        Kirim Sekarang
                    </button>
                @endcan
            @endif
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <section class="card" aria-labelledby="broadcast-pesan-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="send" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="broadcast-pesan-title">Isi Pesan</h3>
                    <p>Pesan yang dikirim kepada penerima.</p>
                </div>
            </div>
        </div>
        <div class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $broadcast->pesan }}</div>
    </section>

    <section class="card" aria-labelledby="broadcast-log-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon green"><x-icon name="scroll-text" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="broadcast-log-title">Log Pengiriman ({{ $logs->total() }})</h3>
                    <p>Status per penerima beserta galat bila ada.</p>
                </div>
            </div>
        </div>

        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Penerima</th>
                        <th scope="col">Status</th>
                        <th scope="col">Dikirim</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr wire:key="broadcast-log-{{ $log->id }}">
                            <td>
                                {{ $log->penerima }}
                                @if ($log->error) <span class="block text-xs text-danger">{{ $log->error }}</span> @endif
                            </td>
                            <td>
                                <span class="badge {{ $log->status === 'terkirim' ? 'badge-green' : 'badge-red' }}">
                                    {{ $log->status === 'terkirim' ? 'Terkirim' : 'Gagal' }}
                                </span>
                            </td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $log->dikirim_pada?->format('d M Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-slate-500">Belum ada pengiriman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-5">{{ $logs->links() }}</div>
</div>
