<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama siswa…"
                       aria-label="Cari kunjungan"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <select wire:model.live="rujukan" aria-label="Filter rujukan"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua</option>
                @foreach (\App\Models\HealthVisit::RUJUKAN as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            @can('uks.obat')
                <a href="{{ route('uks.obat') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary">
                    <i data-lucide="pill" class="w-4 h-4"></i> Obat
                </a>
            @endcan
            @can('uks.create')
                <a href="{{ route('uks.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                    <i data-lucide="plus" class="w-4 h-4"></i> Catat Kunjungan
                </a>
            @endcan
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Waktu</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Siswa</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Keluhan</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Rujukan</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->tgl->format('d M Y H:i') }}</td>
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ $item->student->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($item->keluhan, 60) }}</td>
                        <td class="px-4 py-2.5">
                            @if ($item->rujukan !== 'tidak')
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-danger/10 text-danger">{{ \App\Models\HealthVisit::RUJUKAN[$item->rujukan] }}</span>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            @can('uks.update')
                                <a href="{{ route('uks.edit', $item) }}" class="text-xs font-semibold text-primary hover:underline">Ubah</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada kunjungan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
