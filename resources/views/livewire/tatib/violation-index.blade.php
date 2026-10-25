<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Tata Tertib</span>
            <h2 class="management-page-title">Pelanggaran</h2>
            <p>Catatan pelanggaran siswa beserta poin dan tindak lanjutnya.</p>
        </div>
        <div class="page-actions">
            @can('tatib.create')
                <a href="{{ route('tatib.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Catat Pelanggaran
                </a>
            @endcan
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    @if ($rekap->isNotEmpty())
        <div class="card">
            <h2 class="text-sm font-bold mb-2">Poin Tertinggi</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($rekap as $r)
                    <span class="badge badge-gray">
                        <strong>{{ $r->nama }}</strong> · {{ $r->total_poin }} poin ({{ $r->jumlah }}×)
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="pelanggaran-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari pelanggaran</span>
                <input id="pelanggaran-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari siswa…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <label class="management-filter-field" for="pelanggaran-kategori"><span class="sr-only">Filter kategori</span>
                <select id="pelanggaran-kategori" wire:model.live="kategori" class="select-field">
                    <option value="">Semua kategori</option>
                    @foreach (\App\Models\ViolationType::KATEGORI as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            @can('tatib.create')
                <a href="{{ route('tatib.jenis') }}" class="text-xs font-semibold text-slate-500 hover:text-primary whitespace-nowrap">Kelola Jenis</a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Siswa</th>
                        <th scope="col">Pelanggaran</th>
                        <th scope="col">Poin</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="pelanggaran-{{ $item->id }}">
                            <td class="text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $item->tanggal->format('d M Y') }}</td>
                            <td>
                                <span class="font-semibold">{{ $item->student->nama }}</span>
                                <span class="text-xs text-slate-500">· {{ $item->student->nis }}</span>
                            </td>
                            <td>
                                <span class="text-slate-700 dark:text-slate-300">{{ $item->type->nama }}</span>
                                <span class="badge ml-1 {{ $item->type->kategori === 'berat' ? 'badge-red' : ($item->type->kategori === 'sedang' ? 'badge-orange' : 'badge-gray') }}">
                                    {{ \App\Models\ViolationType::KATEGORI[$item->type->kategori] }}
                                </span>
                                @if ($item->keterangan)
                                    <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($item->keterangan, 80) }}</p>
                                @endif
                            </td>
                            <td class="font-bold text-danger">+{{ $item->type->poin }}</td>
                            <td class="text-right whitespace-nowrap">
                                @can('tatib.update')
                                    <a href="{{ route('tatib.edit', $item) }}" class="text-xs font-semibold text-primary hover:underline">Ubah</a>
                                @endcan
                                @can('tatib.delete')
                                    <button wire:click="destroy({{ $item->id }})" wire:confirm="Hapus catatan ini?" class="ml-2 text-xs font-semibold text-danger hover:underline">Hapus</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">Belum ada catatan pelanggaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
