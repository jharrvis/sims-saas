<div class="max-w-3xl">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div>
            <label for="siswa" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Siswa <span class="text-danger">*</span></label>
            <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Ketik nama siswa…"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            @if ($searchSiswa !== '' && ! $isEdit)
                <div class="mt-1 rounded-lg border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700 max-h-48 overflow-y-auto">
                    @forelse ($siswaList as $s)
                        <button type="button" wire:click="$set('student_id', {{ $s->id }}); $set('searchSiswa', '{{ addslashes($s->nama) }}')"
                                class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-800 {{ $student_id === $s->id ? 'bg-primary/5 font-semibold text-primary' : 'text-slate-700 dark:text-slate-300' }}">
                            {{ $s->nama }} <span class="text-xs text-slate-400">· {{ $s->nis }}</span>
                        </button>
                    @empty
                        <p class="px-3.5 py-2 text-xs text-slate-500">Tidak ditemukan.</p>
                    @endforelse
                </div>
            @endif
            @error('student_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="jenis" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Jenis Pelanggaran <span class="text-danger">*</span></label>
                <select id="jenis" wire:model="violation_type_id"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    <option value="">— Pilih —</option>
                    @foreach ($types as $t)
                        <option value="{{ $t->id }}">{{ $t->nama }} ({{ $t->poin }} poin · {{ \App\Models\ViolationType::KATEGORI[$t->kategori] }})</option>
                    @endforeach
                </select>
                @error('violation_type_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tanggal" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tanggal <span class="text-danger">*</span></label>
                <input id="tanggal" type="date" wire:model="tanggal" max="{{ now()->toDateString() }}"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('tanggal') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="keterangan" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Keterangan</label>
            <textarea id="keterangan" wire:model="keterangan" rows="3"
                      class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
        </div>

        <div>
            <label for="tindak" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tindak Lanjut</label>
            <input id="tindak" type="text" wire:model="tindak_lanjut" maxlength="200" placeholder="cth. Panggil orang tua"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Catat Pelanggaran' }}
            </button>
            <a href="{{ route('tatib.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
