<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / LMS</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Tugas' : 'Buat Tugas' }}</h2>
            <p>Tugas kelas beserta mapel, deadline, dan instruksinya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('lms.index') }}" class="back-link">← Tugas</a>
        </div>
    </div>

    <section class="card management-form-card" aria-labelledby="tugas-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="book-open" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="tugas-form-title">Data Tugas</h3>
                    <p>Pengumpulan dan penilaian diatur dari halaman detail tugas.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="form-grid management-form-grid">
            <div class="form-group management-field-span">
                <label for="judul">Judul <span class="required">*</span></label>
                <input id="judul" type="text" wire:model="judul" maxlength="200" class="field">
                @error('judul') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="mapel">Mata Pelajaran <span class="required">*</span></label>
                <select id="mapel" wire:model="subject_id" class="select-field">
                    <option value="">— Pilih mapel —</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                    @endforeach
                </select>
                @error('subject_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="rombel">Kelas <span class="required">*</span></label>
                <select id="rombel" wire:model="rombel_id" class="select-field">
                    <option value="">— Pilih kelas —</option>
                    @foreach ($rombels as $r)
                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                    @endforeach
                </select>
                @error('rombel_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="deadline">Deadline <span class="required">*</span></label>
                <input id="deadline" type="datetime-local" wire:model="deadline" class="field">
                @error('deadline') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group management-field-span">
                <label for="deskripsi">Deskripsi / Instruksi</label>
                <textarea id="deskripsi" wire:model="deskripsi" rows="5" class="field"></textarea>
                @error('deskripsi') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions management-field-span">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Buat Tugas' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <a href="{{ route('lms.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </section>
</div>
