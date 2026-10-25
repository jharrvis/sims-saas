<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('aset.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Data Aset
            </a>
            <span class="eyebrow">Operasional / Inventaris</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Aset' : 'Tambah Aset' }}</h2>
            <p>Data inventaris beserta kategori, lokasi, dan sumber dananya.</p>
        </div>
    </div>

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="aset-form-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="package" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="aset-form-title">Data Aset</h3>
                        <p>Kode, nama, dan detail perolehan aset.</p>
                    </div>
                </div>
            </div>

            <div class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="kode">Kode <span class="required">*</span></label>
                    <input id="kode" type="text" wire:model="kode" maxlength="50" class="field">
                    @error('kode') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="nama">Nama <span class="required">*</span></label>
                    <input id="nama" type="text" wire:model="nama" maxlength="200" class="field">
                    @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" wire:model="asset_category_id" class="select-field">
                        <option value="">— Baru (isi di bawah) —</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->nama }}</option>
                        @endforeach
                    </select>
                    @error('asset_category_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="kondisi">Kondisi</label>
                    <select id="kondisi" wire:model="kondisi" class="select-field">
                        @foreach (\App\Models\Asset::KONDISI as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('kondisi') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                    <p class="text-xs font-semibold text-slate-500 mb-2">Atau buat kategori baru:</p>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <input type="text" wire:model="new_kategori_kode" maxlength="30" placeholder="Kode kategori (mis. ELK)" aria-label="Kode kategori baru" class="field">
                        <input type="text" wire:model="new_kategori_nama" maxlength="150" placeholder="Nama kategori (mis. Elektronik)" aria-label="Nama kategori baru" class="field">
                    </div>
                </div>
                <div class="form-group">
                    <label for="lokasi">Lokasi</label>
                    <input id="lokasi" type="text" wire:model="lokasi" maxlength="150" placeholder="Ruang Guru, Lab Komputer…" class="field">
                    @error('lokasi') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="sumber">Sumber Dana</label>
                    <input id="sumber" type="text" wire:model="sumber_dana" maxlength="100" placeholder="BOS, Hibah…" class="field">
                    @error('sumber_dana') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="tglbeli">Tanggal Beli</label>
                    <input id="tglbeli" type="date" wire:model="tgl_beli" class="field">
                    @error('tgl_beli') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="harga">Harga Beli (Rp)</label>
                    <input id="harga" type="number" wire:model="harga_beli" min="0" class="field">
                    @error('harga_beli') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('aset.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Aset' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </section>
    </form>
</div>
