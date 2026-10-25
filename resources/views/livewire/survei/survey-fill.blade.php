<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Survei</span>
            <h2 class="management-page-title">{{ $survey->judul }}</h2>
            @if ($survey->deskripsi)
                <p>{{ $survey->deskripsi }}</p>
            @endif
        </div>
        <div class="page-actions">
            <a href="{{ route('survei.index') }}" class="back-link">← Survei</a>
        </div>
    </div>

    <form wire:submit="submit" class="space-y-4">
        @foreach ($questions as $q)
            <div wire:key="survei-isi-{{ $q->id }}" class="card">
                <p class="font-semibold text-sm mb-3">
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
                    <textarea wire:model="jawaban.{{ $q->id }}" rows="3" class="field"></textarea>
                @endif

                @error("jawaban.{$q->id}") <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
        @endforeach

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Kirim Jawaban</span>
                <span wire:loading wire:target="submit">Mengirim...</span>
            </button>
            <a href="{{ route('survei.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
