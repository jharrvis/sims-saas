<div class="max-w-3xl">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="judul" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Judul <span class="text-danger">*</span></label>
                <input id="judul" type="text" wire:model="judul" maxlength="200"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('judul') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="deskripsi" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi</label>
                <textarea id="deskripsi" wire:model="deskripsi" rows="3"
                          class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
            </div>
            <div>
                <label for="mulai" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Dibuka</label>
                <input id="mulai" type="datetime-local" wire:model="mulai"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="selesai" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Ditutup</label>
                <input id="selesai" type="datetime-local" wire:model="selesai"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('selesai') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Status</label>
                <select id="status" wire:model="status"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    @foreach (\App\Models\Survey::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="target" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Target</label>
                <select id="target" wire:model="target_type"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                    @foreach (\App\Models\Survey::TARGET as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="pt-2">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-800 dark:text-white">Pertanyaan</h3>
                <button type="button" wire:click="tambahPertanyaan" class="text-xs font-semibold text-primary hover:underline">+ Tambah Pertanyaan</button>
            </div>
            @error('questions') <p class="text-xs text-danger mb-2">{{ $message }}</p> @enderror

            @foreach ($questions as $i => $q)
                <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-4 mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-400">Pertanyaan {{ $i + 1 }}</span>
                        <button type="button" wire:click="hapusPertanyaan({{ $i }})" class="text-xs font-semibold text-danger hover:underline">Hapus</button>
                    </div>
                    <textarea wire:model="questions.{{ $i }}.pertanyaan" rows="2" placeholder="Tulis pertanyaan…"
                              class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white mb-2"></textarea>
                    @error("questions.$i.pertanyaan") <p class="text-xs text-danger mb-2">{{ $message }}</p> @enderror
                    <div class="grid sm:grid-cols-3 gap-2">
                        <select wire:model="questions.{{ $i }}.tipe"
                                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm dark:text-white">
                            @foreach (\App\Models\SurveyQuestion::TIPE as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @if (($q['tipe'] ?? '') === 'pilihan_ganda')
                            <textarea wire:model="questions.{{ $i }}.opsi" rows="2" placeholder="Satu opsi per baris…"
                                      class="sm:col-span-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white"></textarea>
                        @else
                            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                <input type="checkbox" wire:model="questions.{{ $i }}.wajib" class="w-4 h-4 accent-primary"> Wajib diisi
                            </label>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $isEdit ? 'Simpan Perubahan' : 'Buat Survei' }}
            </button>
            <a href="{{ route('survei.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
