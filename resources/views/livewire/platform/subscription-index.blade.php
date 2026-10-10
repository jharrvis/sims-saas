<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <h1 class="text-2xl font-extrabold">Langganan</h1>
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari sekolah…"
                   aria-label="Cari sekolah"
                   class="rounded-lg border border-slate-200 bg-white py-2.5 px-4 text-sm outline-none focus:border-primary">
            <select wire:model.live="status" aria-label="Filter status"
                    class="rounded-lg border border-slate-200 bg-white py-2.5 px-3 text-sm outline-none focus:border-primary">
                <option value="">Semua status</option>
                @foreach (['trial', 'active', 'past_due', 'suspended', 'cancelled'] as $st)
                    <option value="{{ $st }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Sekolah</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Paket</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Status</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Berakhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subs as $s)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('platform.tenants.show', $s->school_id) }}" class="font-semibold text-primary hover:underline">
                                {{ $names[$s->school_id] ?? '—' }}
                            </a>
                        </td>
                        <td class="px-4 py-2.5">{{ $s->plan?->nama }} <span class="text-xs text-slate-400">({{ $s->periode }})</span></td>
                        <td class="px-4 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full
                                {{ $s->status === 'past_due' ? 'bg-danger/10 text-danger' : 'bg-slate-100 text-slate-600' }}">
                                {{ str_replace('_', ' ', $s->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-slate-500">
                            {{ $s->status === 'trial' ? $s->trial_berakhir_pada?->format('d M Y') : $s->periode_berakhir_pada?->format('d M Y') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Tidak ada langganan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $subs->links() }}</div>
</div>
