<div class="max-w-3xl">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="judul" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Judul <span class="text-danger">*</span></label>
                <input id="judul" type="text" wire:model="judul" maxlength="200"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('judul') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="mapel" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Mata Pelajaran <span class="text-danger">*</span></label>
                <select id="mapel" wire:model="subject_id"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    <option value="">— Pilih mapel —</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                    @endforeach
                </select>
                @error('subject_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="rombel" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kelas <span class="text-danger">*</span></label>
                <select id="rombel" wire:model="rombel_id"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    <option value="">— Pilih kelas —</option>
                    @foreach ($rombels as $r)
                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                    @endforeach
                </select>
                @error('rombel_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="deadline" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Deadline <span class="text-danger">*</span></label>
                <input id="deadline" type="datetime-local" wire:model="deadline"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('deadline') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="deskripsi" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi / Instruksi</label>
                <textarea id="deskripsi" wire:model="deskripsi" rows="5"
                          class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Buat Tugas' }}
            </button>
            <a href="{{ route('lms.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
