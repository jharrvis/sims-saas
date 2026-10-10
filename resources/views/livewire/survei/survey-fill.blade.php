<div class="max-w-3xl">
    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $survey->judul }}</h2>
        @if ($survey->deskripsi)
            <p class="text-sm text-slate-500 mt-1">{{ $survey->deskripsi }}</p>
        @endif
    </div>

    <form wire:submit="submit" class="space-y-4">
        @foreach ($questions as $q)
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5">
                <p class="font-semibold text-slate-800 dark:text-white text-sm mb-3">
                    {{ $q->pertanyaan }} @if ($q->wajib) <span class="text-danger">*</span> @endif
                </p>

                @if ($q->tipe === 'pilihan_ganda')
                    @foreach ((array) ($q->opsi ?? []) as $opsi)
                        <label class="flex items-center gap-2 py-1.5 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                            <input type="radio" wire:model="jawaban.{{ $q->id }}" value="{{ $opsi }}" class="w-4 h-4 accent-primary">
                            {{ $opsi }}
                        </label>
                    @endforeach
                @elseif ($q->tipe === 'skala')
                    <div class="flex items-center gap-2">
                        @for ($n = 1; $n <= 5; $n++)
                            <label class="flex-1 text-center cursor-pointer">
                                <input type="radio" wire:model="jawaban.{{ $q->id }}" value="{{ $n }}" class="peer sr-only">
                                <span class="block rounded-lg border border-slate-200 dark:border-slate-700 py-2 text-sm font-bold peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary">{{ $n }}</span>
                            </label>
                        @endfor
                    </div>
                    <div class="flex justify-between text-xs text-slate-400 mt-1">
                        <span>Sangat tidak setuju</span><span>Sangat setuju</span>
                    </div>
                @else
                    <textarea wire:model="jawaban.{{ $q->id }}" rows="3"
                              class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
                @endif

                @error("jawaban.{$q->id}") <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        @endforeach

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">Kirim Jawaban</button>
            <a href="{{ route('survei.index') }}" class="text-sm text-slate-500 hover:text-primary">Batal</a>
        </div>
    </form>
</div>
