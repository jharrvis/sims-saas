<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Alumni</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Alumni' : 'Tambah Alumni' }}</h2>
            <p>Data alumni beserta tahun lulus dan kontaknya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('alumni.index') }}" class="back-link">← Alumni</a>
        </div>
    </div>

    <section class="card management-form-card" aria-labelledby="alumni-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="graduation-cap" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="alumni-form-title">Data Alumni</h3>
                    <p>Tautan ke data siswa bersifat opsional.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="form-grid management-form-grid">
            <div class="form-group">
                <label for="siswa">Tautkan ke Data Siswa <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama…" class="field">
                <select wire:model.live="student_id" aria-label="Pilih siswa" class="select-field mt-1">
                    <option value="">— Tanpa tautan —</option>
                    @foreach ($siswaList as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                    @endforeach
                </select>
                @error('student_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="nama">Nama <span class="required">*</span></label>
                <input id="nama" type="text" wire:model="nama" maxlength="150" class="field">
                @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="tahun">Tahun Lulus</label>
                <input id="tahun" type="text" wire:model="tahun_lulus" maxlength="9" placeholder="2026" class="field">
                @error('tahun_lulus') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="phone">Nomor WA</label>
                <input id="phone" type="text" wire:model="phone" maxlength="20" class="field">
                @error('phone') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" wire:model="email" maxlength="150" class="field">
                @error('email') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group management-field-span">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" wire:model="alamat" rows="3" class="field"></textarea>
                @error('alamat') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions management-field-span">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Alumni' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <a href="{{ route('alumni.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </section>
</div>
