<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / Jadwal</span>
            <h2 class="management-page-title">Jadwal Pelajaran</h2>
            <p>Pilih kelas untuk melihat jadwal per hari dan jam.</p>
        </div>
        <div class="page-actions">
            @can('jadwal.create')
                <a href="{{ route('jadwal.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Jadwal
                </a>
            @endcan
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="management-filter-field" for="jadwal-rombel"><span class="sr-only">Pilih kelas</span>
                <select id="jadwal-rombel" wire:model.live="rombelId" class="select-field">
                    <option value="">— Pilih kelas —</option>
                    @foreach ($rombels as $r)
                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    @if ($rombelId && ! empty($grid))
        <div class="card">
            <div class="table-scroll">
                <table class="data-table w-full table-fixed min-w-[720px]">
                    <thead>
                        <tr>
                            <th scope="col" class="w-20">Jam</th>
                            @foreach (array_keys($grid) as $hari)
                                <th scope="col">{{ ucfirst($hari) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (array_values($grid['senin']) as $jamKe => $ignore)
                            <tr>
                                <td class="text-center text-xs font-medium text-slate-500">{{ $jamKe + 1 }}</td>
                                @foreach ($grid as $hari => $kolom)
                                    @php $entry = $kolom[$jamKe + 1] ?? null; @endphp
                                    <td class="align-top">
                                        @if ($entry)
                                            <div wire:key="jadwal-entry-{{ $entry->id }}" class="rounded-lg bg-primary/10 border border-primary/20 p-2">
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
        <div class="empty-state">
            <p>Belum ada jadwal untuk kelas ini.</p>
        </div>
    @endif
</div>
