<div class="page-stack">
    @if (session('sukses'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / Data Kelas</span>
            <h2 class="management-page-title">Kelas {{ $rombel->nama }}</h2>
            <p>Tingkat {{ $rombel->tingkat }} · {{ $rombel->academicYear?->nama ?? '—' }} · Wali kelas: {{ $rombel->waliKelas?->nama ?? '—' }} · Kapasitas {{ $rombel->kapasitas }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('kelas.index') }}" class="btn btn-outline">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke daftar kelas
            </a>
            @can('kelas.update')
                <a href="{{ route('kelas.edit', $rombel) }}" class="btn btn-primary">
                    <x-icon name="pencil" class="h-4 w-4" /> Ubah
                </a>
            @endcan
        </div>
    </div>

    @if ($confirmKapasitas)
        <div class="management-alert management-alert-warning rombel-capacity-confirm" role="alert">
            <span>Kelas sudah penuh ({{ number_format($jumlahAnggota, 0, ',', '.') }}/{{ $rombel->kapasitas }}). Tetap pindahkan {{ count($pendingAssign) }} siswa?</span>
            <div class="page-actions">
                <button type="button" wire:click="confirmPendingAssign" wire:loading.attr="disabled" class="btn btn-primary btn-sm">Ya, tetap pindahkan</button>
                <button type="button" wire:click="cancelPendingAssign" class="btn btn-outline btn-sm">Batal</button>
            </div>
        </div>
    @endif

    <div class="rombel-board" wire:loading.class="management-content-loading" wire:target="searchSiswa,searchAnggota,filterAsal,sortCalon,sortAnggota,assignStudent,assignSelected,removeStudent,removeSelected,confirmPendingAssign,toggleSelectAllCalon,toggleSelectAllAnggota">
        @can('kelas.update')
            <section class="card management-table-card" aria-labelledby="rombel-candidate-title">
                <div class="card-head management-table-heading">
                    <div class="card-title">
                        <span class="mini-icon green"><x-icon name="user-plus" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3 id="rombel-candidate-title">Calon Siswa</h3>
                            <p>Siswa aktif yang belum berada di kelas ini{{ $calonTerpotong ? ' · menampilkan '.count($calon).' dari '.number_format($calonTotal, 0, ',', '.').' — persempit dengan pencarian atau filter' : '' }}</p>
                        </div>
                    </div>
                </div>
                <div class="table-toolbar management-toolbar">
                    <div class="toolbar-left management-filter-group">
                        <label class="field-search management-search-field" for="rombel-candidate-search">
                            <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari calon siswa</span>
                            <input id="rombel-candidate-search" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama, NIS, atau NISN..." autocomplete="off">
                            <span wire:loading wire:target="searchSiswa" class="management-inline-spinner" aria-hidden="true"></span>
                        </label>
                        <label class="sr-only" for="rombel-candidate-asal">Filter kelas asal</label>
                        <select id="rombel-candidate-asal" wire:model.live="filterAsal" class="select-field">
                            <option value="semua">Semua asal</option>
                            <option value="tanpa-kelas">Belum ada kelas</option>
                            @foreach ($rombelAsal as $asal)
                                <option value="rombel:{{ $asal->id }}">Dari {{ $asal->nama }}</option>
                            @endforeach
                        </select>
                        <label class="sr-only" for="rombel-candidate-sort">Urutkan calon</label>
                        <select id="rombel-candidate-sort" wire:model.live="sortCalon" class="select-field">
                            <option value="nama">Urut: Nama</option>
                            <option value="nis">Urut: NIS</option>
                            <option value="angkatan">Urut: Angkatan</option>
                            <option value="kelas_asal">Urut: Kelas asal</option>
                        </select>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="data-table management-data-table">
                        <thead>
                            <tr>
                                <th scope="col"><input type="checkbox" wire:click="toggleSelectAllCalon" @checked($this->semuaDipilih($calon->pluck('id')->all(), $selectedCalon)) aria-label="Pilih semua calon yang ditampilkan"></th>
                                <th scope="col">Nama Siswa</th>
                                <th scope="col">NIS</th>
                                <th scope="col">Angkatan</th>
                                <th scope="col">Kelas Asal</th>
                                <th scope="col" class="management-actions-heading">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($calon as $c)
                                <tr wire:key="rombel-candidate-{{ $c->id }}">
                                    <td><input type="checkbox" wire:model="selectedCalon" value="{{ $c->id }}" aria-label="Pilih {{ $c->nama }}"></td>
                                    <td><strong>{{ $c->nama }}</strong></td>
                                    <td><span class="management-reference">{{ $c->nis }}</span></td>
                                    <td>{{ $c->angkatan ?? '—' }}</td>
                                    <td>{{ $asalMap[$c->id] ?? 'Belum ada kelas' }}</td>
                                    <td>
                                        <div class="row-actions management-row-actions">
                                            <button type="button" wire:click="assignStudent({{ $c->id }})" wire:loading.attr="disabled" wire:target="assignStudent({{ $c->id }})" class="table-action" aria-label="Pindahkan {{ $c->nama }} ke kelas ini" title="Pindahkan ke kelas ini"><x-icon name="arrow-right" class="h-4 w-4" /></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="empty-state"><div class="empty-symbol"><x-icon name="search" class="h-7 w-7" /></div><h3>Tidak ada siswa yang cocok</h3><p>Ubah kata kunci, filter asal, atau urutan untuk melihat calon lainnya.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="rombel-board-actions" role="group" aria-label="Pindah massal">
                <button type="button" wire:click="assignSelected" @disabled(count($selectedCalon) === 0) wire:loading.attr="disabled" class="btn btn-primary">
                    Pindahkan yang dipilih ({{ count($selectedCalon) }}) <x-icon name="arrow-right" class="h-4 w-4" />
                </button>
                <button type="button" wire:click="removeSelected" @disabled(count($selectedAnggota) === 0) wire:loading.attr="disabled" class="btn btn-outline">
                    <x-icon name="arrow-left" class="h-4 w-4" /> Keluarkan yang dipilih ({{ count($selectedAnggota) }})
                </button>
            </div>
        @endcan

        <section class="card management-table-card" aria-labelledby="rombel-member-title">
            <div class="card-head management-table-heading">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="rombel-member-title">Anggota Kelas</h3>
                        <p>{{ number_format($jumlahAnggota, 0, ',', '.') }} siswa aktif{{ $rombel->kapasitas !== null ? ' dari kapasitas '.$rombel->kapasitas : '' }}</p>
                    </div>
                </div>
            </div>
            <div class="table-toolbar management-toolbar">
                <div class="toolbar-left management-filter-group">
                    <label class="field-search management-search-field" for="rombel-member-search">
                        <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari anggota kelas</span>
                        <input id="rombel-member-search" type="search" wire:model.live.debounce.300ms="searchAnggota" placeholder="Cari nama, NIS, atau NISN..." autocomplete="off">
                        <span wire:loading wire:target="searchAnggota" class="management-inline-spinner" aria-hidden="true"></span>
                    </label>
                    <label class="sr-only" for="rombel-member-sort">Urutkan anggota</label>
                    <select id="rombel-member-sort" wire:model.live="sortAnggota" class="select-field">
                        <option value="nama">Urut: Nama</option>
                        <option value="nis">Urut: NIS</option>
                        <option value="angkatan">Urut: Angkatan</option>
                    </select>
                </div>
            </div>
            <div class="table-scroll">
                <table class="data-table management-data-table">
                    <thead>
                        <tr>
                            @can('kelas.update')
                                <th scope="col"><input type="checkbox" wire:click="toggleSelectAllAnggota" @checked($this->semuaDipilih($siswa->pluck('id')->all(), $selectedAnggota)) aria-label="Pilih semua anggota yang ditampilkan"></th>
                            @endcan
                            <th scope="col">Nama Siswa</th>
                            <th scope="col">NIS</th>
                            <th scope="col">Angkatan</th>
                            <th scope="col" class="management-actions-heading">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($siswa as $s)
                            <tr wire:key="rombel-member-{{ $s->id }}">
                                @can('kelas.update')
                                    <td><input type="checkbox" wire:model="selectedAnggota" value="{{ $s->id }}" aria-label="Pilih {{ $s->nama }}"></td>
                                @endcan
                                <td><strong>{{ $s->nama }}</strong></td>
                                <td><span class="management-reference">{{ $s->nis }}</span></td>
                                <td>{{ $s->angkatan ?? '—' }}</td>
                                <td>
                                    <div class="row-actions management-row-actions">
                                        @can('kelas.update')
                                            <button type="button" wire:click="removeStudent({{ $s->id }})" wire:confirm="Keluarkan {{ $s->nama }} dari kelas ini?" wire:loading.attr="disabled" wire:target="removeStudent({{ $s->id }})" class="table-action danger" aria-label="Keluarkan {{ $s->nama }}" title="Keluarkan dari kelas"><x-icon name="user-minus" class="h-4 w-4" /></button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state"><div class="empty-symbol"><x-icon name="users" class="h-7 w-7" /></div><h3>{{ $searchAnggota !== '' ? 'Siswa tidak ditemukan' : 'Belum ada siswa di kelas ini' }}</h3><p>{{ $searchAnggota !== '' ? 'Ubah kata kunci untuk melihat anggota lainnya.' : 'Tempatkan siswa dari daftar di samping.' }}</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
