<div class="max-w-3xl">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="siswa" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Siswa <span class="text-danger">*</span></label>
                <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama…"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                <select wire:model="student_id" aria-label="Pilih siswa"
                        class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    <option value="">— Pilih siswa —</option>
                    @foreach ($siswaList as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                    @endforeach
                </select>
                @error('student_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tgl" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Waktu Kunjungan <span class="text-danger">*</span></label>
                <input id="tgl" type="datetime-local" wire:model="tgl"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div class="sm:col-span-2">
                <label for="keluhan" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Keluhan <span class="text-danger">*</span></label>
                <textarea id="keluhan" wire:model="keluhan" rows="3"
                          class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
                @error('keluhan') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="diagnosa" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Diagnosa</label>
                <input id="diagnosa" type="text" wire:model="diagnosa" maxlength="200"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="tindakan" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tindakan</label>
                <input id="tindakan" type="text" wire:model="tindakan" maxlength="2000"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="suhu" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Suhu Tubuh (°C)</label>
                <input id="suhu" type="number" wire:model="suhu" min="30" max="45" step="0.1"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="td" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tekanan Darah</label>
                <input id="td" type="text" wire:model="tekanan_darah" maxlength="20" placeholder="120/80"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div class="sm:col-span-2">
                <label for="rujukan" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Rujukan</label>
                <select id="rujukan" wire:model="rujukan"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    @foreach (\App\Models\HealthVisit::RUJUKAN as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Catat Kunjungan' }}
            </button>
            <a href="{{ route('uks.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
