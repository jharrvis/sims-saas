<div class="px-4 pt-4 pb-28">
    @php
        $rekapWarna = [
            'H' => ['bg-success/10', 'text-success'],
            'I' => ['bg-warning/10', 'text-warning'],
            'S' => ['bg-primary/10', 'text-primary'],
            'A' => ['bg-danger/10', 'text-danger'],
        ];
        $statusIkon = [
            'H' => ['check', 'bg-success/15', 'text-success'],
            'I' => ['info', 'bg-warning/15', 'text-warning'],
            'S' => ['info', 'bg-primary/15', 'text-primary'],
            'A' => ['x', 'bg-danger/15', 'text-danger'],
        ];
    @endphp

    {{-- Ringkasan (design siswa/absensi.html) --}}
    <div class="grid grid-cols-4 gap-2 mb-4">
        @foreach (['H', 'I', 'S', 'A'] as $kode)
            @php $w = $rekapWarna[$kode]; @endphp
            <div class="{{ $w[0] }} rounded-xl p-3 text-center">
                <p class="text-xl font-extrabold {{ $w[1] }}">{{ $rekap[$kode] ?? 0 }}</p>
                <p class="text-[10px] {{ $w[1] }} font-medium">{{ $labels[$kode] }}</p>
            </div>
        @endforeach
    </div>
    <p class="text-[11px] text-slate-400 mb-3">Rekap bulan {{ now()->locale('id')->monthName }}</p>

    <h3 class="font-bold text-slate-800 text-sm mb-3">Detail Absensi</h3>
    <div class="space-y-2">
        @forelse ($items as $a)
            @php $ikon = $statusIkon[$a->status] ?? $statusIkon['H']; @endphp
            <div class="bg-white rounded-xl p-3.5 shadow-sm border border-slate-100 flex items-center gap-3">
                <div class="text-center min-w-[36px]">
                    <p class="text-xs text-slate-400">{{ $a->tanggal->locale('id')->isoFormat('ddd') }}</p>
                    <p class="text-lg font-extrabold text-slate-800">{{ $a->tanggal->locale('id')->isoFormat('D') }}</p>
                </div>
                <div class="w-px h-8 bg-slate-100"></div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold {{ $a->status === 'H' ? 'text-slate-700' : ($a->status === 'A' ? 'text-danger' : 'text-primary') }}">{{ $labels[$a->status] ?? $a->status }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $a->keterangan ?: $a->tanggal->locale('id')->isoFormat('dddd, D MMM Y') }}</p>
                </div>
                <span class="w-7 h-7 rounded-full {{ $ikon[1] }} flex items-center justify-center shrink-0">
                    <x-icon :name="$ikon[0]" class="w-4 h-4 {{ $ikon[2] }}" />
                </span>
            </div>
        @empty
            <p class="text-sm text-slate-400 italic">Belum ada data absensi.</p>
        @endforelse
    </div>

    @if (method_exists($items, 'links'))
        <div class="mt-4">{{ $items->links() }}</div>
    @endif
</div>
