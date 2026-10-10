<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama…"
                       aria-label="Cari alumni"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <select wire:model.live="tahun" aria-label="Filter tahun lulus"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua tahun</option>
                @foreach ($tahuns as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" aria-label="Filter status karier"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua status</option>
                @foreach (\App\Models\AlumniCareer::STATUS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @can('alumni.create')
            <a href="{{ route('alumni.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Alumni
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Nama</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Lulus</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Karier Terakhir</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php $karier = $item->karierTerakhir(); @endphp
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ $item->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->tahun_lulus ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            @if ($karier)
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-primary/10 text-primary">{{ \App\Models\AlumniCareer::STATUS[$karier->status] }}</span>
                                <span class="block text-xs text-slate-500 mt-0.5">{{ $karier->institusi }}{{ $karier->jurusan_posisi ? ' · '.$karier->jurusan_posisi : '' }}</span>
                            @else
                                <span class="text-xs text-slate-400">Belum ada data</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            <a href="{{ route('alumni.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada data alumni.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
