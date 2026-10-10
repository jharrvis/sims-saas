<div>
    @if ($rombel)
        <p class="text-xs text-slate-400 mb-3">{{ $rombel->nama }}</p>
    @endif

    @forelse ($haris as $hari)
        @if ($items->has($hari))
            <h3 class="font-bold text-sm mt-4 mb-2 capitalize">{{ $hari }}</h3>
            @foreach ($items[$hari] as $j)
                <div class="flex items-center gap-3 rounded-xl border border-slate-100 px-3 py-2.5 mb-1.5">
                    <span class="text-[10px] font-bold text-primary bg-primary/10 rounded-lg px-2 py-1 whitespace-nowrap">Jam {{ $j->jam_ke }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate">{{ $j->subject?->nama ?? '—' }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ $j->employee?->nama }}{{ $j->ruang ? ' · '.$j->ruang : '' }}</p>
                    </div>
                </div>
            @endforeach
        @endif
    @empty
        <p class="text-sm text-slate-400 italic">Belum ada jadwal.</p>
    @endforelse
</div>
