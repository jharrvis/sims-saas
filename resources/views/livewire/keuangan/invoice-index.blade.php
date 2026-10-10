<div>
    @if (session('generated'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('generated') }}</div>
    @endif

    <div class="grid sm:grid-cols-4 gap-4 mb-5">
        @php
            $cards = [
                ['Total Tagihan', $summary['total_tagihan']],
                ['Terkumpul', $summary['terkumpul']],
                ['Tunggakan', $summary['tunggakan']],
            ];
        @endphp
        @foreach ($cards as [$label, $nilai])
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-4">
                <p class="text-xs text-slate-500">{{ $label }}</p>
                <p class="text-lg font-bold text-slate-800 dark:text-white mt-1">Rp {{ number_format($nilai, 0, ',', '.') }}</p>
            </div>
        @endforeach
        <div class="rounded-xl border border-danger/30 bg-red-50 dark:bg-red-900/20 p-4">
            <p class="text-xs text-slate-500">Belum Lunas</p>
            <p class="text-lg font-bold text-danger mt-1">{{ $summary['jumlah_unpaid'] }} tagihan</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari siswa…"
                       aria-label="Cari tagihan"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <select wire:model.live="status" aria-label="Filter status"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua status</option>
                @foreach (\App\Models\Invoice::STATUS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @can('keuangan.tagihan.generate')
            <form wire:submit="generate" class="flex items-center gap-2">
                <select wire:model="feeTypeId" aria-label="Jenis tagihan" required
                        class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm dark:text-white">
                    <option value="">— Jenis —</option>
                    @foreach ($feeTypes as $f)
                        <option value="{{ $f->id }}">{{ $f->nama }} — Rp {{ number_format($f->nominal, 0, ',', '.') }}</option>
                    @endforeach
                </select>
                <input type="text" wire:model="periodeLabel" placeholder="Periode (2026-07)" required aria-label="Periode"
                       class="w-36 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm dark:text-white">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                    <i data-lucide="plus" class="w-4 h-4"></i> Generate
                </button>
            </form>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">No. Invoice</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Siswa</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Periode</th>
                        <th class="py-3 px-5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Nominal</th>
                        <th class="py-3 px-5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Sisa</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="py-3 px-5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $inv)
                        <tr class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-5 font-mono text-xs">{{ $inv->no_invoice }}</td>
                            <td class="py-3 px-5">
                                <div class="font-medium text-sm text-slate-800 dark:text-white">{{ $inv->student->nama }}</div>
                                <div class="text-xs text-slate-500">{{ $inv->feeType->nama }}</div>
                            </td>
                            <td class="py-3 px-5 text-sm">{{ $inv->periode_label }}</td>
                            <td class="py-3 px-5 text-right text-sm">Rp {{ number_format($inv->nominal, 0, ',', '.') }}</td>
                            <td class="py-3 px-5 text-right text-sm font-semibold {{ $inv->sisa() > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($inv->sisa(), 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-5">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                    {{ $inv->labelStatus() }}
                                </span>
                            </td>
                            <td class="py-3 px-5 text-right">
                                <a href="{{ route('keuangan.show', $inv) }}" class="inline-flex px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary/20">
                                    Bayar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-sm text-slate-500">Tidak ada tagihan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
