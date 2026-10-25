<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / LMS</span>
            <h2 class="management-page-title">{{ $assignment->judul }}</h2>
            <p>{{ $assignment->subject->nama }} · {{ $assignment->rombel->nama }} · Deadline {{ $assignment->deadline->format('d M Y H:i') }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('lms.index') }}" class="back-link">← Semua Tugas</a>
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    @if ($assignment->deskripsi)
        <div class="card">
            <div class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $assignment->deskripsi }}</div>
        </div>
    @endif

    @if ($bolehKumpul)
        <section class="card management-form-card" aria-labelledby="tugas-kumpul-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="send" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="tugas-kumpul-title">Kumpulkan Jawaban</h3>
                        <p>Jawaban tersimpan setelah tombol Kumpulkan ditekan.</p>
                    </div>
                </div>
            </div>

            <form wire:submit="kumpul" class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="siswa">Siswa <span class="required">*</span></label>
                    <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama…" class="field">
                    <select wire:model="student_id" aria-label="Pilih siswa" class="select-field mt-1">
                        <option value="">— Pilih siswa —</option>
                        @foreach ($siswaList as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group management-field-span">
                    <label for="jawaban">Jawaban <span class="required">*</span></label>
                    <textarea id="jawaban" wire:model="jawaban" rows="6" class="field"></textarea>
                    @error('jawaban') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-actions management-field-span">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="kumpul">
                        <span wire:loading.remove wire:target="kumpul">Kumpulkan</span>
                        <span wire:loading wire:target="kumpul">Mengumpulkan...</span>
                    </button>
                </div>
            </form>
        </section>
    @endif

    <div class="card">
        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 font-semibold text-sm">
            Pengumpulan ({{ $submissions->count() }})
        </div>
        @forelse ($submissions as $s)
            <div wire:key="kumpul-{{ $s->id }}" class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-semibold text-sm">{{ $s->student->nama }}</span>
                        <span class="text-xs text-slate-400">· {{ $s->tgl_kumpul->format('d M Y H:i') }}</span>
                        @if ($s->isTerlambat())
                            <span class="badge badge-orange ml-1">Terlambat</span>
                        @endif
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line">{{ $s->jawaban }}</p>
                    </div>
                    @if ($s->nilai !== null)
                        <span class="badge badge-blue shrink-0">{{ rtrim(rtrim((string) $s->nilai, '0'), '.') }}</span>
                    @endif
                </div>
                @if ($bolehNilai)
                    <div class="grid sm:grid-cols-3 gap-3 mt-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Nilai (0–100)</label>
                            <input type="number" wire:model="nilai.{{ $s->id }}" min="0" max="100" step="0.5" class="field">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Feedback</label>
                            <input type="text" wire:model="feedback.{{ $s->id }}" class="field">
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
