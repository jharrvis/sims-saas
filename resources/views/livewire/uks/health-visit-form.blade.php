<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / UKS</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Kunjungan' : 'Catat Kunjungan' }}</h2>
            <p>Kunjungan siswa ke UKS beserta keluhan, diagnosa, dan tindakannya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('uks.index') }}" class="back-link">← Kunjungan UKS</a>
        </div>
    </div>

    <section class="card management-form-card" aria-labelledby="kunjungan-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="heart-pulse" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="kunjungan-form-title">Data Kunjungan</h3>
                    <p>Tindakan dan rujukan dicatat untuk tindak lanjut petugas UKS.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="form-grid management-form-grid">
            <div class="form-group">
                <label for="siswa">Siswa <span class="required">*</span></label>
                <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama…" class="field">
                <select wire:model="student_id" aria-label="Pilih siswa" class="select-field mt-1">
                    <option value="">— Pilih siswa —</option>
                    @foreach ($siswaList as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                    @endforeach
                </select>
                @error('student_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="tgl">Waktu Kunjungan <span class="required">*</span></label>
                <input id="tgl" type="datetime-local" wire:model="tgl" class="field">
                @error('tgl') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group management-field-span">
                <label for="keluhan">Keluhan <span class="required">*</span></label>
                <textarea id="keluhan" wire:model="keluhan" rows="3" class="field"></textarea>
                @error('keluhan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="diagnosa">Diagnosa</label>
                <input id="diagnosa" type="text" wire:model="diagnosa" maxlength="200" class="field">
                @error('diagnosa') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="tindakan">Tindakan</label>
                <input id="tindakan" type="text" wire:model="tindakan" maxlength="2000" class="field">
                @error('tindakan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="suhu">Suhu Tubuh (°C)</label>
                <input id="suhu" type="number" wire:model="suhu" min="30" max="45" step="0.1" class="field">
                @error('suhu') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="td">Tekanan Darah</label>
                <input id="td" type="text" wire:model="tekanan_darah" maxlength="20" placeholder="120/80" class="field">
                @error('tekanan_darah') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group management-field-span">
                <label for="rujukan">Rujukan</label>
                <select id="rujukan" wire:model="rujukan" class="select-field">
                    @foreach (\App\Models\HealthVisit::RUJUKAN as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('rujukan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions management-field-span">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Catat Kunjungan' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <a href="{{ route('uks.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </section>
</div>
