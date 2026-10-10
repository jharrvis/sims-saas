<div>
    @forelse ($items as $p)
        <a href="{{ route('pwa.pengumuman.show', $p) }}" class="block rounded-2xl border border-slate-100 p-4 mb-2">
            @if ($p->penting)
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-danger/10 text-danger">PENTING</span>
            @endif
            <p class="font-bold text-sm mt-1 leading-snug">{{ $p->judul }}</p>
            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ strip_tags($p->isi) }}</p>
            <p class="text-[11px] text-slate-400 mt-1.5">{{ $p->tayang_dari?->locale('id')->isoFormat('D MMM Y') }}</p>
        </a>
    @empty
        <div class="text-center py-12">
            <i data-lucide="megaphone" class="w-10 h-10 mx-auto text-slate-200"></i>
            <p class="text-sm text-slate-400 mt-2">Belum ada pengumuman.</p>
        </div>
    @endforelse

    <div class="mt-4">{{ $items->links() }}</div>
</div>
