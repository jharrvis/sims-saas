<div class="px-4 pt-4 pb-28">
    <a href="{{ route('pwa.pengumuman') }}" class="text-sm text-slate-500 font-semibold">← Semua pengumuman</a>

    <article class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mt-3">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl {{ $item->penting ? 'bg-danger/10 text-danger' : 'bg-primary/10 text-primary' }} flex items-center justify-center flex-shrink-0">
                <x-icon name="megaphone" class="w-5 h-5" />
            </div>
            <div class="min-w-0">
                @if ($item->penting)
                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-danger/10 text-danger">PENTING</span>
                @endif
                <h2 class="text-lg font-extrabold mt-1 leading-snug text-slate-800">{{ $item->judul }}</h2>
                <p class="text-[11px] text-slate-400 mt-1">{{ $item->tayang_dari?->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>
        <div class="prose prose-sm max-w-none mt-4 text-slate-700">
            {!! nl2br(e($item->isi)) !!}
        </div>
    </article>
</div>
