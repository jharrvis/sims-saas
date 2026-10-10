<div class="max-w-3xl">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="nama" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama Organisasi <span class="text-danger">*</span></label>
                <input id="nama" type="text" wire:model="nama" maxlength="150" placeholder="OSIS, Pramuka Ambalan…"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="jenis" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Jenis</label>
                <select id="jenis" wire:model="jenis"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    @foreach (\App\Models\Organization::JENIS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="pembina" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Pembina</label>
                <select id="pembina" wire:model="pembina_id"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    <option value="">— Pilih pembina —</option>
                    @foreach ($pembinas as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 sm:col-span-2">
                <input id="aktif" type="checkbox" wire:model="aktif" class="w-4 h-4 accent-primary">
                <label for="aktif" class="text-sm text-slate-600 dark:text-slate-300">Aktif</label>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Organisasi' }}
            </button>
            <a href="{{ route('organisasi.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
