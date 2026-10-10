<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <select wire:model.live="status" aria-label="Filter status"
                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
            <option value="">Semua status</option>
            @foreach (\App\Models\Broadcast::STATUS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        @can('broadcast.create')
            <a href="{{ route('broadcast.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Buat Broadcast
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Judul</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Channel · Target</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Jadwal</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ $item->judul }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">
                            {{ \App\Models\Broadcast::CHANNEL[$item->channel] }} · {{ \App\Models\Broadcast::TARGET[$item->target_type] }}
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->dijadwalkan_pada?->format('d M Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $item->status === 'terkirim' ? 'bg-success/10 text-success' : ($item->status === 'terjadwal' ? 'bg-warning/15 text-amber-600' : ($item->status === 'gagal' ? 'bg-danger/10 text-danger' : 'bg-slate-100 text-slate-500 dark:bg-slate-800')) }}">
                                {{ \App\Models\Broadcast::STATUS[$item->status] }} ({{ $item->logs_count }})
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            <a href="{{ route('broadcast.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada broadcast.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
