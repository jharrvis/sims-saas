<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Organisasi</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Organisasi' : 'Tambah Organisasi' }}</h2>
            <p>Organisasi siswa beserta jenis dan pembinanya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('organisasi.index') }}" class="back-link">← Organisasi</a>
        </div>
    </div>

    <section class="card management-form-card" aria-labelledby="organisasi-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="organisasi-form-title">Data Organisasi</h3>
                    <p>Pengurus dan kegiatan diatur dari halaman kelola organisasi.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="form-grid management-form-grid">
            <div class="form-group management-field-span">
                <label for="nama">Nama Organisasi <span class="required">*</span></label>
                <input id="nama" type="text" wire:model="nama" maxlength="150" placeholder="OSIS, Pramuka Ambalan…" class="field">
                @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="jenis">Jenis</label>
                <select id="jenis" wire:model="jenis" class="select-field">
                    @foreach (\App\Models\Organization::JENIS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="pembina">Pembina</label>
                <select id="pembina" wire:model="pembina_id" class="select-field">
                    <option value="">— Pilih pembina —</option>
                    @foreach ($pembinas as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
                    @endforeach
                </select>
                @error('pembina_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group management-field-span">
                <label class="toggle-row">
                    <input id="aktif" type="checkbox" wire:model="aktif">
                    <span class="toggle-control" aria-hidden="true"></span>
                    <span>Aktif</span>
                </label>
                @error('aktif') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions management-field-span">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Organisasi' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <a href="{{ route('organisasi.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </section>
</div>
