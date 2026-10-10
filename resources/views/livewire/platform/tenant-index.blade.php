<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <h1 class="text-2xl font-extrabold">Tenant</h1>
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama / NPSN…"
                   aria-label="Cari tenant"
                   class="rounded-lg border border-slate-200 bg-white py-2.5 px-4 text-sm outline-none focus:border-primary">
            <select wire:model.live="status" aria-label="Filter status"
                    class="rounded-lg border border-slate-200 bg-white py-2.5 px-3 text-sm outline-none focus:border-primary">
                <option value="">Semua status</option>
                @foreach (['trialing', 'active', 'past_due', 'suspended', 'cancelled'] as $st)
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
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tenants as $t)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('platform.tenants.show', $t->id) }}" class="font-semibold text-primary hover:underline">{{ $t->nama }}</a>
                            <span class="block text-xs text-slate-400">{{ $t->jenjang }}@if($t->npsn) · NPSN {{ $t->npsn }}@endif</span>
                        </td>
                        <td class="px-4 py-2.5 text-xs">{{ $t->subscription?->plan?->nama ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full
                                {{ $t->status_langganan === 'active' ? 'bg-success/10 text-success' : '' }}
                                {{ $t->status_langganan === 'trialing' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ in_array($t->status_langganan, ['suspended', 'past_due']) ? 'bg-danger/10 text-danger' : '' }}
                                {{ in_array($t->status_langganan, ['cancelled']) ? 'bg-slate-100 text-slate-500' : '' }}">
                                {{ str_replace('_', ' ', $t->status_langganan) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            @if ($t->status_langganan !== 'suspended')
                                <button wire:click="suspend({{ $t->id }})" wire:confirm="Suspend tenant {{ $t->nama }}? Akses tulis mereka akan diblokir."
                                        class="text-xs font-semibold text-danger hover:underline">Suspend</button>
                            @else
                                <button wire:click="activate({{ $t->id }})" wire:confirm="Aktifkan kembali {{ $t->nama }}?"
                                        class="text-xs font-semibold text-success hover:underline">Aktifkan</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Tidak ada tenant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $tenants->links() }}</div>
</div>
