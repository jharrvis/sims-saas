<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('pengumuman.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Pengumuman
            </a>
            <span class="eyebrow">Informasi &amp; Sistem / Pengumuman</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Pengumuman' : 'Buat Pengumuman' }}</h2>
            <p>Judul, isi, target penerima, dan masa tayang pengumuman.</p>
        </div>
    </div>

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="pengumuman-form-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="megaphone" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="pengumuman-form-title">Isi Pengumuman</h3>
                        <p>Ditampilkan kepada target yang dipilih selama masa tayang.</p>
                    </div>
                </div>
            </div>

            <div class="form-grid management-form-grid">
                <div class="form-group management-field-span">
                    <label for="judul">Judul <span class="required">*</span></label>
                    <input id="judul" type="text" wire:model="judul" maxlength="200" class="field">
                    @error('judul') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span">
                    <label for="isi">Isi <span class="required">*</span></label>
                    <textarea id="isi" wire:model="isi" rows="6" class="textarea-field"></textarea>
                    @error('isi') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-group mt-5">
                <label>Target <span class="required">*</span></label>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\Announcement::TARGET_TYPES as $key => $label)
                        <label class="inline-flex items-center gap-2 rounded-lg border px-3.5 py-2 text-sm cursor-pointer {{ $target_type === $key ? 'border-primary bg-primary/5 text-primary font-semibold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}">
                            <input type="radio" wire:model.live="target_type" value="{{ $key }}" class="accent-primary">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            @if ($target_type === 'peran')
                <div class="form-group mt-5">
                    <label>Pilih peran</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($roles as $role)
                            <label class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs cursor-pointer text-slate-600 dark:text-slate-300">
                                <input type="checkbox" wire:model="target_roles" value="{{ $role }}" class="accent-primary"> {{ $role }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($target_type === 'kelas')
                <div class="form-group mt-5">
                    <label>Pilih kelas</label>
                    <div class="grid sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                        @foreach ($rombels as $rombel)
                            <label class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs cursor-pointer text-slate-600 dark:text-slate-300">
                                <input type="checkbox" wire:model="target_rombels" value="{{ $rombel->id }}" class="accent-primary"> {{ $rombel->nama }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="form-grid management-form-grid mt-5">
                <div class="form-group">
                    <label for="tayang_dari">Tayang dari <span class="required">*</span></label>
                    <input id="tayang_dari" type="date" wire:model="tayang_dari" class="field">
                    @error('tayang_dari') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="tayang_sampai">Tayang sampai <span class="helper-text">(opsional)</span></label>
                    <input id="tayang_sampai" type="date" wire:model="tayang_sampai" class="field">
                    @error('tayang_sampai') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <label class="toggle-row mt-5">
                <input type="checkbox" wire:model="penting" class="accent-danger w-4 h-4">
                <span class="helper-text">Tandai sebagai <strong>penting</strong></span>
            </label>

            <div class="form-actions">
                <a href="{{ route('pengumuman.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Draf' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </section>
    </form>
</div>
