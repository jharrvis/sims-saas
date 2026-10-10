<div>
    <div class="flex justify-end mb-5">
        @can('survei.create')
            <a href="{{ route('survei.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Buat Survei
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Judul</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Target</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Status</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Responden</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5">
                            <span class="block font-semibold text-slate-800 dark:text-white">{{ $item->judul }}</span>
                            <span class="text-xs text-slate-400">{{ $item->questions_count }} pertanyaan</span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ \App\Models\Survey::TARGET[$item->target_type] }}</td>
                        <td class="px-4 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $item->status === 'aktif' ? 'bg-success/10 text-success' : ($item->status === 'tutup' ? 'bg-danger/10 text-danger' : 'bg-slate-100 text-slate-500 dark:bg-slate-800') }}">
                                {{ \App\Models\Survey::STATUS[$item->status] }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->responses_count }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            @if ($item->isDapatDiisi())
                                @can('survei.isi')
                                    <a href="{{ route('survei.isi', $item) }}" class="text-xs font-semibold text-success hover:underline">Isi</a>
                                @endcan
                            @endif
                            @can('survei.hasil')
                                <a href="{{ route('survei.hasil', $item) }}" class="ml-2 text-xs font-semibold text-primary hover:underline">Hasil</a>
                            @endcan
                            @can('survei.update')
                                <a href="{{ route('survei.edit', $item) }}" class="ml-2 text-xs font-semibold text-slate-500 hover:text-primary">Ubah</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada survei.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
