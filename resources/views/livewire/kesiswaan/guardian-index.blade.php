<div class="page-stack">
    @if (session('sukses'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Data Induk</span>
            <h2 class="management-page-title">Data Wali</h2>
            <p>Informasi orang tua, wali, kontak, dan relasi dengan siswa.</p>
        </div>
        <div class="page-actions">
            @can('wali-murid.create')
                <a href="{{ route('wali.create') }}" class="btn btn-primary">
                    <x-icon name="user-plus" class="h-4 w-4" /> Tambah Wali
                </a>
            @endcan
        </div>
    </div>

    <div class="stats-grid management-summary-stats">
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon blue"><x-icon name="users" class="h-5 w-5" /></span><span class="badge badge-blue">Terdaftar</span></div>
            <div class="stat-value">{{ number_format($stats['total'], 0, ',', '.') }}</div>
            <div class="stat-caption">Total wali murid</div>
            <div class="stat-footer"><span class="stat-note">Seluruh hubungan</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon green"><x-icon name="user" class="h-5 w-5" /></span><span class="badge badge-green">Ayah</span></div>
            <div class="stat-value">{{ number_format($stats['ayah'], 0, ',', '.') }}</div>
            <div class="stat-caption">Hubungan ayah</div>
            <div class="stat-footer"><span class="stat-note">Wali dengan hubungan ayah</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon purple"><x-icon name="user" class="h-5 w-5" /></span><span class="badge badge-purple">Ibu</span></div>
            <div class="stat-value">{{ number_format($stats['ibu'], 0, ',', '.') }}</div>
            <div class="stat-caption">Hubungan ibu</div>
            <div class="stat-footer"><span class="stat-note">Wali dengan hubungan ibu</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon cyan"><x-icon name="user" class="h-5 w-5" /></span><span class="badge badge-gray">Wali</span></div>
            <div class="stat-value">{{ number_format($stats['wali'], 0, ',', '.') }}</div>
            <div class="stat-caption">Hubungan wali</div>
            <div class="stat-footer"><span class="stat-note">Wali selain ayah dan ibu</span></div>
        </article>
        @can('wali-murid.delete')
            <article class="card stat-card">
                <div class="stat-top"><span class="mini-icon orange"><x-icon name="trash-2" class="h-5 w-5" /></span><span class="badge {{ $trashedCount > 0 ? 'badge-orange' : 'badge-gray' }}">Sampah</span></div>
                <div class="stat-value">{{ number_format($trashedCount, 0, ',', '.') }}</div>
                <div class="stat-caption">Data terhapus</div>
                <div class="stat-footer"><span class="stat-note">Masih dapat dipulihkan</span></div>
            </article>
        @endcan
    </div>

    @if ($showTrashed)
        <div class="management-alert management-alert-warning" role="status">
            <x-icon name="alert-triangle" class="h-5 w-5" />
            <span><strong>Mode sampah aktif.</strong> {{ number_format($guardians->total(), 0, ',', '.') }} data terhapus dapat dipulihkan atau dihapus permanen.</span>
        </div>
    @endif

    <section class="card management-table-card" aria-labelledby="guardian-list-title">
        <div class="card-head management-table-heading">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="user-round-check" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="guardian-list-title">{{ $showTrashed ? 'Sampah Wali Murid' : 'Direktori Wali Murid' }}</h3>
                    <p>{{ number_format($guardians->total(), 0, ',', '.') }} data sesuai filter</p>
                </div>
            </div>
            @can('wali-murid.delete')
                <button type="button" wire:click="toggleTrashed" wire:loading.attr="disabled" wire:target="toggleTrashed" class="btn {{ $showTrashed ? 'btn-primary' : 'btn-soft' }} btn-sm">
                    <x-icon name="{{ $showTrashed ? 'arrow-left' : 'trash-2' }}" class="h-4 w-4" />
                    {{ $showTrashed ? 'Kembali ke Direktori' : 'Buka Sampah' }}
                    @if ($trashedCount > 0 && ! $showTrashed)<span class="management-count-badge">{{ $trashedCount }}</span>@endif
                </button>
            @endcan
        </div>

        <div class="table-toolbar management-toolbar">
            <div class="toolbar-left management-filter-group">
                <label class="field-search management-search-field" for="guardian-search">
                    <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari wali murid</span>
                    <input id="guardian-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau nomor WA..." autocomplete="off">
                    <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
                </label>
                <label class="management-filter-field" for="guardian-hubungan"><span class="sr-only">Filter hubungan</span><select id="guardian-hubungan" wire:model.live="hubungan" class="select-field"><option value="">Semua hubungan</option><option value="ayah">Ayah</option><option value="ibu">Ibu</option><option value="wali">Wali</option></select></label>
            </div>

            <div class="toolbar-right management-directory-actions">
                @if ($search !== '' || $hubungan !== '')
                    <button type="button" wire:click="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters" class="btn btn-outline btn-sm">
                        <x-icon name="undo-2" class="h-4 w-4" /> Reset filter
                    </button>
                @endif
            </div>
        </div>

        <div class="table-scroll" wire:loading.class="management-content-loading" wire:target="search,hubungan,toggleTrashed,delete,restore,forceDelete">
            <table class="data-table management-data-table">
                <thead>
                    <tr>
                        <th scope="col">Nama Wali</th>
                        <th scope="col">Hubungan</th>
                        <th scope="col">Nomor WA</th>
                        <th scope="col">Pekerjaan</th>
                        <th scope="col">Siswa Tertaut</th>
                        <th scope="col" class="management-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guardians as $wali)
                        @php
                            $nameParts = preg_split('/\s+/', trim($wali->nama));
                            $initials = strtoupper(mb_substr($nameParts[0], 0, 1).(count($nameParts) > 1 ? mb_substr(end($nameParts), 0, 1) : ''));
                            $labelHubungan = match ($wali->hubungan) { 'ayah' => 'Ayah', 'ibu' => 'Ibu', default => 'Wali' };
                        @endphp
                        <tr wire:key="guardian-directory-{{ $wali->id }}">
                            <td>
                                <div class="person-cell">
                                    <span class="table-avatar">{{ $initials }}</span>
                                    <span><strong>{{ $wali->nama }}</strong><small>{{ $wali->email ?: 'Wali Murid' }}</small></span>
                                </div>
                            </td>
                            <td>{{ $labelHubungan }}</td>
                            <td>{{ $wali->no_wa }}</td>
                            <td>{{ $wali->pekerjaan ?: '—' }}</td>
                            <td>{{ number_format($wali->students_count, 0, ',', '.') }} siswa</td>
                            <td>
                                <div class="row-actions management-row-actions">
                                    @if ($showTrashed)
                                        @can('wali-murid.delete')
                                            <button type="button" wire:click="restore({{ $wali->id }})" wire:loading.attr="disabled" wire:target="restore({{ $wali->id }})" class="table-action management-action-success" aria-label="Pulihkan {{ $wali->nama }}" title="Pulihkan"><x-icon name="undo-2" class="h-4 w-4" /></button>
                                            <button type="button" wire:click="forceDelete({{ $wali->id }})" wire:confirm="Hapus permanen {{ $wali->nama }}? Data tidak dapat dikembalikan." wire:loading.attr="disabled" wire:target="forceDelete({{ $wali->id }})" class="table-action danger" aria-label="Hapus permanen {{ $wali->nama }}" title="Hapus permanen"><x-icon name="trash-2" class="h-4 w-4" /></button>
                                        @endcan
                                    @else
                                        @can('wali-murid.view')<a href="{{ route('wali.show', $wali) }}" class="table-action" aria-label="Lihat {{ $wali->nama }}" title="Lihat detail"><x-icon name="eye" class="h-4 w-4" /></a>@endcan
                                        @can('wali-murid.update')<a href="{{ route('wali.edit', $wali) }}" class="table-action" aria-label="Ubah {{ $wali->nama }}" title="Ubah data"><x-icon name="pencil" class="h-4 w-4" /></a>@endcan
                                        @can('wali-murid.delete')<button type="button" wire:click="delete({{ $wali->id }})" wire:confirm="Pindahkan {{ $wali->nama }} ke sampah?" wire:loading.attr="disabled" wire:target="delete({{ $wali->id }})" class="table-action danger" aria-label="Hapus {{ $wali->nama }}" title="Pindahkan ke sampah"><x-icon name="trash-2" class="h-4 w-4" /></button>@endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-symbol"><x-icon name="users" class="h-7 w-7" /></div><h3>{{ $showTrashed ? 'Sampah masih kosong' : 'Wali murid tidak ditemukan' }}</h3><p>{{ $showTrashed ? 'Belum ada data wali murid yang dipindahkan ke sampah.' : 'Ubah kata kunci atau filter untuk melihat data lainnya.' }}</p>@if (! $showTrashed && ($search !== '' || $hubungan !== ''))<button type="button" wire:click="resetFilters" class="btn btn-soft btn-sm">Reset filter</button>@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($guardians->hasPages())<div class="management-pagination">{{ $guardians->links() }}</div>@endif
    </section>
</div>
