<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / LMS</span>
            <h2 class="management-page-title">Tugas</h2>
            <p>Tugas kelas beserta mapel, deadline, dan jumlah pengumpulannya.</p>
        </div>
        <div class="page-actions">
            @can('lms.tugas.create')
                <a href="{{ route('lms.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Buat Tugas
                </a>
            @endcan
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="tugas-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari tugas</span>
                <input id="tugas-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari judul tugas…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
        </div>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Mapel · Kelas</th>
                        <th scope="col">Deadline</th>
                        <th scope="col">Terkumpul</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="tugas-{{ $item->id }}">
                            <td>
                                <a href="{{ route('lms.show', $item) }}" class="font-semibold hover:text-primary">{{ $item->judul }}</a>
                                @if ($item->isTerlambat())
                                    <span class="badge badge-red ml-1">Lewat deadline</span>
                                @endif
                            </td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->subject->nama }} · {{ $item->rombel->nama }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->deadline->format('d M Y H:i') }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->submissions_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('lms.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Detail</a>
                                @can('lms.tugas.update')
                                    <a href="{{ route('lms.edit', $item) }}" class="ml-2 text-xs font-semibold text-slate-500 hover:text-primary">Ubah</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">Belum ada tugas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
