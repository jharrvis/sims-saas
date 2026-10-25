<div class="page-stack">
    <div class="page-header">
        <div><span class="eyebrow">Platform / Keamanan</span><h2 class="platform-page-title">Audit Log</h2><p>Telusuri aktivitas operator dan perubahan penting lintas tenant.</p></div>
    </div>

    <section class="card platform-table-card" aria-labelledby="audit-list-title">
        <div class="card-head platform-table-heading"><div class="card-title"><span class="mini-icon red"><x-icon name="shield" class="h-[19px] w-[19px]" /></span><div><h3 id="audit-list-title">Jejak Aktivitas</h3><p>{{ number_format($logs->total(), 0, ',', '.') }} aktivitas tercatat</p></div></div></div>
        <div class="table-toolbar platform-tenant-toolbar">
            <label class="field-search platform-search-field" for="audit-search"><x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari aksi audit</span><input id="audit-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama aksi..." autocomplete="off"><span wire:loading wire:target="search" class="platform-inline-spinner" aria-hidden="true"></span></label>
            <span class="platform-toolbar-note"><x-icon name="lock" class="h-4 w-4" /> Catatan bersifat hanya-baca</span>
        </div>
        <div class="table-scroll" wire:loading.class="platform-content-loading" wire:target="search">
            <table class="data-table platform-audit-table">
                <thead><tr><th scope="col">Waktu</th><th scope="col">Aktivitas</th><th scope="col">Tenant</th><th scope="col">Operator</th><th scope="col">Sumber</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php
                            $actionLabel = str_replace(['.', '_'], [' / ', ' '], $log->aksi);
                            $tenantName = $log->school_id ? ($names[$log->school_id] ?? 'Tenant #'.$log->school_id) : 'Platform';
                            $userName = $log->user_id ? ($users[$log->user_id] ?? 'User #'.$log->user_id) : 'Sistem';
                        @endphp
                        <tr wire:key="platform-audit-{{ $log->id }}">
                            <td><strong class="platform-tabular">{{ $log->created_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</strong><small class="platform-cell-meta">{{ $log->created_at?->format('H:i:s') ?? '—' }} WIB</small></td>
                            <td><span class="platform-audit-action">{{ $actionLabel }}</span>@if ($log->model_type)<small class="platform-cell-meta">{{ class_basename($log->model_type) }}{{ $log->model_id ? ' #'.$log->model_id : '' }}</small>@endif</td>
                            <td><span class="badge {{ $log->school_id ? 'badge-blue' : 'badge-purple' }}">{{ $tenantName }}</span></td>
                            <td><strong>{{ $userName }}</strong><small class="platform-cell-meta">{{ $log->user_id ? 'ID #'.$log->user_id : 'Proses otomatis' }}</small></td>
                            <td><span class="platform-reference">{{ $log->ip_address ?: '—' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><div class="empty-symbol"><x-icon name="shield-check" class="h-7 w-7" /></div><h3>Aktivitas tidak ditemukan</h3><p>Ubah kata kunci untuk mencari nama aksi lainnya.</p>@if ($search !== '')<button type="button" class="btn btn-soft btn-sm" wire:click="resetFilters">Reset pencarian</button>@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())<div class="platform-pagination">{{ $logs->links() }}</div>@endif
    </section>
</div>
