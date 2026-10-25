<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / Inventaris</span>
            <h2 class="management-page-title">Data Aset</h2>
            <p>Inventaris sekolah beserta kategori, lokasi, dan kondisinya.</p>
        </div>
        <div class="page-actions">
            @can('aset.create')
                <a href="{{ route('aset.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Aset
                </a>
            @endcan
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="aset-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari aset</span>
                <input id="aset-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari kode / nama…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <label class="management-filter-field" for="aset-kategori"><span class="sr-only">Filter kategori</span>
                <select id="aset-kategori" wire:model.live="kategori" class="select-field">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->nama }}</option>
                    @endforeach
                </select>
            </label>
            <label class="management-filter-field" for="aset-kondisi"><span class="sr-only">Filter kondisi</span>
                <select id="aset-kondisi" wire:model.live="kondisi" class="select-field">
                    <option value="">Semua kondisi</option>
                    @foreach (\App\Models\Asset::KONDISI as $key => $label)
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
                        <th scope="col">Kode</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Lokasi</th>
                        <th scope="col">Kondisi</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="aset-{{ $item->id }}">
                            <td class="font-mono text-xs text-slate-600 dark:text-slate-300">{{ $item->kode }}</td>
                            <td class="font-semibold">{{ $item->nama }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->category->nama }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->lokasi ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $item->kondisi === 'baik' ? 'badge-green' : ($item->kondisi === 'hilang' ? 'badge-red' : 'badge-orange') }}">
                                    {{ \App\Models\Asset::KONDISI[$item->kondisi] }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('aset.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-500">Belum ada aset.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
