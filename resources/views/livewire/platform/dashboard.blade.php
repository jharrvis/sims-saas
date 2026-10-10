<div>
    <h1 class="text-2xl font-extrabold mb-6">Dashboard Platform</h1>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Total Tenant</p>
            <p class="text-3xl font-extrabold mt-1">{{ $metrics['total_tenant'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">MRR</p>
            <p class="text-3xl font-extrabold mt-1">Rp{{ number_format($metrics['mrr'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Trial berakhir ≤ 7 hari</p>
            <p class="text-3xl font-extrabold mt-1 text-amber-600">{{ $metrics['trial_berakhir_7_hari'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Piutang</p>
            <p class="text-3xl font-extrabold mt-1 text-danger">Rp{{ number_format($metrics['piutang'], 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $metrics['invoice_overdue'] }} tagihan overdue</p>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm mb-6">
        <h2 class="font-bold mb-3">Tenant per Status Langganan</h2>
        <div class="flex flex-wrap gap-2">
            @forelse ($metrics['per_status'] as $status => $jumlah)
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm">
                    <span class="font-semibold capitalize">{{ str_replace('_', ' ', $status) }}</span>
                    <span class="font-extrabold">{{ $jumlah }}</span>
                </span>
            @empty
                <p class="text-sm text-slate-400">Belum ada tenant.</p>
            @endforelse
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="font-bold mb-3">Trial Segera Berakhir</h2>
            @forelse ($trialSoon as $s)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                    <span>{{ $this->schoolName($s->school_id) }}</span>
                    <span class="text-amber-600 font-semibold">{{ $s->trial_berakhir_pada?->format('d M Y') }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Tidak ada.</p>
            @endforelse
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="font-bold mb-3">Tagihan Overdue</h2>
            @forelse ($overdue as $inv)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                    <span>{{ $inv->nama_sekolah_snapshot }} <span class="text-slate-400">· {{ $inv->nomor }}</span></span>
                    <span class="text-danger font-semibold">Rp{{ number_format($inv->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Tidak ada.</p>
            @endforelse
        </div>
    </div>
</div>
