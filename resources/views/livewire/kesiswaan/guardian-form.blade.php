<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('wali.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Direktori Wali
            </a>
            <span class="eyebrow">Kesiswaan / Data Induk</span>
            <h2 class="management-page-title">{{ $guardian?->exists ? 'Ubah Data Wali Murid' : 'Tambah Wali Murid' }}</h2>
            <p>{{ $guardian?->exists ? 'Perbarui identitas dan kontak wali murid. Kolom bertanda bintang wajib diisi.' : 'Lengkapi identitas dan kontak wali murid. Nomor WA dipakai untuk OTP dan notifikasi. Kolom bertanda bintang wajib diisi.' }}</p>
        </div>
    </div>

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="guardian-identitas-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="user-round-check" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="guardian-identitas-title">Identitas &amp; Kontak</h3>
                        <p>Data utama dan nomor yang dapat dihubungi.</p>
                    </div>
                </div>
            </div>

            <div class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span class="required">*</span></label>
                    <input id="nama" type="text" wire:model="nama" required class="field" autocomplete="name">
                    @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="hubungan">Hubungan Utama <span class="required">*</span></label>
                    <select id="hubungan" wire:model="hubungan" required class="select-field">
                        <option value="ayah">Ayah</option>
                        <option value="ibu">Ibu</option>
                        <option value="wali">Wali</option>
                    </select>
                    @error('hubungan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="nik">NIK</label>
                    <input id="nik" type="text" wire:model="nik" inputmode="numeric" class="field">
                    @error('nik') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="pekerjaan">Pekerjaan</label>
                    <input id="pekerjaan" type="text" wire:model="pekerjaan" class="field">
                    @error('pekerjaan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="no_wa">Nomor WA <span class="required">*</span></label>
                    <input id="no_wa" type="tel" wire:model="no_wa" required placeholder="cth. 6281234567890" class="field" autocomplete="tel">
                    @error('no_wa') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" wire:model="email" class="field" autocomplete="email">
                    @error('email') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" wire:model="alamat" rows="2" class="textarea-field"></textarea>
                    @error('alamat') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <div class="page-actions management-form-actions">
            <a href="{{ route('wali.index') }}" class="btn btn-outline">Batal</a>
            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary">
                <span wire:loading.remove wire:target="save"><x-icon name="check" class="h-4 w-4" /> {{ $guardian?->exists ? 'Simpan Perubahan' : 'Simpan Wali' }}</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
