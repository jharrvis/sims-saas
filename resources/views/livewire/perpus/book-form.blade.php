<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('perpus.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Katalog
            </a>
            <span class="eyebrow">Operasional / Perpustakaan</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Buku' : 'Tambah Buku' }}</h2>
            <p>Data katalog buku perpustakaan sekolah.</p>
        </div>
    </div>

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="buku-form-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="book-open" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="buku-form-title">Data Buku</h3>
                        <p>Kode, judul, dan detail terbitan.</p>
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
                    <label for="kategori">Kategori</label>
                    <input id="kategori" type="text" wire:model="kategori" maxlength="100" class="field">
                    @error('kategori') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span">
                    <label for="judul">Judul <span class="required">*</span></label>
                    <input id="judul" type="text" wire:model="judul" maxlength="200" class="field">
                    @error('judul') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="pengarang">Pengarang</label>
                    <input id="pengarang" type="text" wire:model="pengarang" maxlength="150" class="field">
                    @error('pengarang') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="penerbit">Penerbit</label>
                    <input id="penerbit" type="text" wire:model="penerbit" maxlength="150" class="field">
                    @error('penerbit') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="tahun">Tahun Terbit</label>
                    <input id="tahun" type="number" wire:model="tahun_terbit" min="1900" max="2100" class="field">
                    @error('tahun_terbit') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input id="isbn" type="text" wire:model="isbn" maxlength="30" class="field">
                    @error('isbn') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="jumlah">Jumlah <span class="required">*</span></label>
                    <input id="jumlah" type="number" wire:model="jumlah" min="1" max="10000" class="field">
                    @error('jumlah') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('perpus.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Buku' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </section>
    </form>
</div>
