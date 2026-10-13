<div class="px-4 pt-4 pb-28">
    <div class="space-y-3">
        @forelse ($items as $p)
            <a href="{{ route('pwa.pengumuman.show', $p) }}" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl {{ $p->penting ? 'bg-danger/10 text-danger' : 'bg-primary/10 text-primary' }} flex items-center justify-center flex-shrink-0">
                    <x-icon name="megaphone" class="w-5 h-5" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-slate-800 truncate">{{ $p->judul }}</span>
                        <span class="text-[10px] text-slate-400 shrink-0">{{ $p->tayang_dari?->locale('id')->isoFormat('D MMM Y') }}</span>
                    </div>
                    @if ($p->penting)
                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-danger/10 text-danger mt-1">PENTING</span>
                    @endif
                    <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ strip_tags($p->isi) }}</p>
                </div>
            </a>
        @empty
            <div class="text-center py-12">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto">
                    <x-icon name="megaphone" class="w-8 h-8 text-slate-300" />
                </div>
                <p class="text-sm text-slate-400 mt-3">Belum ada pengumuman.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
