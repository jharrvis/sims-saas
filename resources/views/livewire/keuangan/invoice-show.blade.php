<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('keuangan.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Daftar Tagihan
            </a>
            <span class="eyebrow">Keuangan / Tagihan</span>
            <h2 class="management-page-title">{{ $invoice->student->nama }}</h2>
            <p>
                {{ $invoice->no_invoice }} · {{ $invoice->feeType->nama }} · Periode {{ $invoice->periode_label }}
                <span class="badge badge-blue">{{ $invoice->labelStatus() }}</span>
            </p>
        </div>
    </div>

    @if (session('paid'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('paid') }}</span>
        </div>
    @endif

    <div class="info-grid">
        <div class="info-tile">
            <small>Nominal</small>
            <strong>Rp {{ number_format($invoice->nominal, 0, ',', '.') }}</strong>
        </div>
        <div class="info-tile">
            <small>Dibayar</small>
            <strong class="text-success">Rp {{ number_format($invoice->dibayar, 0, ',', '.') }}</strong>
        </div>
        <div class="info-tile">
            <small>Sisa</small>
            <strong class="text-danger">Rp {{ number_format($invoice->sisa(), 0, ',', '.') }}</strong>
        </div>
    </div>

    @if ($invoice->sisa() > 0)
        @can('keuangan.bayar.create')
            <section class="card management-form-card" aria-labelledby="bayar-title">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon green"><x-icon name="wallet" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3 id="bayar-title">Catat Pembayaran</h3>
                            <p>Pembayaran dicatat ke riwayat di bawah setelah disimpan.</p>
                        </div>
                    </div>
                </div>

                <form wire:submit="pay" class="form-grid management-form-grid">
                    <div class="form-group">
                        <label for="nominal">Nominal (Rp) <span class="required">*</span></label>
                        <input id="nominal" type="number" wire:model="nominal" min="1" max="{{ $invoice->sisa() }}" required class="field">
                        @error('nominal') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="tgl_bayar">Tanggal Bayar <span class="required">*</span></label>
                        <input id="tgl_bayar" type="date" wire:model="tgl_bayar" required class="field">
                        @error('tgl_bayar') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="metode">Metode <span class="required">*</span></label>
                        <select id="metode" wire:model="metode" class="select-field">
                            @foreach (\App\Models\Payment::METODES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('metode') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="catatan">Catatan</label>
                        <input id="catatan" type="text" wire:model="catatan" class="field">
                        @error('catatan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-actions management-field-span">
                        <button type="submit" wire:loading.attr="disabled" class="btn btn-success" wire:target="pay">
                            <span wire:loading.remove wire:target="pay">Simpan Pembayaran</span>
                            <span wire:loading wire:target="pay">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </section>
        @endcan
    @endif

    <section class="card" aria-labelledby="riwayat-bayar-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="receipt" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="riwayat-bayar-title">Riwayat Pembayaran</h3>
                    <p>Kuitansi yang sudah diterbitkan untuk tagihan ini.</p>
                </div>
            </div>
        </div>

        @if ($invoice->payments->isEmpty())
            <div class="empty-state">
                <p>Belum ada pembayaran.</p>
            </div>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach ($invoice->payments as $p)
                    <li wire:key="payment-{{ $p->id }}" class="py-3 flex items-center justify-between">
                        <div>
                            <p class="font-mono text-xs text-slate-500">{{ $p->no_kuitansi }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $p->tgl_bayar->format('d M Y') }} · {{ \App\Models\Payment::METODES[$p->metode] }}</p>
                            @if ($p->catatan)<p class="text-xs text-slate-500">{{ $p->catatan }}</p>@endif
                        </div>
                        <p class="font-bold text-sm text-success">Rp {{ number_format($p->nominal, 0, ',', '.') }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
