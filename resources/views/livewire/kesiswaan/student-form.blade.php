<div class="max-w-3xl mx-auto">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="nis" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">NIS <span class="text-danger">*</span></label>
                <input id="nis" type="text" wire:model="nis" required
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                @error('nis') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="nisn" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">NISN</label>
                <input id="nisn" type="text" wire:model="nisn"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                @error('nisn') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="nama" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-danger">*</span></label>
            <input id="nama" type="text" wire:model="nama" required
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            @error('nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
        </div>

        <div class="grid sm:grid-cols-3 gap-5">
            <div>
                <label for="jk" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Jenis Kelamin <span class="text-danger">*</span></label>
                <select id="jk" wire:model="jk" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div>
                <label for="tempat_lahir" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Tempat Lahir</label>
                <input id="tempat_lahir" type="text" wire:model="tempat_lahir"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="tgl_lahir" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Tanggal Lahir</label>
                <input id="tgl_lahir" type="date" wire:model="tgl_lahir"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="agama" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Agama</label>
                <input id="agama" type="text" wire:model="agama"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="phone" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">No. HP</label>
                <input id="phone" type="tel" wire:model="phone"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div>
            <label for="alamat" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Alamat</label>
            <textarea id="alamat" wire:model="alamat" rows="3"
                      class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('siswa.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                Simpan
            </button>
        </div>
    </form>
</div>
