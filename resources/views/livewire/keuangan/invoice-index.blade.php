<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Keuangan / Tagihan</span>
            <h2 class="management-page-title">Daftar Tagihan</h2>
            <p>Ringkasan dan daftar tagihan siswa, termasuk tunggakan dan tagihan yang belum lunas.</p>
        </div>
    </div>

    @if (session('generated'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('generated') }}</span>
        </div>
    @endif

    <div class="stats-grid management-summary-stats">
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon blue"><x-icon name="wallet" class="h-5 w-5" /></span><span class="badge badge-blue">Tagihan</span></div>
            <div class="stat-value">Rp {{ number_format($summary['total_tagihan'], 0, ',', '.') }}</div>
            <div class="stat-caption">Total Tagihan</div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon green"><x-icon name="check-circle" class="h-5 w-5" /></span><span class="badge badge-green">Masuk</span></div>
            <div class="stat-value">Rp {{ number_format($summary['terkumpul'], 0, ',', '.') }}</div>
            <div class="stat-caption">Terkumpul</div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon orange"><x-icon name="clock" class="h-5 w-5" /></span><span class="badge badge-orange">Sisa</span></div>
            <div class="stat-value">Rp {{ number_format($summary['tunggakan'], 0, ',', '.') }}</div>
            <div class="stat-caption">Tunggakan</div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon red"><x-icon name="alert-triangle" class="h-5 w-5" /></span><span class="badge badge-red">Perhatian</span></div>
            <div class="stat-value">{{ $summary['jumlah_unpaid'] }} tagihan</div>
            <div class="stat-caption">Belum Lunas</div>
        </article>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="tagihan-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari tagihan</span>
                <input id="tagihan-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari siswa…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <label class="management-filter-field" for="tagihan-status"><span class="sr-only">Filter status</span>
                <select id="tagihan-status" wire:model.live="status" class="select-field">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Invoice::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        @can('keuangan.tagihan.generate')
            <form wire:submit="generate" class="toolbar-right management-directory-actions">
                <label class="management-filter-field" for="tagihan-jenis"><span class="sr-only">Jenis tagihan</span>
                    <select id="tagihan-jenis" wire:model="feeTypeId" required class="select-field">
                        <option value="">— Jenis —</option>
                        @foreach ($feeTypes as $f)
                            <option value="{{ $f->id }}">{{ $f->nama }} — Rp {{ number_format($f->nominal, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </label>
                <input type="text" wire:model="periodeLabel" placeholder="Periode (2026-07)" required aria-label="Periode"
                       class="field w-36">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Generate
                </button>
            </form>
        @endcan
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">No. Invoice</th>
                        <th scope="col">Siswa</th>
                        <th scope="col">Periode</th>
                        <th scope="col" class="text-right">Nominal</th>
                        <th scope="col" class="text-right">Sisa</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $inv)
                        <tr wire:key="invoice-{{ $inv->id }}">
                            <td class="font-mono text-xs">{{ $inv->no_invoice }}</td>
                            <td>
                                <div class="font-medium">{{ $inv->student->nama }}</div>
                                <div class="text-xs text-slate-500">{{ $inv->feeType->nama }}</div>
                            </td>
                            <td>{{ $inv->periode_label }}</td>
                            <td class="text-right">Rp {{ number_format($inv->nominal, 0, ',', '.') }}</td>
                            <td class="text-right font-semibold {{ $inv->sisa() > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($inv->sisa(), 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge badge-blue">
                                    {{ $inv->labelStatus() }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('keuangan.show', $inv) }}" class="btn btn-sm btn-outline">
                                    Bayar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-sm text-slate-500">Tidak ada tagihan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
