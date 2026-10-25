<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / Transportasi</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Rute' : 'Tambah Rute' }}</h2>
            <p>Rute antar-jemput beserta kendaraan, sopir, dan kapasitasnya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('transportasi.index') }}" class="back-link">← Semua Rute</a>
        </div>
    </div>

    <section class="card management-form-card" aria-labelledby="rute-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="bus" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="rute-form-title">Data Rute</h3>
                    <p>Titik penjemputan dan penumpang diatur dari halaman kelola rute.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="form-grid management-form-grid">
            <div class="form-group management-field-span">
                <label for="nama">Nama Rute <span class="required">*</span></label>
                <input id="nama" type="text" wire:model="nama" maxlength="150" placeholder="Rute Utara, Rute Kota…" class="field">
                @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="sopir">Sopir</label>
                <input id="sopir" type="text" wire:model="sopir" maxlength="150" class="field">
                @error('sopir') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="nopol">Nomor Kendaraan</label>
                <input id="nopol" type="text" wire:model="no_kendaraan" maxlength="30" class="field">
                @error('no_kendaraan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="kapasitas">Kapasitas <span class="required">*</span></label>
                <input id="kapasitas" type="number" wire:model="kapasitas" min="0" max="100" class="field">
                @error('kapasitas') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="toggle-row">
                    <input type="checkbox" wire:model="aktif">
                    <span class="toggle-control" aria-hidden="true"></span>
                    <span>Aktif</span>
                </label>
                @error('aktif') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions management-field-span">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Rute' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <a href="{{ route('transportasi.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </section>
</div>
