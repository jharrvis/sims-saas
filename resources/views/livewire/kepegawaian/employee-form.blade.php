<div class="max-w-3xl mx-auto">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="nama" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-danger">*</span></label>
                <input id="nama" type="text" wire:model="nama" required
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                @error('nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="nip_nuptk" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">NIP / NUPTK</label>
                <input id="nip_nuptk" type="text" wire:model="nip_nuptk"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="jenis" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Jenis <span class="text-danger">*</span></label>
                <select id="jenis" wire:model="jenis" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    <option value="guru_mapel">Guru Mapel</option>
                    <option value="guru_kelas">Guru Kelas</option>
                    <option value="tendik">Tendik</option>
                </select>
            </div>
            <div>
                <label for="status_kepegawaian" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Status Kepegawaian <span class="text-danger">*</span></label>
                <select id="status_kepegawaian" wire:model="status_kepegawaian" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    <option value="pns">PNS</option>
                    <option value="pppk">PPPK</option>
                    <option value="honorer">Honorer</option>
                    <option value="kontrak">Kontrak</option>
                </select>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="jabatan" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Jabatan</label>
                <input id="jabatan" type="text" wire:model="jabatan" placeholder="cth. Kepala Sekolah"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div class="flex items-end pb-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="status_aktif" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary">
                    <span class="text-sm text-slate-700 dark:text-slate-300">Status aktif</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('guru.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                Simpan
            </button>
        </div>
    </form>
</div>
