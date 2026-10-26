<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('mapel.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Mata Pelajaran
            </a>
            <span class="eyebrow">Akademik / Data Master</span>
            <h2 class="management-page-title">{{ $subject?->exists ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran' }}</h2>
            <p>{{ $subject?->exists ? 'Perbarui kode, nama, kelompok, dan KKM default mata pelajaran.' : 'Lengkapi kode, nama, kelompok, dan KKM default mata pelajaran. Kolom bertanda bintang wajib diisi.' }}</p>
        </div>
    </div>

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="subject-data-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="book-open" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="subject-data-title">Data Mata Pelajaran</h3>
                        <p>Identitas mapel dan KKM default.</p>
                    </div>
                </div>
            </div>

            <div class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="kode">Kode <span class="required">*</span></label>
                    <input id="kode" type="text" wire:model="kode" placeholder="cth. MTK, BIN" required class="field">
                    @error('kode') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="nama">Nama Mata Pelajaran <span class="required">*</span></label>
                    <input id="nama" type="text" wire:model="nama" placeholder="cth. Matematika" required class="field">
                    @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="kelompok">Kelompok <span class="required">*</span></label>
                    <select id="kelompok" wire:model="kelompok" class="select-field">
                        @foreach ($kelompokOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('kelompok') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="kkm_default">KKM Default <span class="required">*</span></label>
                    <input id="kkm_default" type="number" wire:model="kkm_default" min="0" max="100" step="0.01" required class="field">
                    @error('kkm_default') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('mapel.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save"><x-icon name="check" class="h-4 w-4" /> Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </section>
    </form>
</div>
