<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Survei</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Survei' : 'Buat Survei' }}</h2>
            <p>Survei beserta periode, target, dan daftar pertanyaannya.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('survei.index') }}" class="back-link">← Survei</a>
        </div>
    </div>

    <section class="card management-form-card" aria-labelledby="survei-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="clipboard-list" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="survei-form-title">Data Survei</h3>
                    <p>Pertanyaan ditambahkan di bawah sebelum survei disimpan.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="form-grid management-form-grid">
            <div class="form-group management-field-span">
                <label for="judul">Judul <span class="required">*</span></label>
                <input id="judul" type="text" wire:model="judul" maxlength="200" class="field">
                @error('judul') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group management-field-span">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" wire:model="deskripsi" rows="3" class="field"></textarea>
                @error('deskripsi') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="mulai">Dibuka</label>
                <input id="mulai" type="datetime-local" wire:model="mulai" class="field">
                @error('mulai') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="selesai">Ditutup</label>
                <input id="selesai" type="datetime-local" wire:model="selesai" class="field">
                @error('selesai') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" wire:model="status" class="select-field">
                    @foreach (\App\Models\Survey::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="target">Target</label>
                <select id="target" wire:model="target_type" class="select-field">
                    @foreach (\App\Models\Survey::TARGET as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('target_type') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="management-field-span pt-2">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold">Pertanyaan</h3>
                    <button type="button" wire:click="tambahPertanyaan" class="text-xs font-semibold text-primary hover:underline">+ Tambah Pertanyaan</button>
                </div>
                @error('questions') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror

                @foreach ($questions as $i => $q)
                    <div wire:key="survei-pertanyaan-{{ $i }}" class="rounded-lg border border-slate-200 dark:border-slate-700 p-4 mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-400">Pertanyaan {{ $i + 1 }}</span>
                            <button type="button" wire:click="hapusPertanyaan({{ $i }})" class="text-xs font-semibold text-danger hover:underline">Hapus</button>
                        </div>
                        <textarea wire:model="questions.{{ $i }}.pertanyaan" rows="2" placeholder="Tulis pertanyaan…" class="field mb-2"></textarea>
                        @error("questions.$i.pertanyaan") <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                        <div class="grid sm:grid-cols-3 gap-2">
                            <select wire:model="questions.{{ $i }}.tipe" class="select-field">
                                @foreach (\App\Models\SurveyQuestion::TIPE as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @if (($q['tipe'] ?? '') === 'pilihan_ganda')
                                <textarea wire:model="questions.{{ $i }}.opsi" rows="2" placeholder="Satu opsi per baris…" class="field sm:col-span-2"></textarea>
                            @else
                                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <input type="checkbox" wire:model="questions.{{ $i }}.wajib" class="w-4 h-4 accent-primary"> Wajib diisi
                                </label>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="form-actions management-field-span">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Buat Survei' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <a href="{{ route('survei.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </section>
</div>
