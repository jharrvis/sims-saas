<div>
    <div class="grid grid-cols-4 gap-2 mb-4">
        @foreach (['H' => 'bg-success', 'I' => 'bg-warning', 'S' => 'bg-warning', 'A' => 'bg-danger'] as $kode => $color)
            <div class="rounded-xl bg-slate-50 p-3 text-center">
                <p class="text-lg font-extrabold">{{ $rekap[$kode] ?? 0 }}</p>
                <p class="text-[10px] text-slate-500 font-semibold">{{ $labels[$kode] }}</p>
            </div>
        @endforeach
    </div>
    <p class="text-[11px] text-slate-400 mb-3">Rekap bulan {{ now()->locale('id')->monthName }}</p>

    @forelse ($items as $a)
        <div class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 mb-1.5">
            <div>
                <p class="text-sm font-semibold">{{ $a->tanggal->locale('id')->isoFormat('dddd, D MMM Y') }}</p>
                @if ($a->keterangan) <p class="text-[11px] text-slate-400">{{ $a->keterangan }}</p> @endif
            </div>
            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full
                {{ $a->status === 'H' ? 'bg-success/10 text-success' : ($a->status === 'A' ? 'bg-danger/10 text-danger' : 'bg-warning/15 text-amber-600') }}">
                {{ $labels[$a->status] ?? $a->status }}
            </span>
        </div>
    @empty
        <p class="text-sm text-slate-400 italic">Belum ada data absensi.</p>
    @endforelse

    @if (method_exists($items, 'links'))
        <div class="mt-4">{{ $items->links() }}</div>
    @endif
</div>
