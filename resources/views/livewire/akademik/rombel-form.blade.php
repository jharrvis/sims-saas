<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('kelas.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Data Kelas
            </a>
            <span class="eyebrow">Akademik / Data Kelas</span>
            <h2 class="management-page-title">{{ $rombel?->exists ? 'Ubah Kelas' : 'Tambah Kelas' }}</h2>
            <p>{{ $rombel?->exists ? 'Perbarui tahun ajaran, tingkat, nama, kapasitas, dan wali kelas.' : 'Lengkapi tahun ajaran, tingkat, nama, kapasitas, dan wali kelas. Kolom bertanda bintang wajib diisi.' }}</p>
        </div>
    </div>

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="rombel-data-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="school" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="rombel-data-title">Data Kelas</h3>
                        <p>Identitas rombel dan wali kelas.</p>
                    </div>
                </div>
            </div>

            <div class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="academic_year_id">Tahun Ajaran <span class="required">*</span></label>
                    <select id="academic_year_id" wire:model="academic_year_id" class="select-field">
                        @foreach ($tahunAjaran as $ta)
                            <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                        @endforeach
                    </select>
                    @error('academic_year_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="tingkat">Tingkat <span class="required">*</span></label>
                    <input id="tingkat" type="text" wire:model="tingkat" placeholder="cth. 7, 12, XII" required class="field">
                    @error('tingkat') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="nama">Nama Kelas <span class="required">*</span></label>
                    <input id="nama" type="text" wire:model="nama" placeholder="cth. VII A" required class="field">
                    @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="kapasitas">Kapasitas <span class="required">*</span></label>
                    <input id="kapasitas" type="number" wire:model="kapasitas" min="1" max="100" required class="field">
                    @error('kapasitas') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span">
                    <label for="wali_kelas_id">Wali Kelas</label>
                    <select id="wali_kelas_id" wire:model="wali_kelas_id" class="select-field">
                        <option value="">— Belum ditentukan —</option>
                        @foreach ($gurus as $guru)
                            <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                        @endforeach
                    </select>
                    @error('wali_kelas_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('kelas.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save"><x-icon name="check" class="h-4 w-4" /> Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </section>
    </form>
</div>
