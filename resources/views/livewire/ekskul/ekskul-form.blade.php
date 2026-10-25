<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Ekstrakurikuler</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Ekstrakurikuler' : 'Tambah Ekstrakurikuler' }}</h2>
            <p>Ekstrakurikuler beserta jadwal latihan dan pembinanya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('ekskul.index') }}" class="back-link">← Ekstrakurikuler</a>
        </div>
    </div>

    <section class="card management-form-card" aria-labelledby="ekskul-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="award" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="ekskul-form-title">Data Ekstrakurikuler</h3>
                    <p>Anggota diatur dari halaman kelola ekstrakurikuler.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="form-grid management-form-grid">
            <div class="form-group management-field-span">
                <label for="nama">Nama <span class="required">*</span></label>
                <input id="nama" type="text" wire:model="nama" maxlength="150" placeholder="Pramuka, Paskibra, Sepak Bola…" class="field">
                @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="jadwal">Jadwal Latihan</label>
                <input id="jadwal" type="text" wire:model="jadwal" maxlength="100" placeholder="Senin 15.30–17.00" class="field">
                @error('jadwal') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
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
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Ekstrakurikuler' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <a href="{{ route('ekskul.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </section>
</div>
