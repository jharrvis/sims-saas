<div class="page-stack">
    @if (session('ok'))
        <div class="platform-alert platform-alert-success" role="status" aria-live="polite">
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <div class="page-header">
        <div>
            <span class="eyebrow">Platform / Manajemen Tenant</span>
            <h2 class="platform-page-title">Daftar Sekolah</h2>
            <p>Kelola status layanan, paket aktif, dan akses setiap tenant dari satu workspace platform.</p>
        </div>
        @if (auth()->user()?->hasPermissionTo('platform.tenant.create', 'platform'))
            <div class="page-actions">
                <a href="{{ route('platform.tenants.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Tenant
                </a>
            </div>
        @endif
    </div>

    <section class="card platform-table-card" aria-labelledby="tenant-list-title">
        <div class="card-head platform-table-heading">
            <div class="card-title">
                <span class="mini-icon orange"><x-icon name="school" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="tenant-list-title">Direktori Tenant</h3>
                    <p>{{ number_format($tenants->total(), 0, ',', '.') }} sekolah ditemukan</p>
                </div>
            </div>
        </div>

        <div class="table-toolbar platform-tenant-toolbar">
            <label class="field-search platform-search-field" for="tenant-search">
                <x-icon name="search" class="h-4 w-4" />
                <span class="sr-only">Cari tenant</span>
                <input id="tenant-search" type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama sekolah atau NPSN..." autocomplete="off">
                <span wire:loading wire:target="search" class="platform-inline-spinner" aria-hidden="true"></span>
            </label>

            <label class="platform-filter-field" for="tenant-status">
                <span class="sr-only">Filter status langganan</span>
                <select id="tenant-status" wire:model.live="status" class="select-field">
                    <option value="">Semua status</option>
                    @foreach (['trialing', 'active', 'past_due', 'suspended', 'cancelled'] as $statusOption)
                        <option value="{{ $statusOption }}">{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="table-scroll" wire:loading.class="platform-content-loading" wire:target="search,status">
            <table class="data-table platform-tenant-table">
                <thead>
                    <tr>
                        <th scope="col">Sekolah</th>
                        <th scope="col">NPSN</th>
                        <th scope="col">Paket</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="platform-table-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenants as $tenant)
                        @php
                            $statusClass = match ($tenant->status_langganan) {
                                'active' => 'badge-green',
                                'trialing' => 'badge-orange',
                                'past_due', 'suspended' => 'badge-red',
                                default => 'badge-gray',
                            };
                            $statusLabel = match ($tenant->status_langganan) {
                                'active' => 'Aktif',
                                'trialing' => 'Trial',
                                'past_due' => 'Lewat tempo',
                                'suspended' => 'Ditangguhkan',
                                'cancelled' => 'Dibatalkan',
                                default => ucfirst(str_replace('_', ' ', $tenant->status_langganan)),
                            };
                        @endphp
                        <tr wire:key="platform-tenant-{{ $tenant->id }}">
                            <td>
                                <div class="person-cell">
                                    <span class="table-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($tenant->nama, 0, 2)) }}</span>
                                    <span>
                                        <a href="{{ route('platform.tenants.show', $tenant->id) }}" class="platform-tenant-name">{{ $tenant->nama }}</a>
                                        <small>{{ $tenant->jenjang }} · Workspace sekolah</small>
                                    </span>
                                </div>
                            </td>
                            <td class="platform-tabular">{{ $tenant->npsn ?: '—' }}</td>
                            <td>{{ $tenant->subscription?->plan?->nama ?? 'Belum ada paket' }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td>
                                <div class="row-actions platform-row-actions">
                                    <a href="{{ route('platform.tenants.show', $tenant->id) }}" class="table-action"
                                        aria-label="Lihat detail {{ $tenant->nama }}" title="Lihat detail">
                                        <x-icon name="arrow-right" class="h-4 w-4" />
                                    </a>
                                    @if (auth()->user()?->hasPermissionTo('platform.tenant.suspend', 'platform'))
                                        @if ($tenant->status_langganan !== 'suspended')
                                            <button type="button" wire:click="suspend({{ $tenant->id }})"
                                                wire:confirm="Suspend tenant {{ $tenant->nama }}? Akses tulis mereka akan diblokir."
                                                wire:loading.attr="disabled" wire:target="suspend({{ $tenant->id }})"
                                                class="table-action danger" aria-label="Suspend {{ $tenant->nama }}" title="Suspend tenant">
                                                <x-icon name="shield-alert" class="h-4 w-4" />
                                            </button>
                                        @else
                                            <button type="button" wire:click="activate({{ $tenant->id }})"
                                                wire:confirm="Aktifkan kembali {{ $tenant->nama }}?"
                                                wire:loading.attr="disabled" wire:target="activate({{ $tenant->id }})"
                                                class="table-action platform-action-success" aria-label="Aktifkan {{ $tenant->nama }}" title="Aktifkan tenant">
                                                <x-icon name="check-circle" class="h-4 w-4" />
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-symbol"><x-icon name="school" class="h-7 w-7" /></div>
                                    <h3>Tidak ada tenant yang cocok</h3>
                                    <p>Ubah kata kunci atau filter status untuk melihat sekolah lainnya.</p>
                                    @if ($search !== '' || $status !== '')
                                        <button type="button" class="btn btn-soft btn-sm" wire:click="resetFilters">Reset filter</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tenants->hasPages())
            <div class="platform-pagination">{{ $tenants->links() }}</div>
        @endif
    </section>
</div>
