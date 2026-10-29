<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('siswa.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Data Siswa
            </a>
            <span class="eyebrow">Kesiswaan / Data Siswa</span>
            <h2 class="management-page-title">{{ $student?->exists ? 'Ubah Siswa' : 'Tambah Siswa' }}</h2>
            <p>{{ $student?->exists ? 'Perbarui identitas, kontak, dan foto siswa. Kolom bertanda bintang wajib diisi.' : 'Lengkapi identitas, kontak, dan foto siswa. Kolom bertanda bintang wajib diisi.' }}</p>
        </div>
    </div>

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="student-identitas-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="graduation-cap" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="student-identitas-title">Data Siswa</h3>
                        <p>Identitas dan kontak siswa.</p>
                    </div>
                </div>
            </div>

            <div class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="nis">NIS <span class="required">*</span></label>
                    <input id="nis" type="text" wire:model="nis" required class="field">
                    @error('nis') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="nisn">NISN</label>
                    <input id="nisn" type="text" wire:model="nisn" class="field">
                    @error('nisn') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="angkatan">Angkatan (tahun masuk)</label>
                    <input id="angkatan" type="number" wire:model="angkatan" min="1900" max="2100" placeholder="cth. 2026" class="field">
                    @error('angkatan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span">
                    <label for="nama">Nama Lengkap <span class="required">*</span></label>
                    <input id="nama" type="text" wire:model="nama" required class="field">
                    @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
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
                    <label for="tempat_lahir">Tempat Lahir</label>
                    <input id="tempat_lahir" type="text" wire:model="tempat_lahir" class="field">
                    @error('tempat_lahir') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="tgl_lahir">Tanggal Lahir</label>
                    <input id="tgl_lahir" type="date" wire:model="tgl_lahir" class="field">
                    @error('tgl_lahir') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="agama">Agama</label>
                    <select id="agama" wire:model="agama" class="select-field">
                        <option value="">— Pilih Agama —</option>
                        @foreach (\App\Models\Student::AGAMAS as $pilihanAgama)
                            <option value="{{ $pilihanAgama }}">{{ $pilihanAgama }}</option>
                        @endforeach
                    </select>
                    @error('agama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="phone">No. HP</label>
                    <input id="phone" type="tel" wire:model="phone" class="field">
                    @error('phone') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" wire:model="alamat" rows="3" class="textarea-field"></textarea>
                    @error('alamat') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="card management-form-card" aria-labelledby="student-foto-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="user" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="student-foto-title">Foto Siswa</h3>
                        <p>Opsional. JPG / PNG / WebP, maksimal 2 MB.</p>
                    </div>
                </div>
            </div>

            <label class="management-upload-zone" for="foto-input"
                 ondragover="event.preventDefault(); this.classList.add('is-dragover')"
                 ondragleave="this.classList.remove('is-dragover')"
                 ondrop="event.preventDefault(); this.classList.remove('is-dragover'); const f = event.dataTransfer.files[0]; if (f) { const dt = new DataTransfer(); dt.items.add(f); const inp = document.getElementById('foto-input'); inp.files = dt.files; inp.dispatchEvent(new Event('change', {bubbles: true})); }">
                <div class="management-upload-preview">
                    @if ($foto)
                        <img src="{{ $foto->temporaryUrl() }}" alt="Pratinjau foto" class="management-upload-preview">
                    @elseif ($student?->fotoUrl())
                        <img src="{{ $student->fotoUrl() }}" alt="Foto {{ $student->nama }}" class="management-upload-preview">
                    @else
                        <span class="management-upload-initials">{{ strtoupper(substr($nama ?: '?', 0, 1)) }}</span>
                    @endif
                </div>
                <p class="management-upload-hint">Seret &amp; letakkan foto di sini, atau <span class="management-upload-link">pilih file</span></p>
                <p class="helper-text">Foto otomatis dikompresi (maks 512×512, JPEG) agar halaman tetap ringan.</p>
                @error('foto') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                <input type="file" id="foto-input" wire:model="foto" accept="image/jpeg,image/png,image/webp" class="sr-only">
            </label>

            @if ($student?->foto_path)
                <label class="toggle-row" for="hapus-foto">
                    <input type="checkbox" id="hapus-foto" wire:model="hapusFoto">
                    <span class="helper-text">Hapus foto saat ini (kembali ke avatar inisial).</span>
                </label>
            @endif

            <div class="form-actions">
                <a href="{{ route('siswa.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save"><x-icon name="check" class="h-4 w-4" /> Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </section>
    </form>
</div>
