<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / BK</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Sesi Konseling' : 'Jadwalkan Konseling' }}</h2>
            <p>Sesi konseling siswa beserta topik, catatan rahasia, dan tindak lanjutnya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('bk.index') }}" class="back-link">← Konseling</a>
        </div>
    </div>

    <section class="card management-form-card" aria-labelledby="konseling-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="lock" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="konseling-form-title">Data Sesi Konseling</h3>
                    <p>Catatan konseling bersifat rahasia dan hanya diakses pihak yang berwenang.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="form-grid management-form-grid">
            <div class="form-group management-field-span">
                <label for="siswa">Siswa <span class="required">*</span></label>
                <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Ketik nama siswa…" class="field">
                @if ($searchSiswa !== '' && ! $isEdit)
                    <div class="mt-1 rounded-lg border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700 max-h-48 overflow-y-auto">
                        @forelse ($siswaList as $s)
                            <button type="button" wire:click="$set('student_id', {{ $s->id }}); $set('searchSiswa', '{{ addslashes($s->nama) }}')"
                                    class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-800 {{ $student_id === $s->id ? 'bg-primary/5 font-semibold text-primary' : 'text-slate-700 dark:text-slate-300' }}">
                                {{ $s->nama }} <span class="text-xs text-slate-400">· {{ $s->nis }}</span>
                            </button>
                        @empty
                            <p class="px-3.5 py-2 text-xs text-slate-500">Tidak ditemukan.</p>
                        @endforelse
                    </div>
                @endif
                @error('student_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label for="tanggal">Tanggal <span class="required">*</span></label>
                <input id="tanggal" type="date" wire:model="tanggal" class="field">
                @error('tanggal') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="jenis">Jenis</label>
                <select id="jenis" wire:model="jenis" class="select-field">
                    @foreach (\App\Models\CounselingSession::JENIS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" wire:model="status" class="select-field">
                    @foreach (\App\Models\CounselingSession::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-group management-field-span">
                <label for="topik">Topik <span class="required">*</span></label>
                <input id="topik" type="text" wire:model="topik" maxlength="200" placeholder="cth. Motivasi belajar" class="field">
                @error('topik') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-group management-field-span">
                <label for="catatan">
                    Catatan Konseling <span class="text-xs font-normal text-amber-600">(rahasia)</span>
                </label>
                <textarea id="catatan" wire:model="catatan" rows="4" class="field"></textarea>
                @error('catatan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-group management-field-span">
                <label for="tindak">Tindak Lanjut</label>
                <input id="tindak" type="text" wire:model="tindak_lanjut" maxlength="200" class="field">
                @error('tindak_lanjut') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions management-field-span">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Jadwalkan' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <a href="{{ route('bk.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </section>
</div>
