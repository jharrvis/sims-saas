<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <h1 class="text-2xl font-extrabold">Audit Log</h1>
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari aksi…"
               aria-label="Cari aksi"
               class="rounded-lg border border-slate-200 bg-white py-2.5 px-4 text-sm outline-none focus:border-primary">
    </div>

    <div class="rounded-xl bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Waktu</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Aksi</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Tenant</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">User</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $l)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5 text-xs text-slate-500 whitespace-nowrap">{{ $l->created_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-2.5 font-mono text-xs">{{ $l->aksi }}</td>
                        <td class="px-4 py-2.5 text-xs">{{ $l->school_id ? ($names[$l->school_id] ?? '#'.$l->school_id) : '—' }}</td>
                        <td class="px-4 py-2.5 text-xs">#{{ $l->user_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada log.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $logs->links() }}</div>
</div>
