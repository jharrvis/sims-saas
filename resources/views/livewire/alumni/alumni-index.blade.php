<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Alumni</span>
            <h2 class="management-page-title">Alumni</h2>
            <p>Data alumni beserta tahun lulus dan karier terakhirnya.</p>
        </div>
        <div class="page-actions">
            @can('alumni.create')
                <a href="{{ route('alumni.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Alumni
                </a>
            @endcan
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="alumni-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari alumni</span>
                <input id="alumni-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <label class="management-filter-field" for="alumni-tahun"><span class="sr-only">Filter tahun lulus</span>
                <select id="alumni-tahun" wire:model.live="tahun" class="select-field">
                    <option value="">Semua tahun</option>
                    @foreach ($tahuns as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </label>
            <label class="management-filter-field" for="alumni-status"><span class="sr-only">Filter status karier</span>
                <select id="alumni-status" wire:model.live="status" class="select-field">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\AlumniCareer::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Lulus</th>
                        <th scope="col">Karier Terakhir</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php $karier = $item->karierTerakhir(); @endphp
                        <tr wire:key="alumni-{{ $item->id }}">
                            <td class="font-semibold">{{ $item->nama }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->tahun_lulus ?? '—' }}</td>
                            <td>
                                @if ($karier)
                                    <span class="badge badge-blue">{{ \App\Models\AlumniCareer::STATUS[$karier->status] }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $karier->institusi }}{{ $karier->jurusan_posisi ? ' · '.$karier->jurusan_posisi : '' }}</span>
                                @else
                                    <span class="text-xs text-slate-400">Belum ada data</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('alumni.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-500">Belum ada data alumni.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
