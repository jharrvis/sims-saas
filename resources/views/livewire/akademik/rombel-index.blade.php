<div class="page-stack">
    @if (session('sukses'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite" data-flash-alert>
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / Data Kelas</span>
            <h2 class="management-page-title">Data Kelas</h2>
            <p>Kelola rombel, wali kelas, dan jumlah siswa per kelas dalam satu direktori.</p>
        </div>
        <div class="page-actions">
            @if (\Illuminate\Support\Facades\Route::has('kelas.kenaikan') && auth()->user()?->can('kelas.promote'))
                <a href="{{ route('kelas.kenaikan') }}" class="btn btn-outline">
                    <x-icon name="arrow-up-right" class="h-4 w-4" /> Kenaikan Kelas
                </a>
            @endif
            @can('kelas.create')
                <a href="{{ route('kelas.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Kelas
                </a>
            @endcan
        </div>
    </div>

    <div class="stats-grid management-summary-stats">
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon blue"><x-icon name="school" class="h-5 w-5" /></span><span class="badge badge-blue">Terdaftar</span></div>
            <div class="stat-value">{{ number_format($stats['total'], 0, ',', '.') }}</div>
            <div class="stat-caption">Total kelas</div>
            <div class="stat-footer"><span class="stat-note">{{ $statsScope }}</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon green"><x-icon name="users" class="h-5 w-5" /></span><span class="badge badge-green">Siswa</span></div>
            <div class="stat-value">{{ number_format($stats['siswa'], 0, ',', '.') }}</div>
            <div class="stat-caption">Siswa aktif di kelas</div>
            <div class="stat-footer"><span class="stat-note">Siswa unik, penempatan aktif</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon orange"><x-icon name="user-x" class="h-5 w-5" /></span><span class="badge badge-orange">Tanpa wali</span></div>
            <div class="stat-value">{{ number_format($stats['tanpaWali'], 0, ',', '.') }}</div>
            <div class="stat-caption">Kelas tanpa wali kelas</div>
            <div class="stat-footer"><span class="stat-note">Perlu penunjukan wali</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon purple"><x-icon name="check-circle" class="h-5 w-5" /></span><span class="badge badge-purple">Penuh</span></div>
            <div class="stat-value">{{ number_format($stats['penuh'], 0, ',', '.') }}</div>
            <div class="stat-caption">Kelas mencapai kapasitas</div>
            <div class="stat-footer"><span class="stat-note">Siswa &ge; kapasitas</span></div>
        </article>
    </div>

    <section class="card management-table-card" aria-labelledby="rombel-list-title">
        <div class="card-head management-table-heading">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="school" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="rombel-list-title">Direktori Kelas</h3>
                    <p>{{ number_format($rombels->total(), 0, ',', '.') }} data sesuai filter</p>
                </div>
            </div>
        </div>

        <div class="table-toolbar management-toolbar">
            <div class="toolbar-left management-filter-group">
                <label class="field-search management-search-field" for="rombel-search">
                    <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari kelas</span>
                    <input id="rombel-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama kelas..." autocomplete="off">
                    <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
                </label>
                <label class="management-filter-field" for="rombel-tingkat"><span class="sr-only">Filter tingkat</span><select id="rombel-tingkat" wire:model.live="tingkat" class="select-field"><option value="">Semua tingkat</option>@foreach ($tingkatList as $t)<option value="{{ $t }}">Tingkat {{ $t }}</option>@endforeach</select></label>
                <label class="management-filter-field" for="rombel-tahun"><span class="sr-only">Filter tahun ajaran</span><select id="rombel-tahun" wire:model.live="academicYearId" class="select-field"><option value="">Semua tahun ajaran</option>@foreach ($tahunAjaran as $ta)<option value="{{ $ta->id }}">{{ $ta->nama }}</option>@endforeach</select></label>
            </div>

            <div class="toolbar-right management-directory-actions">
                @if ($search !== '' || $tingkat !== '' || $academicYearId !== $tahunAktifId)
                    <button type="button" wire:click="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters" class="btn btn-outline btn-sm">
                        <x-icon name="undo-2" class="h-4 w-4" /> Reset filter
                    </button>
                @endif
            </div>
        </div>

        <div class="table-scroll" wire:loading.class="management-content-loading" wire:target="search,tingkat,academicYearId,resetFilters">
            <table class="data-table management-data-table">
                <thead>
                    <tr>
                        <th scope="col">Kelas</th>
                        <th scope="col">Tingkat</th>
                        <th scope="col">Tahun Ajaran</th>
                        <th scope="col">Wali Kelas</th>
                        <th scope="col">Siswa</th>
                        <th scope="col" class="management-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rombels as $rombel)
                        <tr wire:key="rombel-directory-{{ $rombel->id }}">
                            <td><strong>{{ $rombel->nama }}</strong></td>
                            <td>{{ $rombel->tingkat }}</td>
                            <td>{{ $rombel->academicYear?->nama ?? '—' }}</td>
                            <td>{{ $rombel->waliKelas?->nama ?? '—' }}</td>
                            <td><span class="management-reference">{{ $rombel->siswa_count }}/{{ $rombel->kapasitas }}</span></td>
                            <td>
                                <div class="row-actions management-row-actions">
                                    @can('kelas.view')<a href="{{ route('kelas.show', $rombel) }}" class="table-action" aria-label="Lihat {{ $rombel->nama }}" title="Lihat siswa"><x-icon name="eye" class="h-4 w-4" /></a>@endcan
                                    @can('kelas.update')<a href="{{ route('kelas.edit', $rombel) }}" class="table-action" aria-label="Ubah {{ $rombel->nama }}" title="Ubah data"><x-icon name="pencil" class="h-4 w-4" /></a>@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-symbol"><x-icon name="school" class="h-7 w-7" /></div><h3>Kelas tidak ditemukan</h3><p>Ubah kata kunci atau filter untuk melihat kelas lainnya.</p>@if ($search !== '' || $tingkat !== '' || $academicYearId !== $tahunAktifId)<button type="button" wire:click="resetFilters" class="btn btn-soft btn-sm">Reset filter</button>@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rombels->hasPages())<div class="management-pagination">{{ $rombels->links() }}</div>@endif
    </section>
</div>
