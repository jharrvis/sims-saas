<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="relative w-full sm:max-w-xs">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari mapel…"
                   aria-label="Cari mata pelajaran"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary dark:text-white">
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Kode</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Kelompok</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">KKM</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjects as $s)
                        <tr class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-5 font-mono text-sm font-semibold text-primary">{{ $s->kode }}</td>
                            <td class="py-3 px-5 font-medium text-sm text-slate-800 dark:text-white">{{ $s->nama }}</td>
                            <td class="py-3 px-5 text-sm capitalize">{{ $s->kelompok }}</td>
                            <td class="py-3 px-5 text-sm">{{ $s->kkm_default }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                Tidak ada data mata pelajaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($subjects->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700">
                {{ $subjects->links() }}
            </div>
        @endif
    </div>
</div>
