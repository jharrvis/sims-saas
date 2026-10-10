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
                <label for="nama" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama <span class="text-danger">*</span></label>
                <input id="nama" type="text" wire:model="nama" maxlength="200"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kategori" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kategori</label>
                <select id="kategori" wire:model="asset_category_id"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    <option value="">— Baru (isi di bawah) —</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="kondisi" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kondisi</label>
                <select id="kondisi" wire:model="kondisi"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    @foreach (\App\Models\Asset::KONDISI as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 rounded-lg bg-slate-50 dark:bg-slate-800 p-3">
                <p class="text-xs font-semibold text-slate-500 mb-2">Atau buat kategori baru:</p>
                <div class="grid sm:grid-cols-2 gap-3">
                    <input type="text" wire:model="new_kategori_kode" maxlength="30" placeholder="Kode kategori (mis. ELK)"
                           class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    <input type="text" wire:model="new_kategori_nama" maxlength="150" placeholder="Nama kategori (mis. Elektronik)"
                           class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
            </div>
            <div>
                <label for="lokasi" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Lokasi</label>
                <input id="lokasi" type="text" wire:model="lokasi" maxlength="150" placeholder="Ruang Guru, Lab Komputer…"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="sumber" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Sumber Dana</label>
                <input id="sumber" type="text" wire:model="sumber_dana" maxlength="100" placeholder="BOS, Hibah…"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="tglbeli" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tanggal Beli</label>
                <input id="tglbeli" type="date" wire:model="tgl_beli"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="harga" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Harga Beli (Rp)</label>
                <input id="harga" type="number" wire:model="harga_beli" min="0"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Aset' }}
            </button>
            <a href="{{ route('aset.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
