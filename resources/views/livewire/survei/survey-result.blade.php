<div class="max-w-4xl">
    <div class="mb-5">
        <a href="{{ route('survei.index') }}" class="text-sm text-slate-500 hover:text-primary">← Semua Survei</a>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $survey->judul }}</h2>
        <p class="text-sm text-slate-500 mt-1">Total responden: <span class="font-bold">{{ $totalResponden }}</span></p>
    </div>

    @forelse ($questions as $q)
        @php $agg = $agregat[$q->id] ?? []; @endphp
        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-4">
            <p class="font-semibold text-slate-800 dark:text-white text-sm mb-1">{{ $q->pertanyaan }}</p>
            <p class="text-xs text-slate-400 mb-3">{{ \App\Models\SurveyQuestion::TIPE[$q->tipe] }} · {{ $agg['responden'] ?? 0 }} jawaban</p>

            @if ($q->tipe === 'skala' && isset($agg['rata_rata']))
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full bg-primary rounded-full" style="width: {{ min(100, $agg['rata_rata'] / 5 * 100) }}%"></div>
                    </div>
                    <span class="font-bold text-lg text-primary">{{ number_format($agg['rata_rata'], 2) }}</span>
                    <span class="text-xs text-slate-400">/ 5</span>
                </div>
            @elseif ($q->tipe === 'pilihan_ganda' && ! empty($agg['distribusi']))
                @php $max = max($agg['distribusi']); @endphp
                @foreach ($agg['distribusi'] as $opsi => $jumlah)
                    <div class="mb-2">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-700 dark:text-slate-200">{{ $opsi }}</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ $jumlah }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full bg-primary rounded-full" style="width: {{ $max > 0 ? $jumlah / $max * 100 : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-sm text-slate-500 italic">Hasil isian teks tidak diagregat.</p>
            @endif
        </div>
    @empty
        <p class="text-sm text-slate-500">Survei belum memiliki pertanyaan.</p>
    @endforelse
</div>
