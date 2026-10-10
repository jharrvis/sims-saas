<div class="max-w-3xl">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="judul" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Judul <span class="text-danger">*</span></label>
                <input id="judul" type="text" wire:model="judul" maxlength="200"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('judul') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="pesan" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Isi Pesan <span class="text-danger">*</span></label>
                <textarea id="pesan" wire:model="pesan" rows="5"
                          class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
                @error('pesan') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="channel" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Channel</label>
                <select id="channel" wire:model="channel"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    @foreach (\App\Models\Broadcast::CHANNEL as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="target" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Target</label>
                <select id="target" wire:model.live="target_type"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    @foreach (\App\Models\Broadcast::TARGET as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if ($target_type === 'rombel')
                <div class="sm:col-span-2">
                    <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Pilih Kelas</span>
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
            <div class="sm:col-span-2">
                <label for="jadwal" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Jadwal Kirim <span class="text-xs font-normal text-slate-400">(kosongkan untuk draf)</span></label>
                <input id="jadwal" type="datetime-local" wire:model="dijadwalkan_pada"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Buat Broadcast' }}
            </button>
            <a href="{{ route('broadcast.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
