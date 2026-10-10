<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5">
        <a href="{{ route('lms.index') }}" class="text-sm text-slate-500 hover:text-primary">← Semua Tugas</a>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $assignment->judul }}</h2>
        <p class="text-sm text-slate-500 mt-1">{{ $assignment->subject->nama }} · {{ $assignment->rombel->nama }} · Deadline {{ $assignment->deadline->format('d M Y H:i') }}</p>
        @if ($assignment->deskripsi)
            <div class="mt-3 text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $assignment->deskripsi }}</div>
        @endif
    </div>

    @if ($bolehKumpul)
        <form wire:submit="kumpul" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
            <h3 class="font-semibold text-slate-800 dark:text-white mb-4">Kumpulkan Jawaban</h3>
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
                <div class="sm:col-span-2">
                    <label for="jawaban" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Jawaban <span class="text-danger">*</span></label>
                    <textarea id="jawaban" wire:model="jawaban" rows="6"
                              class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
                    @error('jawaban') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <button type="submit" class="mt-4 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">Kumpulkan</button>
        </form>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 font-semibold text-slate-800 dark:text-white text-sm">
            Pengumpulan ({{ $submissions->count() }})
        </div>
        @forelse ($submissions as $s)
            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-semibold text-sm text-slate-800 dark:text-white">{{ $s->student->nama }}</span>
                        <span class="text-xs text-slate-400">· {{ $s->tgl_kumpul->format('d M Y H:i') }}</span>
                        @if ($s->isTerlambat())
                            <span class="ml-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-warning/15 text-amber-600">Terlambat</span>
                        @endif
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line">{{ $s->jawaban }}</p>
                    </div>
                    @if ($s->nilai !== null)
                        <span class="shrink-0 text-sm font-bold px-3 py-1 rounded-lg bg-primary/10 text-primary">{{ rtrim(rtrim((string) $s->nilai, '0'), '.') }}</span>
                    @endif
                </div>
                @if ($bolehNilai)
                    <div class="grid sm:grid-cols-3 gap-3 mt-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Nilai (0–100)</label>
                            <input type="number" wire:model="nilai.{{ $s->id }}" min="0" max="100" step="0.5"
                                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Feedback</label>
                            <input type="text" wire:model="feedback.{{ $s->id }}"
                                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                        </div>
                    </div>
                    <button wire:click="nilai({{ $s->id }})" class="mt-2 text-xs font-semibold text-primary hover:underline">Simpan Nilai</button>
                @elseif ($s->feedback)
                    <p class="mt-2 text-xs text-slate-500 italic">Feedback: {{ $s->feedback }}</p>
                @endif
            </div>
        @empty
            <div class="px-4 py-8 text-center text-sm text-slate-500">Belum ada pengumpulan.</div>
        @endforelse
    </div>
</div>
