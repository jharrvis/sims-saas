<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('ppdb.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> PPDB
            </a>
            <span class="eyebrow">PPDB / Pendaftar</span>
            <h2 class="management-page-title">{{ $period->nama }}</h2>
            <p>Daftar pendaftar pada periode ini beserta status prosesnya.</p>
        </div>
        <div class="page-actions">
            @can('ppdb.pendaftar.create')
                <a href="{{ route('ppdb.pendaftar.create', ['period' => $period->id]) }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Daftarkan
                </a>
            @endcan
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="ppdb-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari pendaftar</span>
                <input id="ppdb-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama / no. daftar…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <label class="management-filter-field" for="ppdb-status"><span class="sr-only">Filter status</span>
                <select id="ppdb-status" wire:model.live="status" class="select-field">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\PpdbApplicant::STATUS as $key => $label)
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
                        <th scope="col">No. Pendaftaran</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Asal Sekolah</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applicants as $a)
                        <tr wire:key="ppdb-applicant-{{ $a->id }}">
                            <td class="font-mono text-xs">{{ $a->no_pendaftaran }}</td>
                            <td class="font-medium">{{ $a->nama }}</td>
                            <td class="text-slate-500">{{ $a->asal_sekolah ?? '—' }}</td>
                            <td>
                                <span class="badge badge-blue">
                                    {{ $a->labelStatus() }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('ppdb.pendaftar.show', $a) }}" class="btn btn-sm btn-outline">
                                    Proses
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-sm text-slate-500">Tidak ada pendaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($applicants->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700">{{ $applicants->links() }}</div>
        @endif
    </div>
</div>
