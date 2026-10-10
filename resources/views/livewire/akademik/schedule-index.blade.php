<div>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-5">
        <select wire:model.live="rombelId" aria-label="Pilih kelas"
                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
            <option value="">— Pilih kelas —</option>
            @foreach ($rombels as $r)
                <option value="{{ $r->id }}">{{ $r->nama }}</option>
            @endforeach
        </select>
        @can('jadwal.create')
            <a href="{{ route('jadwal.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Jadwal
            </a>
        @endcan
    </div>

    @if ($rombelId && ! empty($grid))
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-fixed min-w-[720px]">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                            <th class="py-3 px-2 text-sm font-semibold text-slate-600 dark:text-slate-300 w-20">Jam</th>
                            @foreach (array_keys($grid) as $hari)
                                <th class="py-3 px-2 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ ucfirst($hari) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (array_values($grid['senin']) as $jamKe => $ignore)
                            <tr class="border-b border-slate-100 dark:border-slate-700/50">
                                <td class="py-2 px-2 text-center text-xs font-medium text-slate-500">{{ $jamKe + 1 }}</td>
                                @foreach ($grid as $hari => $kolom)
                                    @php $entry = $kolom[$jamKe + 1] ?? null; @endphp
                                    <td class="py-2 px-2 align-top">
                                        @if ($entry)
                                            <div class="rounded-lg bg-primary/10 border border-primary/20 p-2">
                                                <p class="text-xs font-bold text-primary">{{ $entry->subject->kode }}</p>
                                                <p class="text-[11px] text-slate-600 dark:text-slate-300 truncate">{{ $entry->subject->nama }}</p>
                                                <p class="text-[11px] text-slate-500 truncate">{{ $entry->employee->nama }}</p>
                                                @if ($entry->ruang)
                                                    <p class="text-[11px] text-slate-400">{{ $entry->ruang }}</p>
                                                @endif
                                                @can('jadwal.delete')
                                                    <button wire:click="deleteEntry({{ $entry->id }})" wire:confirm="Hapus jadwal ini?"
                                                            class="mt-1 text-[11px] text-danger hover:underline">Hapus</button>
                                                @endcan
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($rombelId)
        <p class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada jadwal untuk kelas ini.</p>
    @endif
</div>
