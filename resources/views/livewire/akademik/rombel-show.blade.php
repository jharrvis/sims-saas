<div class="page-stack">
    @if (session('sukses'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite" data-flash-alert>
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    <div class="page-header management-page-header">
        <div>
            <p class="rombel-breadcrumb">Akademik <span aria-hidden="true">&gt;</span> <a href="{{ route('kelas.index') }}">Data Kelas</a> <span aria-hidden="true">&gt;</span> <a href="{{ route('kelas.show', $rombel) }}">{{ $rombel->nama }}</a> <span aria-hidden="true">&gt;</span> Kelola anggota</p>
            <h2 class="management-page-title">Kelola anggota kelas</h2>
            <p>Atur penempatan siswa ke kelas {{ $rombel->nama }} dengan mudah.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('kelas.show', $rombel) }}" class="btn btn-outline">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
            </a>
            @can('kelas.update')
                <button type="button" wire:click="simpan" @disabled(!$dirty) class="btn btn-primary">
                    <x-icon name="save" class="h-4 w-4" /> Simpan perubahan
                </button>
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

    @can('kelas.update')
        <section class="card rombel-pengurus-card" aria-labelledby="rombel-pengurus-title">
            <div class="card-head management-table-heading">
                <div class="card-title">
                    <span class="mini-icon purple"><x-icon name="award" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="rombel-pengurus-title">Pengurus kelas</h3>
                        <p>Ketua, wakil, sekretaris, dan bendahara dipilih dari anggota kelas</p>
                    </div>
                </div>
            </div>
            <div class="form-grid management-form-grid rombel-pengurus-grid">
                @foreach ($pengurusLabels as $key => $label)
                    <div class="form-group">
                        <label for="rombel-pengurus-{{ $key }}">{{ $label }}</label>
                        <select id="rombel-pengurus-{{ $key }}" wire:model="pengurus.{{ $key }}" class="select-field">
                            <option value="">— Tidak ada —</option>
                            @foreach ($pengurusAnggota as $a)
                                <option value="{{ $a->id }}">{{ $a->nama }}</option>
                            @endforeach
                        </select>
                        @error('pengurus.'.$key) <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
            <div class="rombel-pengurus-actions">
                <button type="button" wire:click="simpanPengurus" wire:loading.attr="disabled" class="btn btn-primary">Simpan pengurus</button>
            </div>
        </section>
    @endcan

    @if ($confirmKapasitas)
        <div class="management-alert management-alert-warning rombel-capacity-confirm" role="alert">
            <span>Kelas akan melebihi kapasitas ({{ number_format($jumlahAnggota, 0, ',', '.') }}/{{ $rombel->kapasitas }}). Tetap simpan perubahan?</span>
            <div class="page-actions">
                <button type="button" wire:click="konfirmasiSimpan" wire:loading.attr="disabled" class="btn btn-primary btn-sm">Ya, tetap simpan</button>
                <button type="button" wire:click="batalKonfirmasi" class="btn btn-outline btn-sm">Batal</button>
            </div>
        </div>
    @endif

    <div class="rombel-board" x-data="{
        selectedCalon: $wire.entangle('selectedCalon'),
        selectedAnggota: $wire.entangle('selectedAnggota'),
        headerCalon: false,
        headerAnggota: false,
        syncHeaders() {
            const calonIds = [...this.$root.querySelectorAll('[data-calon-check]')].map((el) => Number(el.value));
            const anggotaIds = [...this.$root.querySelectorAll('[data-anggota-check]')].map((el) => Number(el.value));
            const selC = this.selectedCalon.map(Number);
            const selA = this.selectedAnggota.map(Number);
            this.headerCalon = calonIds.length > 0 && calonIds.every((id) => selC.includes(id));
            this.headerAnggota = anggotaIds.length > 0 && anggotaIds.every((id) => selA.includes(id));
        },
        toggleAllCalon() {
            const ids = [...this.$root.querySelectorAll('[data-calon-check]')].map((el) => Number(el.value));
            const sel = this.selectedCalon.map(Number);
            this.selectedCalon = ids.length > 0 && ids.every((id) => sel.includes(id))
                ? sel.filter((id) => !ids.includes(id))
                : [...new Set([...sel, ...ids])];
        },
        toggleAllAnggota() {
            const ids = [...this.$root.querySelectorAll('[data-anggota-check]')].map((el) => Number(el.value));
            const sel = this.selectedAnggota.map(Number);
            this.selectedAnggota = ids.length > 0 && ids.every((id) => sel.includes(id))
                ? sel.filter((id) => !ids.includes(id))
                : [...new Set([...sel, ...ids])];
        },
    }" x-init="$watch('selectedCalon', () => syncHeaders()); $watch('selectedAnggota', () => syncHeaders()); if (window.Livewire) { Livewire.hook('morph.updated', () => syncHeaders()); } syncHeaders();">
        <div class="rombel-loading-overlay" wire:loading wire:target="tambahkan,keluarkan,simpan,konfirmasiSimpan,filterAngkatan,sortCalon,sortAnggota,gotoCalonPage,gotoAnggotaPage" role="status" aria-live="polite">
            <span class="rombel-spinner" aria-hidden="true"></span>
            <span>Menyiapkan data...</span>
        </div>

        @can('kelas.update')
            <section class="card management-table-card" aria-labelledby="rombel-candidate-title">
                <div class="card-head management-table-heading">
                    <div class="card-title">
                        <span class="mini-icon green"><x-icon name="user-plus" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3 id="rombel-candidate-title">Calon siswa <span class="rombel-count-badge">{{ $calon->total() }}</span></h3>
                            <p>Siswa aktif yang belum berada di kelas ini</p>
                        </div>
                    </div>
                </div>
                <div class="table-toolbar management-toolbar">
                    <div class="toolbar-left management-filter-group">
                        <label class="field-search management-search-field" for="rombel-candidate-search">
                            <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari calon siswa</span>
                            <input id="rombel-candidate-search" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama atau NIS..." autocomplete="off">
                            <span wire:loading wire:target="searchSiswa" class="management-inline-spinner" aria-hidden="true"></span>
                        </label>
                        <label class="sr-only" for="rombel-candidate-angkatan">Filter angkatan</label>
                        <select id="rombel-candidate-angkatan" wire:model.live="filterAngkatan" class="select-field">
                            <option value="semua">Semua angkatan</option>
                            @foreach ($angkatanList as $tahun)
                                <option value="{{ $tahun }}">{{ $tahun }}</option>
                            @endforeach
                        </select>
                        <label class="sr-only" for="rombel-candidate-sort">Urutkan calon</label>
                        <select id="rombel-candidate-sort" wire:model.live="sortCalon" class="select-field">
                            <option value="nama">Urutkan: Nama (A–Z)</option>
                            <option value="nama_desc">Urutkan: Nama (Z–A)</option>
                            <option value="nis">Urutkan: NIS</option>
                            <option value="angkatan">Urutkan: Angkatan</option>
                        </select>
                    </div>
                </div>
                <div x-show="selectedCalon.length > 0" x-cloak class="rombel-selectbar">
                    <span class="rombel-selectbar-icon" aria-hidden="true">–</span>
                    <span><span x-text="selectedCalon.length"></span> siswa dipilih</span>
                    <button type="button" @click="selectedCalon = []" class="rombel-selectbar-clear">Batal pilih</button>
                </div>
                <div class="table-scroll">
                    <table class="data-table management-data-table">
                        <thead>
                            <tr>
                                <th scope="col" class="col-check"><input type="checkbox" :checked="headerCalon" @click.prevent="toggleAllCalon()" aria-label="Pilih semua calon yang ditampilkan"></th>
                                <th scope="col">Nama siswa</th>
                                <th scope="col" class="col-nis">NIS</th>
                                <th scope="col" class="col-angkatan">Angkatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($calon as $c)
                                <tr wire:key="rombel-candidate-{{ $c->id }}" :class="{ 'row-selected': selectedCalon.map(Number).includes({{ $c->id }}) }">
                                    <td><input type="checkbox" x-model="selectedCalon" data-calon-check value="{{ $c->id }}" aria-label="Pilih {{ $c->nama }}"></td>
                                    <td>
                                        <span class="person-cell">
                                            @if ($c->fotoUrl())
                                                <img src="{{ $c->fotoUrl() }}" alt="Foto {{ $c->nama }}" class="management-avatar-image">
                                            @else
                                                <span class="rombel-avatar" aria-hidden="true">{{ $this->initials($c->nama) }}</span>
                                            @endif
                                            <strong>{{ $c->nama }}</strong>
                                        </span>
                                        @if (isset($asalMap[$c->id]))
                                            <span class="rombel-asal">Dari {{ $asalMap[$c->id] }}</span>
                                        @endif
                                    </td>
                                    <td><span class="management-reference">{{ $c->nis }}</span></td>
                                    <td>{{ $c->angkatan ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="empty-state"><div class="empty-symbol"><x-icon name="search" class="h-7 w-7" /></div><h3>Tidak ada siswa yang cocok</h3><p>Ubah kata kunci, filter angkatan, atau urutan untuk melihat calon lainnya.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="rombel-table-footer">
                    <span>
                        @if ($calon->total() > 0)
                            {{ $calon->firstItem() }}–{{ $calon->lastItem() }} dari {{ $calon->total() }} siswa
                        @else
                            Tidak ada siswa
                        @endif
                    </span>
                    <nav class="rombel-pager" aria-label="Halaman calon siswa">
                        <button type="button" wire:click="gotoCalonPage({{ $calon->currentPage() - 1 }})" @disabled($calon->onFirstPage()) aria-label="Halaman sebelumnya">&lt;</button>
                        @foreach ($calonPager as $p)
                            @if ($p === null)
                                <span class="rombel-pager-ellipsis" aria-hidden="true">…</span>
                            @else
                                <button type="button" wire:click="gotoCalonPage({{ $p }})" @class(['active' => $p === $calon->currentPage()]) aria-current="{{ $p === $calon->currentPage() ? 'page' : 'false' }}">{{ $p }}</button>
                            @endif
                        @endforeach
                        <button type="button" wire:click="gotoCalonPage({{ $calon->currentPage() + 1 }})" @disabled(!$calon->hasMorePages()) aria-label="Halaman berikutnya">&gt;</button>
                    </nav>
                </div>
            </section>

            <div class="rombel-board-actions" role="group" aria-label="Pindahkan siswa">
                <h3>Pindahkan siswa</h3>
                <p>Pilih siswa, lalu gunakan tombol di bawah untuk mengubah anggota kelas.</p>
                <button type="button" wire:click="tambahkan" :disabled="selectedCalon.length === 0" class="btn btn-primary">
                    <x-icon name="arrow-right" class="h-4 w-4" /> Tambahkan
                </button>
                <button type="button" wire:click="keluarkan" :disabled="selectedAnggota.length === 0" class="btn btn-outline">
                    <x-icon name="arrow-left" class="h-4 w-4" /> Keluarkan
                </button>
            </div>
        @endcan

        <section class="card management-table-card" aria-labelledby="rombel-member-title">
            <div class="card-head management-table-heading">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="rombel-member-title">Anggota kelas {{ $rombel->nama }} <span class="rombel-count-badge">{{ $anggota->total() }}</span></h3>
                        <p>Daftar siswa yang terdaftar di kelas ini</p>
                    </div>
                </div>
            </div>
            <div class="table-toolbar management-toolbar">
                <div class="toolbar-left management-filter-group">
                    <label class="field-search management-search-field" for="rombel-member-search">
                        <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari anggota kelas</span>
                        <input id="rombel-member-search" type="search" wire:model.live.debounce.300ms="searchAnggota" placeholder="Cari nama atau NIS..." autocomplete="off">
                        <span wire:loading wire:target="searchAnggota" class="management-inline-spinner" aria-hidden="true"></span>
                    </label>
                    <label class="sr-only" for="rombel-member-sort">Urutkan anggota</label>
                    <select id="rombel-member-sort" wire:model.live="sortAnggota" class="select-field rombel-sort-single">
                        <option value="nama">Urutkan: Nama (A–Z)</option>
                        <option value="nama_desc">Urutkan: Nama (Z–A)</option>
                        <option value="nis">Urutkan: NIS</option>
                        <option value="angkatan">Urutkan: Angkatan</option>
                    </select>
                </div>
            </div>
            @can('kelas.update')
                <div x-show="selectedAnggota.length > 0" x-cloak class="rombel-selectbar">
                    <span class="rombel-selectbar-icon" aria-hidden="true">–</span>
                    <span><span x-text="selectedAnggota.length"></span> siswa dipilih</span>
                    <button type="button" @click="selectedAnggota = []" class="rombel-selectbar-clear">Batal pilih</button>
                </div>
            @endcan
            <div class="table-scroll">
                <table class="data-table management-data-table">
                    <thead>
                        <tr>
                            @can('kelas.update')
                                <th scope="col" class="col-check"><input type="checkbox" :checked="headerAnggota" @click.prevent="toggleAllAnggota()" aria-label="Pilih semua anggota yang ditampilkan"></th>
                            @endcan
                            <th scope="col">Nama siswa</th>
                            <th scope="col" class="col-nis">NIS</th>
                            <th scope="col" class="col-angkatan">Angkatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($anggota as $s)
                            <tr wire:key="rombel-member-{{ $s->id }}" :class="{ 'row-selected': selectedAnggota.map(Number).includes({{ $s->id }}) }">
                                @can('kelas.update')
                                    <td><input type="checkbox" x-model="selectedAnggota" data-anggota-check value="{{ $s->id }}" aria-label="Pilih {{ $s->nama }}"></td>
                                @endcan
                                <td>
                                    <span class="person-cell">
                                        @if ($s->fotoUrl())
                                            <img src="{{ $s->fotoUrl() }}" alt="Foto {{ $s->nama }}" class="management-avatar-image">
                                        @else
                                            <span class="rombel-avatar" aria-hidden="true">{{ $this->initials($s->nama) }}</span>
                                        @endif
                                        <strong>{{ $s->nama }}</strong>
                                    </span>
                                </td>
                                <td><span class="management-reference">{{ $s->nis }}</span></td>
                                <td>{{ $s->angkatan ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty-state"><div class="empty-symbol"><x-icon name="users" class="h-7 w-7" /></div><h3>{{ $searchAnggota !== '' ? 'Siswa tidak ditemukan' : 'Belum ada siswa di kelas ini' }}</h3><p>{{ $searchAnggota !== '' ? 'Ubah kata kunci untuk melihat anggota lainnya.' : 'Tambahkan siswa dari daftar calon, lalu simpan perubahan.' }}</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="rombel-table-footer">
                <span>{{ $anggota->total() }} siswa terdaftar</span>
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
            </div>
        </section>
    </div>

    <div class="rombel-statusbar" role="status">
        <span><x-icon name="info" class="h-4 w-4" /> Pilih siswa, lalu klik Tambahkan atau Keluarkan untuk mengubah anggota kelas.</span>
        @if ($dirty)
            <span class="rombel-status-dirty"><span class="rombel-status-dot dirty" aria-hidden="true"></span> Perubahan belum disimpan</span>
        @else
            <span class="rombel-status-dirty"><span class="rombel-status-dot clean" aria-hidden="true"></span> Semua perubahan tersimpan</span>
        @endif
    </div>
</div>
