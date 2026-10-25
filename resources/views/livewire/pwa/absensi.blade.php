<div class="screen app-screen screen-enter">
    @php
        $attendanceTotal = $rekap->sum();
        $presentPercentage = $attendanceTotal > 0 ? (int) round(($rekap['H'] ?? 0) / $attendanceTotal * 100) : 0;
        $metricTones = ['H' => 'green', 'I' => 'amber', 'S' => 'blue', 'A' => 'coral'];
    @endphp

    <div class="page-title">
        <span class="overline">REKAP BULANAN</span>
        <h1>{{ now()->locale('id')->isoFormat('MMMM YYYY') }}</h1>
        <p>{{ $presentPercentage }}% kehadiran dari {{ $attendanceTotal }} catatan absensi.</p>
    </div>

    <section class="attendance-summary" aria-label="Ringkasan absensi bulan ini">
        @foreach (['H', 'I', 'S', 'A'] as $code)
            <article class="attendance-metric {{ $metricTones[$code] }}" wire:key="attendance-summary-{{ $code }}">
                <span class="attendance-metric-icon">
                    @switch($code)
                        @case('H') <x-icon name="check-circle" class="h-5 w-5" /> @break
                        @case('I') <x-icon name="clock" class="h-5 w-5" /> @break
                        @case('S') <x-icon name="circle-help" class="h-5 w-5" /> @break
                        @default <x-icon name="user-x" class="h-5 w-5" />
                    @endswitch
                </span>
                <strong>{{ $rekap[$code] ?? 0 }}</strong>
                <small>{{ $labels[$code] }}</small>
            </article>
        @endforeach
    </section>

    <section>
        <div class="section-head">
            <h3>Detail Absensi</h3>
            <span class="pwa-section-meta">Terbaru dahulu</span>
        </div>
        <div class="surface-card compact-list attendance-list">
            @forelse ($items as $attendance)
                <article class="data-row" wire:key="attendance-{{ $attendance->id }}">
                    <span class="attendance-date">
                        <small>{{ $attendance->tanggal->locale('id')->isoFormat('ddd') }}</small>
                        <strong>{{ $attendance->tanggal->locale('id')->isoFormat('D') }}</strong>
                    </span>
                    <span>
                        <strong>{{ $labels[$attendance->status] ?? $attendance->status }}</strong>
                        <small>{{ $attendance->keterangan ?: $attendance->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}</small>
                    </span>
                    <span class="status-chip {{ $attendance->status === 'H' ? 'success' : ($attendance->status === 'A' ? 'danger' : 'warning') }}">
                        {{ $attendance->status }}
                    </span>
                </article>
            @empty
                <div class="pwa-empty-state pwa-empty-state-plain">
                    <span class="row-icon green"><x-icon name="check-circle" class="h-5 w-5" /></span>
                    <span><strong>Belum ada data absensi</strong><small>Catatan kehadiran dari sekolah akan tampil di sini.</small></span>
                </div>
            @endforelse
        </div>
    </section>

    @if (method_exists($items, 'links'))
        <div class="pwa-pagination">{{ $items->links() }}</div>
    @endif
</div>
