<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari judul tugas…"
                   aria-label="Cari tugas"
                   class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
        </div>
        @can('lms.tugas.create')
            <a href="{{ route('lms.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Buat Tugas
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Judul</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Mapel · Kelas</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Deadline</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Terkumpul</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('lms.show', $item) }}" class="font-semibold text-slate-800 dark:text-white hover:text-primary">{{ $item->judul }}</a>
                            @if ($item->isTerlambat())
                                <span class="ml-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-danger/10 text-danger">Lewat deadline</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->subject->nama }} · {{ $item->rombel->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->deadline->format('d M Y H:i') }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->submissions_count }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <a href="{{ route('lms.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Detail</a>
                            @can('lms.tugas.update')
                                <a href="{{ route('lms.edit', $item) }}" class="ml-2 text-xs font-semibold text-slate-500 hover:text-primary">Ubah</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada tugas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
