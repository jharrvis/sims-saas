<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Informasi &amp; Sistem / Broadcast</span>
            <h2 class="management-page-title">Broadcast</h2>
            <p>Pesan massal terjadwal atau langsung ke penerima yang dipilih.</p>
        </div>
        <div class="page-actions">
            @can('broadcast.create')
                <a href="{{ route('broadcast.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Buat Broadcast
                </a>
            @endcan
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="management-filter-field" for="broadcast-status"><span class="sr-only">Filter status</span>
                <select id="broadcast-status" wire:model.live="status" class="select-field">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Broadcast::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Channel · Target</th>
                        <th scope="col">Jadwal</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="broadcast-{{ $item->id }}">
                            <td class="font-semibold">{{ $item->judul }}</td>
                            <td class="text-slate-600 dark:text-slate-300">
                                {{ \App\Models\Broadcast::CHANNEL[$item->channel] }} · {{ \App\Models\Broadcast::TARGET[$item->target_type] }}
                            </td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->dijadwalkan_pada?->format('d M Y H:i') ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $item->status === 'terkirim' ? 'badge-green' : ($item->status === 'terjadwal' ? 'badge-orange' : ($item->status === 'gagal' ? 'badge-red' : 'badge-gray')) }}">
                                    {{ \App\Models\Broadcast::STATUS[$item->status] }} ({{ $item->logs_count }})
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('broadcast.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">Belum ada broadcast.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
