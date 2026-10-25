<div class="page-stack">
    <div class="page-header">
        <div><span class="eyebrow">Platform / Komersial</span><h2 class="platform-page-title">Langganan</h2><p>Pantau paket, periode layanan, dan kesehatan langganan seluruh tenant.</p></div>
    </div>

    <section class="card platform-table-card" aria-labelledby="subscription-list-title">
        <div class="card-head platform-table-heading"><div class="card-title"><span class="mini-icon green"><x-icon name="repeat" class="h-[19px] w-[19px]" /></span><div><h3 id="subscription-list-title">Daftar Langganan</h3><p>{{ number_format($subs->total(), 0, ',', '.') }} langganan ditemukan</p></div></div></div>
        <div class="table-toolbar platform-tenant-toolbar">
            <label class="field-search platform-search-field" for="subscription-search"><x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari sekolah</span><input id="subscription-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama sekolah..." autocomplete="off"><span wire:loading wire:target="search" class="platform-inline-spinner" aria-hidden="true"></span></label>
            <label class="platform-filter-field" for="subscription-status"><span class="sr-only">Filter status langganan</span><select id="subscription-status" wire:model.live="status" class="select-field"><option value="">Semua status</option>@foreach (['trial', 'active', 'past_due', 'suspended', 'cancelled'] as $statusOption)<option value="{{ $statusOption }}">{{ match ($statusOption) { 'trial' => 'Trial', 'active' => 'Aktif', 'past_due' => 'Lewat tempo', 'suspended' => 'Ditangguhkan', default => 'Dibatalkan' } }}</option>@endforeach</select></label>
        </div>
        <div class="table-scroll" wire:loading.class="platform-content-loading" wire:target="search,status">
            <table class="data-table platform-subscription-table">
                <thead><tr><th scope="col">Sekolah</th><th scope="col">Paket</th><th scope="col">Periode</th><th scope="col">Status</th><th scope="col">Berakhir</th><th scope="col" class="platform-table-actions-heading">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($subs as $subscription)
                        @php
                            $statusClass = match ($subscription->status) { 'active' => 'badge-green', 'trial' => 'badge-blue', 'past_due', 'suspended' => 'badge-red', default => 'badge-gray' };
                            $statusLabel = match ($subscription->status) { 'active' => 'Aktif', 'trial' => 'Trial', 'past_due' => 'Lewat tempo', 'suspended' => 'Ditangguhkan', 'cancelled' => 'Dibatalkan', default => ucfirst(str_replace('_', ' ', $subscription->status)) };
                            $endDate = $subscription->status === 'trial' ? $subscription->trial_berakhir_pada : $subscription->periode_berakhir_pada;
                            $schoolName = $names[$subscription->school_id] ?? 'Sekolah tidak ditemukan';
                        @endphp
                        <tr wire:key="platform-subscription-{{ $subscription->id }}">
                            <td><div class="person-cell"><span class="table-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($schoolName, 0, 2)) }}</span><span><strong>{{ $schoolName }}</strong><small>ID Tenant #{{ $subscription->school_id }}</small></span></div></td>
                            <td><strong>{{ $subscription->plan?->nama ?? 'Tanpa paket' }}</strong></td>
                            <td>{{ ucfirst($subscription->periode) }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td class="platform-tabular">{{ $endDate?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                            <td><div class="row-actions platform-row-actions"><a href="{{ route('platform.tenants.show', $subscription->school_id) }}" class="table-action" aria-label="Lihat tenant {{ $schoolName }}" title="Lihat tenant"><x-icon name="arrow-right" class="h-4 w-4" /></a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-symbol"><x-icon name="repeat" class="h-7 w-7" /></div><h3>Tidak ada langganan yang cocok</h3><p>Ubah kata kunci atau status untuk melihat data lainnya.</p>@if ($search !== '' || $status !== '')<button type="button" class="btn btn-soft btn-sm" wire:click="resetFilters">Reset filter</button>@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($subs->hasPages())<div class="platform-pagination">{{ $subs->links() }}</div>@endif
    </section>
</div>
