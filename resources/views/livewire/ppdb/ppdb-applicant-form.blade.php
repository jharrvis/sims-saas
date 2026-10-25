<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('ppdb.pendaftar.index', ['period' => $period->id]) }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Daftar Pendaftar
            </a>
            <span class="eyebrow">PPDB / Pendaftar</span>
            <h2 class="management-page-title">Daftarkan Pendaftar</h2>
            <p>Periode: <strong>{{ $period->nama }}</strong></p>
        </div>
    </div>

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="ppdb-form-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="user-plus" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="ppdb-form-title">Data Pendaftar</h3>
                        <p>Identitas pendaftar dan kontak orang tua/wali.</p>
                    </div>
                </div>
            </div>

            <div class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span class="required">*</span></label>
                    <input id="nama" type="text" wire:model="nama" required class="field">
                    @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="nisn">NISN</label>
                    <input id="nisn" type="text" wire:model="nisn" class="field">
                    @error('nisn') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="jk">Jenis Kelamin <span class="required">*</span></label>
                    <select id="jk" wire:model="jk" class="select-field">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                    @error('jk') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="tgl_lahir">Tanggal Lahir</label>
                    <input id="tgl_lahir" type="date" wire:model="tgl_lahir" class="field">
                    @error('tgl_lahir') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span">
                    <label for="asal_sekolah">Asal Sekolah</label>
                    <input id="asal_sekolah" type="text" wire:model="asal_sekolah" class="field">
                    @error('asal_sekolah') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="nama_ortu">Nama Orang Tua/Wali</label>
                    <input id="nama_ortu" type="text" wire:model="nama_ortu" class="field">
                    @error('nama_ortu') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="no_hp_ortu">No. HP Orang Tua</label>
                    <input id="no_hp_ortu" type="text" wire:model="no_hp_ortu" class="field">
                    @error('no_hp_ortu') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('ppdb.pendaftar.index', ['period' => $period->id]) }}" class="btn btn-outline">Batal</a>
                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary" wire:target="save">
                    <span wire:loading.remove wire:target="save">Simpan Pendaftar</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </section>
    </form>
</div>
