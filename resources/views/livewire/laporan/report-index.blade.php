<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Informasi &amp; Sistem / Laporan</span>
            <h2 class="management-page-title">Laporan</h2>
            <p>Pilih jenis laporan dan filter, lalu tampilkan atau ekspor ke CSV.</p>
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <section class="card management-form-card" aria-labelledby="laporan-filter-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="file-text" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="laporan-filter-title">Filter Laporan</h3>
                    <p>Filter berubah mengikuti jenis laporan yang dipilih.</p>
                </div>
            </div>
        </div>

        <div class="form-grid management-form-grid">
            <div class="form-group">
                <label for="type">Jenis Laporan</label>
                <select id="type" wire:model.live="type" class="select-field">
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if (in_array($type, ['siswa', 'guru']))
                <div class="form-group">
                    <label for="q">Cari nama</label>
                    <input id="q" type="search" wire:model="q" placeholder="Nama…" class="field">
                </div>
            @endif

            @if ($type === 'siswa')
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" wire:model="status" class="select-field">
                        <option value="">Semua</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                        <option value="lulus">Lulus</option>
                        <option value="pindah">Pindah</option>
                    </select>
                </div>
            @endif

            @if (in_array($type, ['mutasi', 'absensi']))
                <div class="form-group">
                    <label for="dari">Dari tanggal</label>
                    <input id="dari" type="date" wire:model="dari" class="field">
                </div>
                <div class="form-group">
                    <label for="sampai">Sampai tanggal</label>
                    <input id="sampai" type="date" wire:model="sampai" class="field">
                </div>
            @endif

            @if ($type === 'nilai')
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <select id="semester" wire:model="semesterId" class="select-field">
                        <option value="">Semua</option>
                        @foreach ($semesters as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3 mt-5">
            <button wire:click="generate" class="btn btn-primary">
                <x-icon name="bar-chart-3" class="h-4 w-4" /> Tampilkan
            </button>
            @can('laporan.export')
                <button wire:click="export" class="btn btn-outline">
                    <x-icon name="download" class="h-4 w-4" /> Ekspor CSV
                </button>
            @endcan
            <a href="{{ route('bantuan.show', 'laporan') }}" class="ml-auto text-xs text-slate-400 hover:text-primary inline-flex items-center gap-1">
                <x-icon name="circle-help" class="h-4 w-4" /> Bantuan
            </a>
        </div>
    </section>

    @if ($report)
        <div class="card">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            @foreach ($report['columns'] as $col)
                                <th scope="col">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['rows'] as $row)
                            <tr wire:key="laporan-{{ $loop->index }}">
                                @foreach ($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($report['columns']) }}" class="py-8 text-center text-slate-500">Tidak ada data untuk filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="empty-state">
            <p>Pilih jenis laporan lalu klik <strong>Tampilkan</strong>.</p>
        </div>
    @endif
</div>
