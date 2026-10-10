<div class="max-w-3xl">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="nama" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama Rute <span class="text-danger">*</span></label>
                <input id="nama" type="text" wire:model="nama" maxlength="150" placeholder="Rute Utara, Rute Kota…"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="sopir" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Sopir</label>
                <input id="sopir" type="text" wire:model="sopir" maxlength="150"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="nopol" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nomor Kendaraan</label>
                <input id="nopol" type="text" wire:model="no_kendaraan" maxlength="30"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="kapasitas" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kapasitas <span class="text-danger">*</span></label>
                <input id="kapasitas" type="number" wire:model="kapasitas" min="0" max="100"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('kapasitas') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end pb-2">
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                    <input type="checkbox" wire:model="aktif" class="w-4 h-4 accent-primary"> Aktif
                </label>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Rute' }}
            </button>
            <a href="{{ route('transportasi.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
