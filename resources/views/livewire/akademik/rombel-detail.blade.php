<div class="page-stack rombel-detail">
    <div class="page-header management-page-header">
        <div>
            <p class="rombel-breadcrumb">Akademik <span aria-hidden="true">&gt;</span> <a href="{{ route('kelas.index') }}">Data Kelas</a> <span aria-hidden="true">&gt;</span> {{ $rombel->nama }}</p>
            <h2 class="management-page-title">Kelas {{ $rombel->nama }}</h2>
            <p>Daftar siswa kelas {{ $rombel->nama }}{{ $rombel->academicYear ? ' tahun ajaran '.$rombel->academicYear->nama : '' }}.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('kelas.index') }}" class="btn btn-outline">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
            </a>
            @can('kelas.update')
                <a href="{{ route('kelas.edit', $rombel) }}" class="btn btn-outline">
                    <x-icon name="pencil" class="h-4 w-4" /> Ubah kelas
                </a>
                <a href="{{ route('kelas.anggota', $rombel) }}" class="btn btn-primary">
                    <x-icon name="settings" class="h-4 w-4" /> Atur anggota kelas
                </a>
            @endcan
        </div>
    </div>

    <section class="card rombel-info-card" aria-label="Informasi kelas">
        <div class="rombel-info-main">
            <span class="rombel-info-badge">{{ $rombel->nama }}</span>
            <div class="rombel-info-item">
                <span class="rombel-info-label">Tahun ajaran</span>
                <strong>{{ $rombel->academicYear?->nama ?? '—' }}</strong>
            </div>
            <div class="rombel-info-item">
                <span class="rombel-info-label">Wali kelas</span>
                <strong>{{ $rombel->waliKelas?->nama ?? '—' }}</strong>
            </div>
        </div>
        <div class="rombel-info-capacity">
            <div class="rombel-info-capacity-text">
                <span>Kapasitas kelas</span>
                <strong>{{ number_format($jumlahAnggota, 0, ',', '.') }} / {{ $rombel->kapasitas }} siswa</strong>
            </div>
            <div class="rombel-capacity-bar" role="img" aria-label="Kapasitas terisi {{ $rombel->kapasitas ? min(100, (int) round($jumlahAnggota / $rombel->kapasitas * 100)) : 0 }} persen">
                <span style="width: {{ $rombel->kapasitas ? min(100, (int) round($jumlahAnggota / $rombel->kapasitas * 100)) : 0 }}%"></span>
            </div>
            @if ($rombel->kapasitas === null)
                <span class="rombel-capacity-note">—</span>
            @elseif ($rombel->kapasitas - $jumlahAnggota >= 0)
                <span class="rombel-capacity-note">{{ $rombel->kapasitas - $jumlahAnggota }} kursi tersedia</span>
            @else
                <span class="rombel-capacity-note danger">Melebihi kapasitas {{ $jumlahAnggota - $rombel->kapasitas }} siswa</span>
            @endif
        </div>
    </section>

    <section class="card" aria-label="Pengurus kelas">
        <div class="card-head management-table-heading">
            <div class="card-title">
                <span class="mini-icon purple"><x-icon name="award" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3>Pengurus kelas</h3>
                    <p>Ketua, wakil, sekretaris, dan bendahara kelas ini</p>
                </div>
            </div>
        </div>
        <div class="rombel-pengurus-view-list">
            @foreach ($pengurusLabels as $key => $label)
                <div><span>{{ $label }}</span><strong>{{ $pengurus[$key] ?? '—' }}</strong></div>
            @endforeach
        </div>
    </section>

    <section class="card management-table-card" aria-labelledby="rombel-detail-member-title">
        <div class="card-head management-table-heading">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="rombel-detail-member-title">Anggota kelas {{ $rombel->nama }} <span class="rombel-count-badge">{{ $anggotaTotal }}</span></h3>
                    <p>Daftar siswa yang terdaftar di kelas ini</p>
                </div>
            </div>
        </div>
        <div class="table-toolbar management-toolbar">
            <div class="toolbar-left management-filter-group">
                <label class="field-search management-search-field" for="rombel-detail-member-search">
                    <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari anggota kelas</span>
                    <input id="rombel-detail-member-search" type="search" wire:model.live.debounce.300ms="searchAnggota" placeholder="Cari nama atau NIS..." autocomplete="off">
                    <span wire:loading wire:target="searchAnggota" class="management-inline-spinner" aria-hidden="true"></span>
                </label>
                <label class="sr-only" for="rombel-detail-member-sort">Urutkan anggota</label>
                <select id="rombel-detail-member-sort" wire:model.live="sortAnggota" class="select-field rombel-sort-single">
                    <option value="nama">Urutkan: Nama (A–Z)</option>
                    <option value="nama_desc">Urutkan: Nama (Z–A)</option>
                    <option value="nis">Urutkan: NIS</option>
                    <option value="angkatan">Urutkan: Angkatan</option>
                </select>
                <label class="sr-only" for="rombel-detail-member-per-page">Jumlah baris per halaman</label>
                <select id="rombel-detail-member-per-page" wire:model.live="perPage" class="select-field rombel-sort-single">
                    @foreach ($perPageOptions as $opt)
                        <option value="{{ $opt }}">{{ $opt }} baris</option>
                    @endforeach
                    <option value="0">Semua</option>
                </select>
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table management-data-table">
                <thead>
                    <tr>
                        <th scope="col" class="col-no">No</th>
                        <th scope="col">Nama siswa</th>
                        <th scope="col" class="col-nis">NIS</th>
                        <th scope="col" class="col-nisn">NISN</th>
                        <th scope="col" class="col-jk">Jenis Kelamin</th>
                        <th scope="col" class="col-angkatan">Angkatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($anggota as $s)
                        <tr wire:key="rombel-detail-member-{{ $s->id }}" wire:click="bukaDetail({{ $s->id }})" class="rombel-row-clickable">
                            <td>{{ $nomorAwal + $loop->iteration }}</td>
                            <td>
                                @if ($s->fotoUrl())
                                    <img src="{{ $s->fotoUrl() }}" alt="Foto {{ $s->nama }}" class="management-avatar-image rombel-avatar-img">
                                @else
                                    <span class="rombel-avatar" aria-hidden="true">{{ $this->initials($s->nama) }}</span>
                                @endif
                                <strong>{{ $s->nama }}</strong>
                            </td>
                            <td><span class="management-reference">{{ $s->nis }}</span></td>
                            <td><span class="management-reference">{{ $s->nisn ?? '—' }}</span></td>
                            <td>{{ $s->jk === 'L' ? 'Laki-laki' : ($s->jk === 'P' ? 'Perempuan' : '—') }}</td>
                            <td>{{ $s->angkatan ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-symbol"><x-icon name="users" class="h-7 w-7" /></div><h3>{{ $searchAnggota !== '' ? 'Siswa tidak ditemukan' : 'Belum ada siswa di kelas ini' }}</h3><p>{{ $searchAnggota !== '' ? 'Ubah kata kunci untuk melihat anggota lainnya.' : 'Kelas ini belum memiliki anggota.' }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="rombel-table-footer">
            <span>{{ $isSemua ? 'Menampilkan semua '.$anggotaTotal.' siswa' : $anggotaTotal.' siswa terdaftar' }}</span>
            @if (! $isSemua)
                <nav class="rombel-pager" aria-label="Halaman anggota kelas">
                    <button type="button" wire:click="gotoAnggotaPage({{ $anggota->currentPage() - 1 }})" @disabled($anggota->onFirstPage()) aria-label="Halaman sebelumnya">&lt;</button>
                    @foreach ($anggotaPager as $p)
                        @if ($p === null)
                            <span class="rombel-pager-ellipsis" aria-hidden="true">…</span>
                        @else
                            <button type="button" wire:click="gotoAnggotaPage({{ $p }})" @class(['active' => $p === $anggota->currentPage()]) aria-current="{{ $p === $anggota->currentPage() ? 'page' : 'false' }}">{{ $p }}</button>
                        @endif
                    @endforeach
                    <button type="button" wire:click="gotoAnggotaPage({{ $anggota->currentPage() + 1 }})" @disabled(!$anggota->hasMorePages()) aria-label="Halaman berikutnya">&gt;</button>
                </nav>
            @endif
        </div>
    </section>

    @if ($detailSiswa)
        <div class="rombel-modal-backdrop" wire:click="tutupDetail" x-data @keydown.escape.window="$wire.tutupDetail()">
            <div class="rombel-modal-card" role="dialog" aria-modal="true" aria-labelledby="rombel-modal-siswa-title" wire:click.stop>
                <div class="rombel-modal-head">
                    @if ($detailSiswa->fotoUrl())
                        <img src="{{ $detailSiswa->fotoUrl() }}" alt="Foto {{ $detailSiswa->nama }}" class="rombel-modal-photo">
                    @else
                        <span class="rombel-modal-photo-fallback" aria-hidden="true">{{ $this->initials($detailSiswa->nama) }}</span>
                    @endif
                    <div>
                        <h3 id="rombel-modal-siswa-title">{{ $detailSiswa->nama }}</h3>
                        <p>Siswa kelas {{ $rombel->nama }} · {{ $rombel->academicYear->nama ?? '—' }}</p>
                    </div>
                    <button type="button" wire:click="tutupDetail" class="rombel-modal-close" aria-label="Tutup detail">×</button>
                </div>
                <dl class="rombel-modal-grid">
                    <div><dt>NIS</dt><dd>{{ $detailSiswa->nis }}</dd></div>
                    <div><dt>NISN</dt><dd>{{ $detailSiswa->nisn ?? '—' }}</dd></div>
                    <div><dt>Jenis Kelamin</dt><dd>{{ $detailSiswa->jk === 'L' ? 'Laki-laki' : ($detailSiswa->jk === 'P' ? 'Perempuan' : '—') }}</dd></div>
                    <div><dt>Tempat, Tanggal Lahir</dt><dd>{{ $detailTtl }}</dd></div>
                    <div><dt>Agama</dt><dd>{{ $detailSiswa->agama ?? '—' }}</dd></div>
                    <div><dt>Angkatan</dt><dd>{{ $detailSiswa->angkatan ?? '—' }}</dd></div>
                    <div><dt>Status</dt><dd>{{ ucfirst($detailSiswa->status) }}</dd></div>
                    <div><dt>Telepon</dt><dd>{{ $detailSiswa->phone ?? '—' }}</dd></div>
                    <div class="rombel-modal-span"><dt>Alamat</dt><dd>{{ $detailSiswa->alamat ?? '—' }}</dd></div>
                    @if ($detailSiswa->guardians->isNotEmpty())
                        <div class="rombel-modal-span"><dt>Orang tua / Wali</dt><dd>{{ $detailSiswa->guardians->pluck('nama')->join(', ') }}</dd></div>
                    @endif
                </dl>
                <div class="rombel-modal-actions">
                    @can('siswa.view')
                        <a href="{{ route('siswa.show', $detailSiswa) }}" class="btn btn-outline">Lihat data lengkap</a>
                    @endcan
                    <button type="button" wire:click="tutupDetail" class="btn btn-primary">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div>
