<div class="max-w-3xl">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div>
            <label for="judul" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Judul <span class="text-danger">*</span></label>
            <input id="judul" type="text" wire:model="judul" maxlength="200"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            @error('judul') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="isi" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Isi <span class="text-danger">*</span></label>
            <textarea id="isi" wire:model="isi" rows="6"
                      class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
            @error('isi') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Target <span class="text-danger">*</span></span>
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
            <div>
                <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Pilih peran</span>
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
            <div>
                <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Pilih kelas</span>
                <div class="grid sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                    @foreach ($rombels as $rombel)
                        <label class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs cursor-pointer text-slate-600 dark:text-slate-300">
                            <input type="checkbox" wire:model="target_rombels" value="{{ $rombel->id }}" class="accent-primary"> {{ $rombel->nama }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="tayang_dari" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tayang dari <span class="text-danger">*</span></label>
                <input id="tayang_dari" type="date" wire:model="tayang_dari"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('tayang_dari') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tayang_sampai" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tayang sampai <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input id="tayang_sampai" type="date" wire:model="tayang_sampai"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('tayang_sampai') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
            <input type="checkbox" wire:model="penting" class="accent-danger w-4 h-4"> Tandai sebagai <strong>penting</strong>
        </label>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Draf' }}
            </button>
            <a href="{{ route('pengumuman.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
