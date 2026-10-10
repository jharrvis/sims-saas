<div class="max-w-3xl mx-auto">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 space-y-5">
        <p class="text-sm text-slate-500">Periode: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $period->nama }}</span></p>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="nama" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-danger">*</span></label>
                <input id="nama" type="text" wire:model="nama" required
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                @error('nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="nisn" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">NISN</label>
                <input id="nisn" type="text" wire:model="nisn"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="jk" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Jenis Kelamin <span class="text-danger">*</span></label>
                <select id="jk" wire:model="jk" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm dark:text-white dark:bg-boxdark">
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div>
                <label for="tgl_lahir" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Tanggal Lahir</label>
                <input id="tgl_lahir" type="date" wire:model="tgl_lahir"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div>
            <label for="asal_sekolah" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Asal Sekolah</label>
            <input id="asal_sekolah" type="text" wire:model="asal_sekolah"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="nama_ortu" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nama Orang Tua/Wali</label>
                <input id="nama_ortu" type="text" wire:model="nama_ortu"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="no_hp_ortu" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">No. HP Orang Tua</label>
                <input id="no_hp_ortu" type="text" wire:model="no_hp_ortu"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('ppdb.pendaftar.index', ['period' => $period->id]) }}" class="px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                Simpan Pendaftar
            </button>
        </div>
    </form>
</div>
