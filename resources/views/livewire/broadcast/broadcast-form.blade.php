<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('broadcast.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Broadcast
            </a>
            <span class="eyebrow">Informasi &amp; Sistem / Broadcast</span>
            <h2 class="management-page-title">{{ $isEdit ? 'Ubah Broadcast' : 'Buat Broadcast' }}</h2>
            <p>Judul, isi pesan, channel, target, dan jadwal kirim.</p>
        </div>
    </div>

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="broadcast-form-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="send" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="broadcast-form-title">Isi Broadcast</h3>
                        <p>Kosongkan jadwal kirim untuk menyimpan sebagai draf.</p>
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
                    <label for="pesan">Isi Pesan <span class="required">*</span></label>
                    <textarea id="pesan" wire:model="pesan" rows="5" class="textarea-field"></textarea>
                    @error('pesan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="channel">Channel</label>
                    <select id="channel" wire:model="channel" class="select-field">
                        @foreach (\App\Models\Broadcast::CHANNEL as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('channel') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="target">Target</label>
                    <select id="target" wire:model.live="target_type" class="select-field">
                        @foreach (\App\Models\Broadcast::TARGET as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('target_type') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            @if ($target_type === 'rombel')
                <div class="form-group mt-5">
                    <label>Pilih Kelas</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach ($rombels as $r)
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" wire:model="rombel_ids" value="{{ $r->id }}" class="w-4 h-4 accent-primary">
                                {{ $r->nama }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="form-group management-field-span mt-5">
                <label for="jadwal">Jadwal Kirim <span class="helper-text">(kosongkan untuk draf)</span></label>
                <input id="jadwal" type="datetime-local" wire:model="dijadwalkan_pada" class="field">
                @error('dijadwalkan_pada') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('broadcast.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan Perubahan' : 'Buat Broadcast' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </section>
    </form>
</div>
