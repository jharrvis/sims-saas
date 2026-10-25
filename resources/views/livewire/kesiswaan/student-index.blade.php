<div class="page-stack">
    @if (session('sukses'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @php
        $totalStudents = (int) $statusCounts->sum();
        $activeStudents = (int) ($statusCounts['aktif'] ?? 0);
    @endphp

    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Data Induk</span>
            <h2 class="management-page-title">Data Siswa</h2>
            <p>Kelola identitas, status, penempatan kelas, dan administrasi siswa dalam satu direktori.</p>
        </div>
        <div class="page-actions">
            @if (\Illuminate\Support\Facades\Route::has('siswa.impor') && auth()->user()?->can('siswa.import'))
                <a href="{{ route('siswa.impor') }}" class="btn btn-outline">
                    <x-icon name="upload" class="h-4 w-4" /> Impor Data
                </a>
            @endif
            @can('siswa.create')
                <a href="{{ route('siswa.create') }}" class="btn btn-primary">
                    <x-icon name="user-plus" class="h-4 w-4" /> Tambah Siswa
                </a>
            @endcan
        </div>
    </div>

    <div class="stats-grid management-summary-stats">
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon blue"><x-icon name="users" class="h-5 w-5" /></span><span class="badge badge-blue">Terdaftar</span></div>
            <div class="stat-value">{{ number_format($totalStudents, 0, ',', '.') }}</div>
            <div class="stat-caption">Total siswa</div>
            <div class="stat-footer"><span class="stat-note">Seluruh status aktif dan arsip</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon green"><x-icon name="user-check" class="h-5 w-5" /></span><span class="badge badge-green">Aktif</span></div>
            <div class="stat-value">{{ number_format($activeStudents, 0, ',', '.') }}</div>
            <div class="stat-caption">Siswa aktif</div>
            <div class="stat-footer"><span class="stat-note">Mengikuti operasional sekolah</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon purple"><x-icon name="school" class="h-5 w-5" /></span><span class="badge badge-purple">Tahun aktif</span></div>
            <div class="stat-value">{{ number_format($rombels->count(), 0, ',', '.') }}</div>
            <div class="stat-caption">Rombel tersedia</div>
            <div class="stat-footer"><span class="stat-note">Tujuan penempatan siswa</span></div>
        </article>
        @can('siswa.delete')
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
            <span><strong>Mode sampah aktif.</strong> {{ number_format($students->total(), 0, ',', '.') }} data terhapus dapat dipulihkan atau dihapus permanen.</span>
        </div>
    @endif

    @if (count($selected) > 0)
        <section class="management-bulk-bar" aria-label="Aksi massal siswa">
            <div class="management-selection-count">
                <span class="mini-icon blue sm"><x-icon name="square-check-big" class="h-4 w-4" /></span>
                <span><strong>{{ count($selected) }} siswa dipilih</strong><small>Pilihan hanya berlaku pada halaman ini</small></span>
            </div>
            <div class="management-bulk-controls">
                <label class="sr-only" for="student-bulk-action">Aksi massal</label>
                <select id="student-bulk-action" wire:model.live="bulkAksi" class="select-field">
                    <option value="">Pilih aksi</option>
                    @if ($showTrashed)
                        <option value="pulihkan">Pulihkan</option>
                        <option value="hapus_permanen">Hapus permanen</option>
                    @else
                        @can('siswa.update')
                            <option value="status">Ubah status</option>
                            <option value="rombel">Pindah rombel</option>
                        @endcan
                        <option value="export">Export Excel</option>
                        @can('siswa.delete')
                            <option value="hapus">Pindahkan ke sampah</option>
                        @endcan
                    @endif
                </select>

                @if ($bulkAksi === 'status' && ! $showTrashed)
                    <label class="sr-only" for="student-bulk-status">Status baru</label>
                    <select id="student-bulk-status" wire:model="bulkStatus" class="select-field">
                        <option value="">Status baru</option>
                        <option value="aktif">Aktif</option><option value="pindah">Pindah</option><option value="lulus">Lulus</option><option value="keluar">Keluar</option><option value="do">DO</option>
                    </select>
                @endif

                @if ($bulkAksi === 'rombel' && ! $showTrashed)
                    <label class="sr-only" for="student-bulk-class">Rombel tujuan</label>
                    <select id="student-bulk-class" wire:model="bulkRombelId" class="select-field">
                        <option value="">Rombel tujuan</option>
                        @foreach ($rombels as $rombelOption)
                            <option value="{{ $rombelOption->id }}">{{ $rombelOption->nama }}</option>
                        @endforeach
                    </select>
                @endif

                <button type="button" wire:click="bulkTerapkan" wire:loading.attr="disabled" wire:target="bulkTerapkan"
                    @if (in_array($bulkAksi, ['hapus', 'hapus_permanen'], true)) wire:confirm="Yakin menjalankan aksi ini untuk {{ count($selected) }} siswa?" @endif
                    class="btn btn-primary">
                    <span wire:loading.remove wire:target="bulkTerapkan">Terapkan</span><span wire:loading wire:target="bulkTerapkan">Memproses...</span>
                </button>
            </div>
            @error('bulkAksi') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            @error('bulkStatus') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            @error('bulkRombelId') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
        </section>
    @endif

    <section class="card management-table-card" aria-labelledby="student-list-title">
        <div class="card-head management-table-heading">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="graduation-cap" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="student-list-title">{{ $showTrashed ? 'Sampah Siswa' : 'Direktori Siswa' }}</h3>
                    <p>{{ number_format($students->total(), 0, ',', '.') }} data sesuai filter</p>
                </div>
            </div>
            @can('siswa.delete')
                <button type="button" wire:click="toggleTrashed" wire:loading.attr="disabled" wire:target="toggleTrashed" class="btn {{ $showTrashed ? 'btn-primary' : 'btn-soft' }} btn-sm">
                    <x-icon name="{{ $showTrashed ? 'arrow-left' : 'trash-2' }}" class="h-4 w-4" />
                    {{ $showTrashed ? 'Kembali ke Direktori' : 'Buka Sampah' }}
                    @if ($trashedCount > 0 && ! $showTrashed)<span class="management-count-badge">{{ $trashedCount }}</span>@endif
                </button>
            @endcan
        </div>

        <div class="table-toolbar management-toolbar">
            <div class="toolbar-left management-filter-group">
                <label class="field-search management-search-field" for="student-search">
                    <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari siswa</span>
                    <input id="student-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama, NIS, atau NISN..." autocomplete="off">
                    <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
                </label>
                <label class="management-filter-field" for="student-status"><span class="sr-only">Filter status siswa</span><select id="student-status" wire:model.live="status" class="select-field"><option value="">Semua status</option><option value="aktif">Aktif</option><option value="pindah">Pindah</option><option value="lulus">Lulus</option><option value="keluar">Keluar</option><option value="do">DO</option></select></label>
                <label class="management-filter-field" for="student-class"><span class="sr-only">Filter rombel</span><select id="student-class" wire:model.live="rombel" class="select-field"><option value="">Semua rombel</option>@foreach ($rombels as $rombelOption)<option value="{{ $rombelOption->id }}">{{ $rombelOption->nama }}</option>@endforeach</select></label>
            </div>

            <div class="toolbar-right management-directory-actions">
                <details class="management-action-menu">
                    <summary class="btn btn-outline btn-sm"><x-icon name="download" class="h-4 w-4" /> Export <x-icon name="chevron-down" class="h-4 w-4" /></summary>
                    <div class="management-menu-panel">
                        <p>
                            @if ($showTrashed)
                                Export mengikuti filter aktif tanpa data sampah.
                            @else
                                Export {{ number_format($students->total(), 0, ',', '.') }} siswa
                                @if ($rombel !== '')
                                    · Rombel {{ $rombels->firstWhere('id', (int) $rombel)?->nama ?? 'terpilih' }}
                                @else
                                    · Semua kelas
                                @endif
                                @if ($status !== '') · Status {{ ucfirst($status) }} @endif
                                @if ($search !== '') · Cari “{{ \Illuminate\Support\Str::limit($search, 15) }}” @endif
                            @endif
                        </p>
                        <a href="{{ route('siswa.ekspor.excel', array_filter(['search' => $search ?: null, 'status' => $status ?: null, 'rombel_id' => $rombel ?: null])) }}"><x-icon name="file-spreadsheet" class="h-4 w-4" /> Excel (.xlsx)</a>
                        <a href="{{ route('siswa.ekspor.pdf', array_filter(['search' => $search ?: null, 'status' => $status ?: null, 'rombel_id' => $rombel ?: null])) }}"><x-icon name="file-text" class="h-4 w-4" /> PDF</a>
                    </div>
                </details>
                @if ($rombel !== '')
                    <a href="{{ route('siswa.kartu.rombel', ['rombel_id' => $rombel]) }}" class="btn btn-outline btn-sm"><x-icon name="id-card" class="h-4 w-4" /> Cetak Kartu</a>
                @else
                    <button type="button" disabled title="Pilih kelas dulu untuk mencetak kartu rombel" class="btn btn-outline btn-sm"><x-icon name="id-card" class="h-4 w-4" /> Cetak Kartu</button>
                @endif
            </div>
        </div>

        <div class="table-scroll" wire:loading.class="management-content-loading" wire:target="search,status,rombel,toggleTrashed">
            <table class="data-table management-data-table student-directory-table">
                <thead>
                    <tr>
                        <th scope="col" class="management-check-column"><label class="management-check-target"><input type="checkbox" wire:model.live="selectAll" aria-label="Pilih semua siswa di halaman ini"><span class="sr-only">Pilih semua</span></label></th>
                        <th scope="col">Nama Siswa</th><th scope="col">Rombel</th><th scope="col">NIS / NISN</th><th scope="col">Jenis Kelamin</th><th scope="col">Status</th><th scope="col" class="management-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        @php
                            $nameParts = preg_split('/\s+/', trim($student->nama));
                            $initials = strtoupper(mb_substr($nameParts[0], 0, 1).(count($nameParts) > 1 ? mb_substr(end($nameParts), 0, 1) : ''));
                            $statusClass = match ($student->status) { 'aktif' => 'badge-green', 'lulus' => 'badge-blue', 'pindah' => 'badge-orange', 'keluar', 'do' => 'badge-red', default => 'badge-gray' };
                            $statusLabel = match ($student->status) { 'aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah', 'keluar' => 'Keluar', 'do' => 'DO', default => ucfirst($student->status) };
                        @endphp
                        <tr wire:key="student-directory-{{ $student->id }}">
                            <td class="management-check-column"><label class="management-check-target"><input type="checkbox" wire:model.live="selected" value="{{ $student->id }}" aria-label="Pilih {{ $student->nama }}"></label></td>
                            <td>
                                <div class="person-cell">
                                    @if ($student->fotoUrl())
                                        <img src="{{ $student->fotoUrl() }}" alt="Foto {{ $student->nama }}" class="management-avatar-image">
                                    @else
                                        <span class="table-avatar">{{ $initials }}</span>
                                    @endif
                                    <span><strong>{{ $student->nama }}</strong><small>ID Siswa #{{ $student->id }}</small></span>
                                </div>
                            </td>
                            <td><strong>{{ $student->rombels->first()?->nama ?? 'Belum ditempatkan' }}</strong></td>
                            <td><span class="management-reference">{{ $student->nis }}</span>@if ($student->nisn)<small class="management-cell-meta">NISN {{ $student->nisn }}</small>@endif</td>
                            <td>{{ $student->jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td>
                                <div class="row-actions management-row-actions">
                                    @if ($showTrashed)
                                        @can('siswa.delete')
                                            <button type="button" wire:click="restore({{ $student->id }})" wire:loading.attr="disabled" wire:target="restore({{ $student->id }})" class="table-action management-action-success" aria-label="Pulihkan {{ $student->nama }}" title="Pulihkan"><x-icon name="undo-2" class="h-4 w-4" /></button>
                                            <button type="button" wire:click="forceDelete({{ $student->id }})" wire:confirm="Hapus permanen {{ $student->nama }}? Data tidak dapat dikembalikan." wire:loading.attr="disabled" wire:target="forceDelete({{ $student->id }})" class="table-action danger" aria-label="Hapus permanen {{ $student->nama }}" title="Hapus permanen"><x-icon name="trash-2" class="h-4 w-4" /></button>
                                        @endcan
                                    @else
                                        @can('siswa.view')<a href="{{ route('siswa.show', $student) }}" class="table-action" aria-label="Lihat {{ $student->nama }}" title="Lihat detail"><x-icon name="eye" class="h-4 w-4" /></a>@endcan
                                        @can('siswa.update')<a href="{{ route('siswa.edit', $student) }}" class="table-action" aria-label="Ubah {{ $student->nama }}" title="Ubah data"><x-icon name="pencil" class="h-4 w-4" /></a>@endcan
                                        @can('siswa.delete')<button type="button" wire:click="delete({{ $student->id }})" wire:confirm="Pindahkan {{ $student->nama }} ke sampah?" wire:loading.attr="disabled" wire:target="delete({{ $student->id }})" class="table-action danger" aria-label="Hapus {{ $student->nama }}" title="Pindahkan ke sampah"><x-icon name="trash-2" class="h-4 w-4" /></button>@endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state"><div class="empty-symbol"><x-icon name="users" class="h-7 w-7" /></div><h3>{{ $showTrashed ? 'Sampah masih kosong' : 'Siswa tidak ditemukan' }}</h3><p>{{ $showTrashed ? 'Belum ada data siswa yang dipindahkan ke sampah.' : 'Ubah kata kunci atau filter untuk melihat siswa lainnya.' }}</p>@if (! $showTrashed && ($search !== '' || $status !== '' || $rombel !== ''))<button type="button" wire:click="resetFilters" class="btn btn-soft btn-sm">Reset filter</button>@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->hasPages())<div class="management-pagination">{{ $students->links() }}</div>@endif
    </section>
</div>
