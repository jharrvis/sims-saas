<div class="screen app-screen screen-enter">
    <section class="finance-hero" aria-labelledby="finance-summary-title">
        <div class="finance-head">
            <span id="finance-summary-title">Ringkasan keuangan</span>
            <span class="secure-label"><x-icon name="shield-check" class="h-4 w-4" /> Data sekolah</span>
        </div>
        <strong>Rp{{ number_format($totalAktif, 0, ',', '.') }}</strong>
        <p>{{ $jumlahAktif > 0 ? $jumlahAktif.' tagihan masih aktif' : 'Tidak ada tagihan aktif saat ini' }}</p>
        <div class="finance-hero-status">
            <x-icon :name="$jumlahAktif > 0 ? 'clock' : 'circle-check-big'" class="h-4 w-4" />
            <span>
                @if ($jatuhTempoTerdekat)
                    Jatuh tempo terdekat {{ $jatuhTempoTerdekat->locale('id')->isoFormat('D MMMM Y') }}
                @else
                    Status pembayaran Anda sudah tertib
                @endif
            </span>
        </div>
    </section>

    <div class="finance-stats">
        <div>
            <span class="mini-icon green"><x-icon name="check-circle" class="h-5 w-5" /></span>
            <span><strong>Rp{{ number_format($totalDibayar, 0, ',', '.') }}</strong><small>Total sudah dibayar</small></span>
        </div>
        <div>
            <span class="mini-icon {{ $jumlahAktif > 0 ? 'amber' : 'green' }}"><x-icon name="receipt" class="h-5 w-5" /></span>
            <span><strong>{{ $jumlahAktif }} tagihan</strong><small>Perlu ditindaklanjuti</small></span>
        </div>
    </div>

    <section>
        <div class="section-head">
            <h3>Riwayat Tagihan</h3>
            <span class="pwa-section-meta">{{ $items->count() }} transaksi</span>
        </div>

        <div class="bill-stack">
            @forelse ($items as $invoice)
                @php
                    $isPaid = $invoice->status === 'paid';
                    $isOverdue = $invoice->status === 'overdue';
                @endphp
                <article class="bill-card" wire:key="invoice-{{ $invoice->id }}">
                    <span class="bill-icon {{ $isPaid ? 'green' : ($isOverdue ? 'coral' : 'amber') }}">
                        <x-icon :name="$isPaid ? 'check-circle' : 'receipt'" class="h-5 w-5" />
                    </span>
                    <span class="bill-main">
                        <span class="status-chip {{ $isPaid ? 'success' : ($isOverdue ? 'danger' : 'warning') }}">{{ strtoupper($invoice->labelStatus()) }}</span>
                        <h3>{{ $invoice->feeType?->nama ?? 'Tagihan sekolah' }}</h3>
                        <p>{{ $invoice->periode_label }}{{ $invoice->jatuh_tempo ? ' · '.$invoice->jatuh_tempo->locale('id')->isoFormat('D MMM Y') : '' }}</p>
                        <strong>Rp{{ number_format($invoice->sisa(), 0, ',', '.') }}</strong>
                    </span>
                    <span class="bill-amount">
                        <small>Total</small>
                        <strong>Rp{{ number_format((float) $invoice->nominal, 0, ',', '.') }}</strong>
                    </span>
                </article>
            @empty
                <div class="surface-card pwa-empty-state pwa-empty-state-centered">
                    <span class="row-icon blue"><x-icon name="wallet" class="h-5 w-5" /></span>
                    <span><strong>Belum ada tagihan</strong><small>Tagihan yang diterbitkan sekolah akan tampil di sini.</small></span>
                </div>
            @endforelse
        </div>
    </section>

    <p class="security-note">
        <x-icon name="shield-check" class="h-4 w-4" />
        <span>Nominal dan status berasal langsung dari pencatatan sekolah. Hubungi bendahara bila ada ketidaksesuaian.</span>
    </p>
</div>
