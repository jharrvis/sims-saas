<div class="px-4 pt-4 pb-28">
    @php
        $hariDenganJadwal = collect($haris)->filter(fn ($h) => $items->has($h))->values();
        $hariAktif = $hariDenganJadwal->contains(strtolower(now()->locale('id')->dayName))
            ? strtolower(now()->locale('id')->dayName)
            : $hariDenganJadwal->first();
        $jadwalWarna = [
            ['bg-blue-50', 'text-blue-700', 'text-blue-500'],
            ['bg-purple-50', 'text-purple-700', 'text-purple-500'],
            ['bg-emerald-50', 'text-emerald-700', 'text-emerald-500'],
        ];
    @endphp

    @if ($rombel)
        <p class="text-xs text-slate-400 mb-3">{{ $rombel->nama }}</p>
    @endif

    @if ($hariDenganJadwal->isNotEmpty())
        {{-- Pemilih hari (design siswa/jadwal.html) --}}
        <div class="flex gap-2 overflow-x-auto no-scrollbar pb-2 mb-4">
            @foreach ($hariDenganJadwal as $hari)
                <button type="button" data-jadwal-pill="{{ $hari }}" onclick="pwaJadwalHari('{{ $hari }}')"
                        class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition-all {{ $hari === $hariAktif ? 'bg-[#3c50e0] text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200' }}">
                    {{ ucfirst($hari) }}
                </button>
            @endforeach
        </div>

        @foreach ($hariDenganJadwal as $hari)
            <div data-jadwal-hari="{{ $hari }}" @if ($hari !== $hariAktif) class="hidden" @endif>
                @foreach ($items[$hari] as $j)
                    @php $w = $jadwalWarna[$loop->index % 3]; @endphp
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex gap-4 items-center mb-3">
                        <div class="text-center min-w-[52px]">
                            <p class="text-[10px] text-slate-400 font-medium">Jam ke</p>
                            <p class="text-lg font-extrabold text-slate-800">{{ $j->jam_ke }}</p>
                        </div>
                        <div class="flex-1 {{ $w[0] }} rounded-xl p-3 min-w-0">
                            <p class="font-bold {{ $w[1] }} text-sm truncate">{{ $j->subject?->nama ?? '—' }}</p>
                            <p class="text-xs {{ $w[2] }} mt-0.5 truncate">{{ $j->employee?->nama }}{{ $j->ruang ? ' · '.$j->ruang : '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @else
        <p class="text-sm text-slate-400 italic">Belum ada jadwal.</p>
    @endif
</div>

<script>
function pwaJadwalHari(hari) {
    document.querySelectorAll('[data-jadwal-hari]').forEach((el) => {
        el.classList.toggle('hidden', el.getAttribute('data-jadwal-hari') !== hari);
    });
    document.querySelectorAll('[data-jadwal-pill]').forEach((btn) => {
        const aktif = btn.getAttribute('data-jadwal-pill') === hari;
        btn.className = 'px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition-all ' + (aktif ? 'bg-[#3c50e0] text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200');
    });
}
</script>
