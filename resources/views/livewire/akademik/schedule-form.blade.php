<div class="max-w-3xl mx-auto">
    @if ($conflicts !== [])
        <div class="mb-5 rounded-xl border border-danger/30 bg-red-50 dark:bg-red-900/20 p-4" role="alert">
            <p class="font-semibold text-danger text-sm mb-2">Jadwal bentrok:</p>
            <ul class="list-disc list-inside text-sm text-danger space-y-1">
                @foreach ($conflicts as $c)
                    <li>{{ $c }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="rombel_id" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Kelas <span class="text-danger">*</span></label>
                <select id="rombel_id" wire:model="rombel_id" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    @foreach ($rombels as $r)
                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="hari" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Hari <span class="text-danger">*</span></label>
                <select id="hari" wire:model="hari" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    @foreach (\App\Models\Schedule::HARIS as $h)
                        <option value="{{ $h }}">{{ ucfirst($h) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="jam_ke" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Jam ke <span class="text-danger">*</span></label>
                <input id="jam_ke" type="number" wire:model="jam_ke" min="1" max="15" required
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="ruang" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Ruang</label>
                <input id="ruang" type="text" wire:model="ruang" placeholder="cth. R-101"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="subject_id" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Mata Pelajaran <span class="text-danger">*</span></label>
                <select id="subject_id" wire:model="subject_id" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    <option value="">— Pilih mapel —</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                    @endforeach
                </select>
                @error('subject_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="employee_id" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Guru <span class="text-danger">*</span></label>
                <select id="employee_id" wire:model="employee_id" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    <option value="">— Pilih guru —</option>
                    @foreach ($gurus as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                    @endforeach
                </select>
                @error('employee_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('jadwal.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                Simpan
            </button>
        </div>
    </form>
</div>
