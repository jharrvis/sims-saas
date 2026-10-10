<div class="max-w-3xl">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="kode" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kode <span class="text-danger">*</span></label>
                <input id="kode" type="text" wire:model="kode" maxlength="50"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('kode') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kategori" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kategori</label>
                <input id="kategori" type="text" wire:model="kategori" maxlength="100"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div class="sm:col-span-2">
                <label for="judul" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Judul <span class="text-danger">*</span></label>
                <input id="judul" type="text" wire:model="judul" maxlength="200"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('judul') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="pengarang" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Pengarang</label>
                <input id="pengarang" type="text" wire:model="pengarang" maxlength="150"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="penerbit" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Penerbit</label>
                <input id="penerbit" type="text" wire:model="penerbit" maxlength="150"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="tahun" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tahun Terbit</label>
                <input id="tahun" type="number" wire:model="tahun_terbit" min="1900" max="2100"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="isbn" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">ISBN</label>
                <input id="isbn" type="text" wire:model="isbn" maxlength="30"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="jumlah" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Jumlah <span class="text-danger">*</span></label>
                <input id="jumlah" type="number" wire:model="jumlah" min="1" max="10000"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('jumlah') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Buku' }}
            </button>
            <a href="{{ route('perpus.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
