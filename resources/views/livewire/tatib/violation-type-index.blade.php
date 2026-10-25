<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Tata Tertib</span>
            <h2 class="management-page-title">Jenis Pelanggaran</h2>
            <p>Jenis pelanggaran beserta kategori dan poinnya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('tatib.index') }}" class="back-link">← Kembali ke Daftar Pelanggaran</a>
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <section class="card management-form-card" aria-labelledby="jenis-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="alert-triangle" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="jenis-form-title">{{ $editId ? 'Ubah' : 'Tambah' }} Jenis Pelanggaran</h3>
                    <p>Poin dipakai untuk rekap poin siswa.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="form-grid management-form-grid">
            <div class="form-group management-field-span">
                <label for="jt-nama">Nama <span class="required">*</span></label>
                <input id="jt-nama" type="text" wire:model="nama" maxlength="150" placeholder="cth. Terlambat masuk kelas" class="field">
                @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="jt-kat">Kategori</label>
                <select id="jt-kat" wire:model="kategori" class="select-field">
                    @foreach (\App\Models\ViolationType::KATEGORI as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('kategori') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="jt-poin">Poin <span class="required">*</span></label>
                <input id="jt-poin" type="number" wire:model="poin" min="0" max="1000" class="field">
                @error('poin') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-actions management-field-span">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $editId ? 'Simpan' : 'Tambah' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                @if ($editId)
                    <button type="button" wire:click="cancelEdit" class="btn btn-outline">Batal</button>
                @endif
            </div>
        </form>
    </section>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Poin</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr wire:key="jenis-{{ $item->id }}">
                            <td class="font-semibold">{{ $item->nama }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ \App\Models\ViolationType::KATEGORI[$item->kategori] }}</td>
                            <td class="font-bold text-danger">{{ $item->poin }}</td>
                            <td>
                                <span class="badge {{ $item->aktif ? 'badge-green' : 'badge-gray' }}">
                                    {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <button wire:click="edit({{ $item->id }})" class="text-xs font-semibold text-primary hover:underline">Ubah</button>
                                <button wire:click="toggle({{ $item->id }})" class="ml-2 text-xs font-semibold text-slate-500 hover:text-primary">
                                    {{ $item->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
