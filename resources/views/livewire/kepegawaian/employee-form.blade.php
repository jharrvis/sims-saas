<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('guru.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Direktori Guru
            </a>
            <span class="eyebrow">Kepegawaian / Data Induk</span>
            <h2 class="management-page-title">{{ $employee?->exists ? 'Ubah Data Guru & Pegawai' : 'Tambah Guru & Pegawai' }}</h2>
            <p>{{ $employee?->exists ? 'Perbarui identitas, pendidikan, dan status kepegawaian. Kolom bertanda bintang wajib diisi.' : 'Lengkapi identitas, pendidikan, dan status kepegawaian guru atau tenaga kependidikan.' }}</p>
        </div>
    </div>

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="employee-foto-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="user" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="employee-foto-title">Foto Guru</h3>
                        <p>Opsional. JPG / PNG / WebP, maksimal 2 MB.</p>
                    </div>
                </div>
            </div>

            <div class="management-upload-zone"
                 ondragover="event.preventDefault(); this.classList.add('is-dragover')"
                 ondragleave="this.classList.remove('is-dragover')"
                 ondrop="event.preventDefault(); this.classList.remove('is-dragover'); const f = event.dataTransfer.files[0]; if (f) { const dt = new DataTransfer(); dt.items.add(f); const inp = document.getElementById('foto-input'); inp.files = dt.files; inp.dispatchEvent(new Event('change', {bubbles: true})); }"
                 onclick="document.getElementById('foto-input').click()">
                <div class="management-upload-preview">
                    @if ($foto)
                        <img src="{{ $foto->temporaryUrl() }}" alt="Pratinjau foto" class="management-upload-preview">
                    @elseif ($employee?->fotoUrl())
                        <img src="{{ $employee->fotoUrl() }}" alt="Foto {{ $employee->nama }}" class="management-upload-preview">
                    @else
                        @php
                            $namaParts = preg_split('/\s+/', trim($nama ?: ''));
                            $inisial = !empty($namaParts[0]) ? strtoupper(mb_substr($namaParts[0], 0, 1) . (count($namaParts) > 1 ? mb_substr(end($namaParts), 0, 1) : '')) : '?';
                        @endphp
                        <span class="management-upload-initials">{{ $inisial }}</span>
                    @endif
                </div>
                <p class="management-upload-hint">Seret &amp; letakkan foto di sini, atau <span class="management-upload-link">pilih file</span></p>
                <p class="helper-text">Foto otomatis dikompresi (maks 512×512, JPEG) agar halaman tetap ringan.</p>
                @error('foto') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <input type="file" id="foto-input" wire:model="foto" accept="image/jpeg,image/png,image/webp" class="hidden">

            @if ($employee?->foto_path)
                <label class="toggle-row" for="hapus-foto">
                    <input type="checkbox" id="hapus-foto" wire:model="hapusFoto">
                    <span class="helper-text">Hapus foto saat ini (kembali ke avatar inisial).</span>
                </label>
            @endif
        </section>

        <section class="card management-form-card" aria-labelledby="employee-identitas-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="contact-round" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="employee-identitas-title">Identitas &amp; Kontak</h3>
                        <p>Data utama dan bisa dihubungi.</p>
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
                    <label for="nip_nuptk">NIP / NUPTK</label>
                    <input id="nip_nuptk" type="text" wire:model="nip_nuptk" class="field">
                    @error('nip_nuptk') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="nik">NIK</label>
                    <input id="nik" type="text" wire:model="nik" inputmode="numeric" class="field">
                    @error('nik') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="jk">Jenis Kelamin</label>
                    <select id="jk" wire:model="jk" class="select-field">
                        <option value="">—</option>
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
                        <option value="">—</option>
                        @foreach (\App\Models\Employee::AGAMAS as $agamaOption)
                            <option value="{{ $agamaOption }}">{{ $agamaOption }}</option>
                        @endforeach
                    </select>
                    @error('agama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" wire:model="email" class="field" autocomplete="email">
                    @error('email') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="phone">No. HP</label>
                    <input id="phone" type="tel" wire:model="phone" placeholder="cth. 6281234567890" class="field" autocomplete="tel">
                    @error('phone') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" wire:model="alamat" rows="2" class="textarea-field"></textarea>
                    @error('alamat') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="card management-form-card" aria-labelledby="employee-pendidikan-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon purple"><x-icon name="scroll-text" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="employee-pendidikan-title">Pendidikan</h3>
                        <p>Riwayat pendidikan formal terakhir.</p>
                    </div>
                </div>
            </div>

            <div class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="pendidikan_terakhir">Pendidikan Terakhir</label>
                    <select id="pendidikan_terakhir" wire:model="pendidikan_terakhir" class="select-field">
                        <option value="">—</option>
                        @foreach (\App\Models\Employee::PENDIDIKAN as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('pendidikan_terakhir') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="tahun_lulus">Tahun Lulus</label>
                    <input id="tahun_lulus" type="number" wire:model="tahun_lulus" min="1950" max="{{ date('Y') }}" placeholder="cth. 2015" class="field">
                    @error('tahun_lulus') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="jurusan">Jurusan</label>
                    <input id="jurusan" type="text" wire:model="jurusan" placeholder="cth. Pendidikan Matematika" class="field">
                    @error('jurusan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="perguruan_tinggi">Perguruan Tinggi / Sekolah</label>
                    <input id="perguruan_tinggi" type="text" wire:model="perguruan_tinggi" class="field">
                    @error('perguruan_tinggi') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="card management-form-card" aria-labelledby="employee-kepegawaian-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon green"><x-icon name="briefcase" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="employee-kepegawaian-title">Kepegawaian</h3>
                        <p>Status, jabatan, dan masa kerja.</p>
                    </div>
                </div>
            </div>

            <div class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="jenis">Jenis <span class="required">*</span></label>
                    <select id="jenis" wire:model="jenis" class="select-field">
                        <option value="guru_mapel">Guru Mapel</option>
                        <option value="guru_kelas">Guru Kelas</option>
                        <option value="tendik">Tendik</option>
                    </select>
                    @error('jenis') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="status_kepegawaian">Status Kepegawaian <span class="required">*</span></label>
                    <select id="status_kepegawaian" wire:model="status_kepegawaian" class="select-field">
                        <option value="pns">PNS</option>
                        <option value="pppk">PPPK</option>
                        <option value="honorer">Honorer</option>
                        <option value="kontrak">Kontrak</option>
                    </select>
                    @error('status_kepegawaian') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="tmt">TMT (Terhitung Mulai Tanggal)</label>
                    <input id="tmt" type="date" wire:model="tmt" class="field">
                    @error('tmt') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="golongan">Golongan / Pangkat</label>
                    <select id="golongan" wire:model="golongan" class="select-field">
                        <option value="">—</option>
                        @foreach (\App\Models\Employee::GOLONGAN as $gol)
                            <option value="{{ $gol }}">{{ $gol }}</option>
                        @endforeach
                    </select>
                    @error('golongan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="sertifikasi">Sertifikasi</label>
                    <select id="sertifikasi" wire:model="sertifikasi" class="select-field">
                        <option value="">—</option>
                        @foreach (\App\Models\Employee::SERTIFIKASI as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('sertifikasi') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="no_sertifikat">No. Sertifikat Pendidik</label>
                    <input id="no_sertifikat" type="text" wire:model="no_sertifikat" class="field">
                    @error('no_sertifikat') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span">
                    <label for="jabatan">Jabatan</label>
                    <input id="jabatan" type="text" wire:model="jabatan" placeholder="cth. Kepala Sekolah" class="field">
                    @error('jabatan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="management-field-span">
                    <label class="toggle-row" for="status_aktif">
                        <input type="checkbox" id="status_aktif" wire:model="status_aktif">
                        <span class="helper-text">Status aktif — centang selama pegawai masih bertugas aktif.</span>
                    </label>
                    @error('status_aktif') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('guru.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save"><x-icon name="check" class="h-4 w-4" /> Simpan Data</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </section>
    </form>
</div>
