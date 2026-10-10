<div class="max-w-3xl mx-auto">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="academic_year_id" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Tahun Ajaran <span class="text-danger">*</span></label>
                <select id="academic_year_id" wire:model="academic_year_id" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    @foreach ($tahunAjaran as $ta)
                        <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                    @endforeach
                </select>
                @error('academic_year_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tingkat" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Tingkat <span class="text-danger">*</span></label>
                <input id="tingkat" type="text" wire:model="tingkat" placeholder="cth. 7, 12, XII" required
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                @error('tingkat') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="nama" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nama Kelas <span class="text-danger">*</span></label>
                <input id="nama" type="text" wire:model="nama" placeholder="cth. VII A" required
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                @error('nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kapasitas" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Kapasitas <span class="text-danger">*</span></label>
                <input id="kapasitas" type="number" wire:model="kapasitas" min="1" max="100" required
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                @error('kapasitas') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="wali_kelas_id" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Wali Kelas</label>
            <select id="wali_kelas_id" wire:model="wali_kelas_id" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                <option value="">— Belum ditentukan —</option>
                @foreach ($gurus as $guru)
                    <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                @endforeach
            </select>
            @error('wali_kelas_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('kelas.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                Simpan
            </button>
        </div>
    </form>
</div>
