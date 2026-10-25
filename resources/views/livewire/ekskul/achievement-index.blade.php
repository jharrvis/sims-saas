<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Ekstrakurikuler</span>
            <h2 class="management-page-title">Prestasi</h2>
            <p>Prestasi siswa beserta kategori, tingkat, dan tanggalnya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('ekskul.index') }}" class="back-link">← Ekstrakurikuler</a>
            @can('prestasi.create')
                <button wire:click="$set('showForm', true)" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Catat Prestasi
                </button>
            @endcan
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="prestasi-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari prestasi</span>
                <input id="prestasi-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari prestasi…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <label class="management-filter-field" for="prestasi-tingkat"><span class="sr-only">Filter tingkat</span>
                <select id="prestasi-tingkat" wire:model.live="tingkat" class="select-field">
                    <option value="">Semua tingkat</option>
                    @foreach (\App\Models\Achievement::TINGKAT as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    @if ($showForm)
        @can('prestasi.create')
            <section class="card management-form-card" aria-labelledby="prestasi-form-title">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon blue"><x-icon name="trophy" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3 id="prestasi-form-title">Catat Prestasi</h3>
                            <p>Prestasi tampil di daftar setelah disimpan.</p>
                        </div>
                    </div>
                </div>

                <form wire:submit="save" class="form-grid management-form-grid">
                    <div class="form-group">
                        <label for="siswa">Siswa <span class="required">*</span></label>
                        <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama…" class="field">
                        <select wire:model="student_id" aria-label="Pilih siswa" class="select-field mt-1">
                            <option value="">— Pilih siswa —</option>
                            @foreach ($siswaList as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                            @endforeach
                        </select>
                        @error('student_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama Prestasi <span class="required">*</span></label>
                        <input id="nama" type="text" wire:model="nama" maxlength="200" placeholder="Juara 1 Olimpiade Matematika" class="field">
                        @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" wire:model="kategori" class="select-field">
                            @foreach (\App\Models\Achievement::KATEGORI as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('kategori') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="tingkat">Tingkat</label>
                        <select id="tingkat" wire:model="tingkat" class="select-field">
                            @foreach (\App\Models\Achievement::TINGKAT as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('tingkat') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="tanggal">Tanggal</label>
                        <input id="tanggal" type="date" wire:model="tanggal" class="field">
                        @error('tanggal') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <input id="keterangan" type="text" wire:model="keterangan" maxlength="2000" class="field">
                        @error('keterangan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-actions management-field-span">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">Simpan</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="$set('showForm', false)" class="btn btn-outline">Batal</button>
                    </div>
                </form>
            </section>
        @endcan
    @endif

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Siswa</th>
                        <th scope="col">Prestasi</th>
                        <th scope="col">Tingkat</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="prestasi-{{ $item->id }}">
                            <td class="font-semibold">{{ $item->student->nama }}</td>
                            <td>
                                <span class="block font-medium text-slate-700 dark:text-slate-200">{{ $item->nama }}</span>
                                <span class="text-xs text-slate-400">{{ \App\Models\Achievement::KATEGORI[$item->kategori] }}</span>
                            </td>
                            <td>
                                <span class="badge badge-orange">{{ \App\Models\Achievement::TINGKAT[$item->tingkat] }}</span>
                            </td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->tanggal?->format('d M Y') ?? '—' }}</td>
                            <td class="text-right">
                                @can('prestasi.delete')
                                    <button wire:click="destroy({{ $item->id }})" wire:confirm="Hapus prestasi ini?"
                                            class="text-xs font-semibold text-danger hover:underline">Hapus</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">Belum ada prestasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
