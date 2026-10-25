<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / UKS</span>
            <h2 class="management-page-title">Kunjungan UKS</h2>
            <p>Kunjungan siswa ke UKS beserta keluhan dan rujukannya.</p>
        </div>
        <div class="page-actions">
            @can('uks.obat')
                <a href="{{ route('uks.obat') }}" class="btn btn-outline">
                    <x-icon name="pill" class="h-4 w-4" /> Obat
                </a>
            @endcan
            @can('uks.create')
                <a href="{{ route('uks.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Catat Kunjungan
                </a>
            @endcan
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="kunjungan-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari kunjungan</span>
                <input id="kunjungan-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama siswa…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <label class="management-filter-field" for="kunjungan-rujukan"><span class="sr-only">Filter rujukan</span>
                <select id="kunjungan-rujukan" wire:model.live="rujukan" class="select-field">
                    <option value="">Semua</option>
                    @foreach (\App\Models\HealthVisit::RUJUKAN as $key => $label)
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
                        <th scope="col">Waktu</th>
                        <th scope="col">Siswa</th>
                        <th scope="col">Keluhan</th>
                        <th scope="col">Rujukan</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="kunjungan-{{ $item->id }}">
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->tgl->format('d M Y H:i') }}</td>
                            <td class="font-semibold">{{ $item->student->nama }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($item->keluhan, 60) }}</td>
                            <td>
                                @if ($item->rujukan !== 'tidak')
                                    <span class="badge badge-red">{{ \App\Models\HealthVisit::RUJUKAN[$item->rujukan] }}</span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('uks.update')
                                    <a href="{{ route('uks.edit', $item) }}" class="text-xs font-semibold text-primary hover:underline">Ubah</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">Belum ada kunjungan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
