<div class="page-stack">
    @if (session('sukses'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kepegawaian / Data Induk</span>
            <h2 class="management-page-title">Data Guru &amp; Pegawai</h2>
            <p>Kelola identitas, bidang studi, status kepegawaian, dan penempatan wali kelas dalam satu direktori.</p>
        </div>
        <div class="page-actions">
            @if (\Illuminate\Support\Facades\Route::has('guru.impor') && auth()->user()?->can('guru.import'))
                <a href="{{ route('guru.impor') }}" class="btn btn-outline">
                    <x-icon name="upload" class="h-4 w-4" /> Impor Data
                </a>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('guru.ekspor.excel') && auth()->user()?->can('guru.export'))
                <a href="{{ route('guru.ekspor.excel', array_filter(['search' => $search ?: null, 'jenis' => $jenis ?: null, 'status' => $status ?: null])) }}" class="btn btn-outline">
                    <x-icon name="download" class="h-4 w-4" /> Ekspor Data
                </a>
            @endif
            @can('guru.create')
                <a href="{{ route('guru.create') }}" class="btn btn-primary">
                    <x-icon name="user-plus" class="h-4 w-4" /> Tambah Guru
                </a>
            @endcan
        </div>
    </div>

    <div class="stats-grid management-summary-stats">
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon blue"><x-icon name="users" class="h-5 w-5" /></span><span class="badge badge-blue">Terdaftar</span></div>
            <div class="stat-value">{{ number_format($stats['total'], 0, ',', '.') }}</div>
            <div class="stat-caption">Total guru &amp; pegawai</div>
            <div class="stat-footer"><span class="stat-note">Seluruh status kepegawaian</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon green"><x-icon name="user-check" class="h-5 w-5" /></span><span class="badge badge-green">Aktif</span></div>
            <div class="stat-value">{{ number_format($stats['aktif'], 0, ',', '.') }}</div>
            <div class="stat-caption">Status aktif</div>
            <div class="stat-footer"><span class="stat-note">Masih bertugas aktif</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon purple"><x-icon name="graduation-cap" class="h-5 w-5" /></span><span class="badge badge-purple">Pengajar</span></div>
            <div class="stat-value">{{ number_format($stats['pengajar'], 0, ',', '.') }}</div>
            <div class="stat-caption">Guru mapel &amp; kelas</div>
            <div class="stat-footer"><span class="stat-note">Memegang kelas atau mata pelajaran</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon cyan"><x-icon name="briefcase" class="h-5 w-5" /></span><span class="badge badge-gray">Tendik</span></div>
            <div class="stat-value">{{ number_format($stats['tendik'], 0, ',', '.') }}</div>
            <div class="stat-caption">Tenaga kependidikan</div>
            <div class="stat-footer"><span class="stat-note">Non-pengajar</span></div>
        </article>
        @can('guru.delete')
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
            <span><strong>Mode sampah aktif.</strong> {{ number_format($employees->total(), 0, ',', '.') }} data terhapus dapat dipulihkan atau dihapus permanen.</span>
        </div>
    @endif

    <section class="card management-table-card" aria-labelledby="employee-list-title">
        <div class="card-head management-table-heading">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="graduation-cap" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="employee-list-title">{{ $showTrashed ? 'Sampah Guru & Pegawai' : 'Direktori Guru & Pegawai' }}</h3>
                    <p>{{ number_format($employees->total(), 0, ',', '.') }} data sesuai filter</p>
                </div>
            </div>
            @can('guru.delete')
                <button type="button" wire:click="toggleTrashed" wire:loading.attr="disabled" wire:target="toggleTrashed" class="btn {{ $showTrashed ? 'btn-primary' : 'btn-soft' }} btn-sm">
                    <x-icon name="{{ $showTrashed ? 'arrow-left' : 'trash-2' }}" class="h-4 w-4" />
                    {{ $showTrashed ? 'Kembali ke Direktori' : 'Buka Sampah' }}
                    @if ($trashedCount > 0 && ! $showTrashed)<span class="management-count-badge">{{ $trashedCount }}</span>@endif
                </button>
            @endcan
        </div>

        <div class="table-toolbar management-toolbar">
            <div class="toolbar-left management-filter-group">
                <label class="field-search management-search-field" for="employee-search">
                    <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari guru atau pegawai</span>
                    <input id="employee-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau NIP/NUPTK..." autocomplete="off">
                    <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
                </label>
                <label class="management-filter-field" for="employee-jenis"><span class="sr-only">Filter jenis pegawai</span><select id="employee-jenis" wire:model.live="jenis" class="select-field"><option value="">Semua jenis</option><option value="guru_mapel">Guru Mapel</option><option value="guru_kelas">Guru Kelas</option><option value="tendik">Tendik</option></select></label>
                <label class="management-filter-field" for="employee-status"><span class="sr-only">Filter status aktif</span><select id="employee-status" wire:model.live="status" class="select-field"><option value="">Semua status</option><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select></label>
            </div>

            <div class="toolbar-right management-directory-actions">
                @if ($search !== '' || $jenis !== '' || $status !== '')
                    <button type="button" wire:click="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters" class="btn btn-outline btn-sm">
                        <x-icon name="undo-2" class="h-4 w-4" /> Reset filter
                    </button>
                @endif
            </div>
        </div>

        <div class="table-scroll" wire:loading.class="management-content-loading" wire:target="search,jenis,status,toggleTrashed,delete,restore,forceDelete">
            <table class="data-table management-data-table">
                <thead>
                    <tr>
                        <th scope="col">Nama Guru / Pegawai</th>
                        <th scope="col">NIP / NUPTK</th>
                        <th scope="col">Jenis</th>
                        <th scope="col">Telepon</th>
                        <th scope="col">Status</th>
                        <th scope="col">Akun Login</th>
                        <th scope="col" class="management-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $guru)
                        @php
                            $nameParts = preg_split('/\s+/', trim($guru->nama));
                            $initials = strtoupper(mb_substr($nameParts[0], 0, 1).(count($nameParts) > 1 ? mb_substr(end($nameParts), 0, 1) : ''));
                            $statusClass = $guru->status_aktif ? 'badge-green' : 'badge-gray';
                            $statusLabel = $guru->status_aktif ? 'Aktif' : 'Nonaktif';
                        @endphp
                        <tr wire:key="employee-directory-{{ $guru->id }}">
                            <td>
                                <div class="person-cell">
                                    @if ($guru->fotoUrl())
                                        <img src="{{ $guru->fotoUrl() }}" alt="Foto {{ $guru->nama }}" class="management-avatar-image">
                                    @else
                                        <span class="table-avatar">{{ $initials }}</span>
                                    @endif
                                    <span><strong>{{ $guru->nama }}</strong><small>{{ $guru->jabatan ?: 'Guru & Pegawai' }}</small></span>
                                </div>
                            </td>
                            <td><span class="management-reference">{{ $guru->nip_nuptk ?? '—' }}</span>@if ($guru->nik)<small class="management-cell-meta">NIK {{ $guru->nik }}</small>@endif</td>
                            <td>{{ $guru->labelJenis() }}</td>
                            <td>{{ $guru->phone ?: '—' }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td>
                                @if ($guru->user)
                                    <span class="font-mono text-xs">{{ $guru->user->username }}</span>
                                @else
                                    <span class="text-xs text-slate-500">Belum ada akun</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions management-row-actions">
                                    @if ($showTrashed)
                                        @can('guru.delete')
                                            <button type="button" wire:click="restore({{ $guru->id }})" wire:loading.attr="disabled" wire:target="restore({{ $guru->id }})" class="table-action management-action-success" aria-label="Pulihkan {{ $guru->nama }}" title="Pulihkan"><x-icon name="undo-2" class="h-4 w-4" /></button>
                                            <button type="button" wire:click="forceDelete({{ $guru->id }})" wire:confirm="Hapus permanen {{ $guru->nama }}? Data dan foto tidak dapat dikembalikan." wire:loading.attr="disabled" wire:target="forceDelete({{ $guru->id }})" class="table-action danger" aria-label="Hapus permanen {{ $guru->nama }}" title="Hapus permanen"><x-icon name="trash-2" class="h-4 w-4" /></button>
                                        @endcan
                                    @else
                                        @can('guru.view')<a href="{{ route('guru.show', $guru) }}" class="table-action" aria-label="Lihat {{ $guru->nama }}" title="Lihat detail"><x-icon name="eye" class="h-4 w-4" /></a>@endcan
                                        @can('guru.update')<a href="{{ route('guru.edit', $guru) }}" class="table-action" aria-label="Ubah {{ $guru->nama }}" title="Ubah data"><x-icon name="pencil" class="h-4 w-4" /></a>@endcan
                                        @can('guru.delete')<button type="button" wire:click="delete({{ $guru->id }})" wire:confirm="Pindahkan {{ $guru->nama }} ke sampah?" wire:loading.attr="disabled" wire:target="delete({{ $guru->id }})" class="table-action danger" aria-label="Hapus {{ $guru->nama }}" title="Pindahkan ke sampah"><x-icon name="trash-2" class="h-4 w-4" /></button>@endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-symbol"><x-icon name="users" class="h-7 w-7" /></div><h3>{{ $showTrashed ? 'Sampah masih kosong' : 'Guru/pegawai tidak ditemukan' }}</h3><p>{{ $showTrashed ? 'Belum ada data guru atau pegawai yang dipindahkan ke sampah.' : 'Ubah kata kunci atau filter untuk melihat data lainnya.' }}</p>@if (! $showTrashed && ($search !== '' || $jenis !== '' || $status !== ''))<button type="button" wire:click="resetFilters" class="btn btn-soft btn-sm">Reset filter</button>@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->hasPages())<div class="management-pagination">{{ $employees->links() }}</div>@endif
    </section>
</div>
