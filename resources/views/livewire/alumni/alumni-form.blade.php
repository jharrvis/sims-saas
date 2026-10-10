<div class="max-w-3xl">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="siswa" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tautkan ke Data Siswa <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama…"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                <select wire:model.live="student_id" aria-label="Pilih siswa"
                        class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    <option value="">— Tanpa tautan —</option>
                    @foreach ($siswaList as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="nama" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama <span class="text-danger">*</span></label>
                <input id="nama" type="text" wire:model="nama" maxlength="150"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tahun" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tahun Lulus</label>
                <input id="tahun" type="text" wire:model="tahun_lulus" maxlength="9" placeholder="2026"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="phone" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nomor WA</label>
                <input id="phone" type="text" wire:model="phone" maxlength="20"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Email</label>
                <input id="email" type="email" wire:model="email" maxlength="150"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('email') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="alamat" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Alamat</label>
                <textarea id="alamat" wire:model="alamat" rows="3"
                          class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Alumni' }}
            </button>
            <a href="{{ route('alumni.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
