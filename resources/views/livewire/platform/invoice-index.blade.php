<div class="page-stack">
    @if (session('ok'))
        <div class="platform-alert platform-alert-success" role="status" aria-live="polite"><x-icon name="check-circle" class="h-5 w-5" /><span>{{ session('ok') }}</span></div>
    @endif

    <div class="page-header">
        <div><span class="eyebrow">Platform / Penagihan</span><h2 class="platform-page-title">Tagihan Langganan</h2><p>Terbitkan dan pantau tagihan layanan SaaS untuk seluruh tenant.</p></div>
    </div>

    @if (auth()->user()?->hasPermissionTo('platform.plan.manage', 'platform'))
        <section class="card platform-billing-form" aria-labelledby="manual-invoice-title">
            <div class="card-head"><div class="card-title"><span class="mini-icon blue"><x-icon name="receipt" class="h-[19px] w-[19px]" /></span><div><h3 id="manual-invoice-title">Terbitkan Tagihan Manual</h3><p>Pilih langganan aktif dan diskon bila diperlukan</p></div></div></div>
            <form wire:submit="issue" class="platform-compact-form">
                <div class="form-group platform-form-grow">
                    <label for="invoice-subscription">Langganan <span class="required">*</span></label>
                    <select id="invoice-subscription" wire:model="subscription_id" class="select-field"><option value="">Pilih langganan</option>@foreach ($subs as $subscription)<option value="{{ $subscription->id }}">{{ $names[$subscription->school_id] ?? 'Sekolah tidak ditemukan' }} · {{ $subscription->plan?->nama ?? 'Tanpa paket' }} ({{ ucfirst($subscription->periode) }})</option>@endforeach</select>
                    @error('subscription_id') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                </div>
                <div class="form-group platform-discount-field">
                    <label for="invoice-discount">Diskon (Rp)</label>
                    <input id="invoice-discount" type="number" wire:model="diskon" min="0" class="field" inputmode="numeric">
                    @error('diskon') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                </div>
                <button type="submit" data-sims-confirm="Terbitkan tagihan untuk langganan ini?" wire:loading.attr="disabled" wire:target="issue" class="btn btn-primary">
                    <x-icon name="send" class="h-4 w-4" /><span wire:loading.remove wire:target="issue">Terbitkan</span><span wire:loading wire:target="issue">Memproses...</span>
                </button>
            </form>
            <p class="helper-text platform-form-note"><x-icon name="circle-help" class="h-4 w-4" /> Nomor dan periode tagihan dibuat otomatis oleh sistem.</p>
        </section>
    @endif

    <section class="card platform-table-card" aria-labelledby="invoice-list-title">
        <div class="card-head platform-table-heading"><div class="card-title"><span class="mini-icon orange"><x-icon name="wallet" class="h-[19px] w-[19px]" /></span><div><h3 id="invoice-list-title">Riwayat Tagihan</h3><p>{{ number_format($invoices->total(), 0, ',', '.') }} tagihan ditemukan</p></div></div></div>
        <div class="table-toolbar platform-tenant-toolbar">
            <span class="platform-toolbar-note"><x-icon name="shield-check" class="h-4 w-4" /> Data lintas tenant, hanya untuk operator platform</span>
            <label class="platform-filter-field" for="invoice-status"><span class="sr-only">Filter status tagihan</span><select id="invoice-status" wire:model.live="status" class="select-field"><option value="">Semua status</option>@foreach (['draft', 'issued', 'paid', 'overdue', 'void'] as $statusOption)<option value="{{ $statusOption }}">{{ match ($statusOption) { 'draft' => 'Draf', 'issued' => 'Terbit', 'paid' => 'Lunas', 'overdue' => 'Lewat tempo', default => 'Batal' } }}</option>@endforeach</select></label>
        </div>
        <div class="table-scroll" wire:loading.class="platform-content-loading" wire:target="status,markPaid">
            <table class="data-table platform-invoice-table">
                <thead><tr><th scope="col">Nomor</th><th scope="col">Sekolah</th><th scope="col">Total</th><th scope="col">Jatuh Tempo</th><th scope="col">Status</th><th scope="col" class="platform-table-actions-heading">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        @php
                            $statusClass = match ($invoice->status) { 'paid' => 'badge-green', 'issued' => 'badge-blue', 'overdue' => 'badge-red', 'draft' => 'badge-orange', default => 'badge-gray' };
                            $statusLabel = match ($invoice->status) { 'paid' => 'Lunas', 'issued' => 'Terbit', 'overdue' => 'Lewat tempo', 'draft' => 'Draf', 'void' => 'Batal', default => ucfirst($invoice->status) };
                        @endphp
                        <tr wire:key="platform-invoice-{{ $invoice->id }}">
                            <td><strong class="platform-reference">{{ $invoice->nomor }}</strong></td>
                            <td><div class="person-cell"><span class="table-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($invoice->nama_sekolah_snapshot, 0, 2)) }}</span><span><strong>{{ $invoice->nama_sekolah_snapshot }}</strong><small>{{ $invoice->npsn_snapshot ? 'NPSN '.$invoice->npsn_snapshot : 'ID Tenant #'.$invoice->school_id }}</small></span></div></td>
                            <td><strong class="platform-tabular">Rp{{ number_format($invoice->total, 0, ',', '.') }}</strong></td>
                            <td class="platform-tabular">{{ $invoice->jatuh_tempo?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td>
                                @if (in_array($invoice->status, ['issued', 'overdue'], true) && auth()->user()?->hasPermissionTo('platform.plan.manage', 'platform'))
                                    <button type="button" wire:click="markPaid({{ $invoice->id }})" data-sims-confirm="Tandai tagihan {{ $invoice->nomor }} sebagai lunas?" wire:loading.attr="disabled" wire:target="markPaid({{ $invoice->id }})" class="btn btn-success btn-sm"><x-icon name="check" class="h-4 w-4" /> Tandai Lunas</button>
                                @else
                                    <span class="platform-muted-value">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-symbol"><x-icon name="receipt" class="h-7 w-7" /></div><h3>Belum ada tagihan</h3><p>Tagihan yang diterbitkan akan tampil di sini.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())<div class="platform-pagination">{{ $invoices->links() }}</div>@endif
    </section>
</div>
