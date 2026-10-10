<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <h1 class="text-2xl font-extrabold">Tagihan Langganan</h1>
        <select wire:model.live="status" aria-label="Filter status"
                class="rounded-lg border border-slate-200 bg-white py-2.5 px-3 text-sm outline-none focus:border-primary">
            <option value="">Semua status</option>
            @foreach (['draft', 'issued', 'paid', 'overdue', 'void'] as $st)
                <option value="{{ $st }}">{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm mb-6">
        <h2 class="font-bold mb-3">Terbitkan Tagihan Manual</h2>
        <div class="flex flex-wrap gap-2 items-end">
            <div class="min-w-64">
                <label class="text-xs text-slate-500">Langganan</label>
                <select wire:model="subscription_id" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    <option value="">— Pilih —</option>
                    @foreach ($subs as $s)
                        <option value="{{ $s->id }}">{{ $names[$s->school_id] ?? '—' }} · {{ $s->plan?->nama }} ({{ $s->periode }})</option>
                    @endforeach
                </select>
                @error('subscription_id') <p class="text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-slate-500">Diskon (Rp)</label>
                <input type="number" wire:model="diskon" min="0" class="block w-36 rounded-lg border border-slate-200 py-2 px-3 text-sm">
            </div>
            <button wire:click="issue" wire:confirm="Terbitkan tagihan?"
                    class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Terbitkan</button>
        </div>
        <p class="text-xs text-slate-400 mt-2">Pembayaran otomatis via Midtrans menyusul di langkah onboarding.</p>
    </div>

    <div class="rounded-xl bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Nomor</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Sekolah</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Total</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Jatuh Tempo</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $inv)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5 font-mono text-xs">{{ $inv->nomor }}</td>
                        <td class="px-4 py-2.5">{{ $inv->nama_sekolah_snapshot }}</td>
                        <td class="px-4 py-2.5 font-semibold">Rp{{ number_format($inv->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-xs">{{ $inv->jatuh_tempo?->format('d M Y') }}</td>
                        <td class="px-4 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full
                                {{ $inv->status === 'paid' ? 'bg-success/10 text-success' : '' }}
                                {{ $inv->status === 'overdue' ? 'bg-danger/10 text-danger' : '' }}
                                {{ in_array($inv->status, ['issued', 'draft']) ? 'bg-amber-100 text-amber-700' : '' }}">
                                {{ $inv->status }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            @if (in_array($inv->status, ['issued', 'overdue']))
                                <button wire:click="markPaid({{ $inv->id }})" wire:confirm="Tandai {{ $inv->nomor }} lunas?"
                                        class="text-xs font-semibold text-success hover:underline">Tandai Lunas</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada tagihan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $invoices->links() }}</div>
</div>
