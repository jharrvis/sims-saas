<div>
    <a href="{{ route('pwa.pengumuman') }}" class="text-sm text-slate-500 font-semibold">← Semua pengumuman</a>

    <article class="mt-3">
        @if ($item->penting)
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-danger/10 text-danger">PENTING</span>
        @endif
        <h2 class="text-lg font-extrabold mt-1 leading-snug">{{ $item->judul }}</h2>
        <p class="text-[11px] text-slate-400 mt-1">{{ $item->tayang_dari?->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
        <div class="prose prose-sm max-w-none mt-4 text-slate-700">
            {!! nl2br(e($item->isi)) !!}
        </div>
    </article>
</div>
