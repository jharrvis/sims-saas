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

    <section class="card management-table-card" aria-labelledby="rombel-member-title">
        <div class="card-head management-table-heading">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="rombel-member-title">Anggota Kelas</h3>
                    <p>{{ number_format($jumlahAnggota, 0, ',', '.') }} siswa aktif</p>
                </div>
            </div>
        </div>

        <div class="table-toolbar management-toolbar">
            <div class="toolbar-left management-filter-group">
                <label class="field-search management-search-field" for="rombel-member-search">
                    <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari anggota kelas</span>
                    <input id="rombel-member-search" type="search" wire:model.live.debounce.300ms="searchAnggota" placeholder="Cari nama siswa..." autocomplete="off">
                    <span wire:loading wire:target="searchAnggota" class="management-inline-spinner" aria-hidden="true"></span>
                </label>
            </div>
        </div>

        <div class="table-scroll" wire:loading.class="management-content-loading" wire:target="searchAnggota,assignStudent,removeStudent">
            <table class="data-table management-data-table">
                <thead>
                    <tr>
                        <th scope="col">Nama Siswa</th>
                        <th scope="col">NIS</th>
                        <th scope="col" class="management-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($siswa as $s)
                        <tr wire:key="rombel-member-{{ $s->id }}">
                            <td><strong>{{ $s->nama }}</strong></td>
                            <td><span class="management-reference">{{ $s->nis }}</span></td>
                            <td>
                                <div class="row-actions management-row-actions">
                                    @can('kelas.update')
                                        <button type="button" wire:click="removeStudent({{ $s->id }})" wire:confirm="Keluarkan {{ $s->nama }} dari kelas ini?" wire:loading.attr="disabled" wire:target="removeStudent({{ $s->id }})" class="table-action danger" aria-label="Keluarkan {{ $s->nama }}" title="Keluarkan dari kelas"><x-icon name="user-minus" class="h-4 w-4" /></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><div class="empty-symbol"><x-icon name="users" class="h-7 w-7" /></div><h3>{{ $searchAnggota !== '' ? 'Siswa tidak ditemukan' : 'Belum ada siswa di kelas ini' }}</h3><p>{{ $searchAnggota !== '' ? 'Ubah kata kunci untuk melihat anggota lainnya.' : 'Tempatkan siswa dari daftar di bawah.' }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @can('kelas.update')
        <section class="card management-table-card" aria-labelledby="rombel-candidate-title">
            <div class="card-head management-table-heading">
                <div class="card-title">
                    <span class="mini-icon green"><x-icon name="user-plus" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="rombel-candidate-title">Tempatkan Siswa</h3>
                        <p>Cari siswa aktif yang belum berada di kelas ini</p>
                    </div>
                </div>
            </div>
            <div class="table-toolbar management-toolbar">
                <div class="toolbar-left management-filter-group">
                    <label class="field-search management-search-field" for="rombel-candidate-search">
                        <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari siswa untuk ditempatkan</span>
                        <input id="rombel-candidate-search" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari siswa..." autocomplete="off">
                        <span wire:loading wire:target="searchSiswa" class="management-inline-spinner" aria-hidden="true"></span>
                    </label>
                </div>
            </div>
            <div class="table-scroll" wire:loading.class="management-content-loading" wire:target="searchSiswa,assignStudent">
                <table class="data-table management-data-table">
                    <thead>
                        <tr>
                            <th scope="col">Nama Siswa</th>
                            <th scope="col">NIS</th>
                            <th scope="col" class="management-actions-heading">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($calon as $c)
                            <tr wire:key="rombel-candidate-{{ $c->id }}">
                                <td><strong>{{ $c->nama }}</strong></td>
                                <td><span class="management-reference">{{ $c->nis }}</span></td>
                                <td>
                                    <div class="row-actions management-row-actions">
                                        <button type="button" wire:click="assignStudent({{ $c->id }})" wire:loading.attr="disabled" wire:target="assignStudent({{ $c->id }})" class="btn btn-soft btn-sm">
                                            <x-icon name="plus" class="h-4 w-4" /> Tempatkan
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="empty-state"><div class="empty-symbol"><x-icon name="search" class="h-7 w-7" /></div><h3>Ketik nama siswa untuk mencari</h3><p>Hanya siswa aktif yang belum berada di kelas ini yang ditampilkan.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endcan
</div>
