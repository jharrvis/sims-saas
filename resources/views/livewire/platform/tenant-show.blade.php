<div class="page-stack">
    @if (session('ok'))
        <div class="platform-alert platform-alert-success" role="status" aria-live="polite">
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    @php
        $schoolStatusClass = match ($school->status_langganan) {
            'active' => 'badge-green',
            'trialing' => 'badge-orange',
            'past_due', 'suspended' => 'badge-red',
            default => 'badge-gray',
        };
        $schoolStatusLabel = match ($school->status_langganan) {
            'active' => 'Aktif',
            'trialing' => 'Trial',
            'past_due' => 'Lewat tempo',
            'suspended' => 'Ditangguhkan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $school->status_langganan)),
        };
        $activeModuleCount = $tenantModules->filter(fn ($tenantModule) => $tenantModule->aktif)->count();
    @endphp

    <div class="page-header platform-detail-header">
        <div>
            <a href="{{ route('platform.tenants') }}" class="platform-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke daftar tenant
            </a>
            <span class="eyebrow">Platform / Detail Tenant</span>
            <div class="platform-title-row">
                <h2 class="platform-page-title">{{ $school->nama }}</h2>
                <span class="badge {{ $schoolStatusClass }}">{{ $schoolStatusLabel }}</span>
            </div>
            <p>{{ $school->jenjang }} @if ($school->npsn) · NPSN {{ $school->npsn }} @endif · ID Tenant #{{ $school->id }}</p>
        </div>
        @if (auth()->user()?->hasPermissionTo('platform.tenant.impersonate', 'platform'))
            <div class="page-actions">
                <button type="button" wire:click="impersonate"
                    data-sims-confirm="Masuk sebagai admin {{ $school->nama }}? Aksi ini dicatat di audit log."
                    wire:loading.attr="disabled" wire:target="impersonate" class="btn btn-outline">
                    <x-icon name="user-check" class="h-4 w-4" />
                    <span wire:loading.remove wire:target="impersonate">Masuk sebagai Admin</span>
                    <span wire:loading wire:target="impersonate">Membuka workspace...</span>
                </button>
            </div>
        @endif
    </div>

    <div class="stats-grid platform-tenant-stats">
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon blue"><x-icon name="users" class="h-5 w-5" /></span><span class="badge badge-blue">Aktif</span></div>
            <div class="stat-value">{{ number_format($siswaCount, 0, ',', '.') }}</div>
            <div class="stat-caption">Siswa aktif</div>
            <div class="stat-footer"><span class="stat-note">Pemakaian tenant saat ini</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon purple"><x-icon name="puzzle" class="h-5 w-5" /></span><span class="badge badge-purple">{{ $modules->count() }} tersedia</span></div>
            <div class="stat-value">{{ $activeModuleCount }}</div>
            <div class="stat-caption">Modul aktif</div>
            <div class="stat-footer"><span class="stat-note">Sesuai paket dan override</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon orange"><x-icon name="settings" class="h-5 w-5" /></span><span class="badge badge-orange">Khusus</span></div>
            <div class="stat-value">{{ $overrides->count() }}</div>
            <div class="stat-caption">Override kuota</div>
            <div class="stat-footer"><span class="stat-note">Aturan khusus tenant</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon green"><x-icon name="receipt" class="h-5 w-5" /></span><span class="badge badge-green">10 terbaru</span></div>
            <div class="stat-value">{{ $invoices->count() }}</div>
            <div class="stat-caption">Tagihan tercatat</div>
            <div class="stat-footer"><span class="stat-note">Riwayat langganan terbaru</span></div>
        </article>
    </div>

    <div class="platform-detail-grid">
        <section class="card" aria-labelledby="subscription-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon green"><x-icon name="repeat" class="h-[19px] w-[19px]" /></span>
                    <div><h3 id="subscription-title">Langganan</h3><p>Paket dan periode layanan tenant</p></div>
                </div>
                @if ($subscription)<span class="badge badge-green">{{ ucfirst($subscription->status) }}</span>@endif
            </div>

            @if ($subscription)
                <dl class="platform-definition-list">
                    <div><dt>Paket</dt><dd>{{ $subscription->plan?->nama ?? '—' }}</dd></div>
                    <div><dt>Periode</dt><dd>{{ ucfirst($subscription->periode) }}</dd></div>
                    @if ($subscription->trial_berakhir_pada)<div><dt>Trial berakhir</dt><dd>{{ $subscription->trial_berakhir_pada->format('d M Y') }}</dd></div>@endif
                    @if ($subscription->periode_berakhir_pada)<div><dt>Periode berakhir</dt><dd>{{ $subscription->periode_berakhir_pada->format('d M Y') }}</dd></div>@endif
                </dl>
            @else
                <div class="platform-inline-empty"><x-icon name="calendar-x" class="h-5 w-5" /><span>Belum ada langganan aktif.</span></div>
            @endif

            @if (auth()->user()?->hasPermissionTo('platform.tenant.create', 'platform'))
                <div class="platform-card-section">
                    <h4>Buat atau ganti langganan</h4>
                    <div class="platform-compact-form">
                        <div class="form-group platform-form-grow">
                            <label for="plan_id">Paket</label>
                            <select id="plan_id" wire:model="plan_id" class="select-field">
                                <option value="">Pilih paket</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->nama }} — Rp{{ number_format($plan->harga_bulanan, 0, ',', '.') }}/bulan</option>
                                @endforeach
                            </select>
                            @error('plan_id') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="periode">Periode</label>
                            <select id="periode" wire:model="periode" class="select-field">
                                <option value="bulanan">Bulanan</option>
                                <option value="tahunan">Tahunan</option>
                            </select>
                        </div>
                        <label class="platform-check-control"><input type="checkbox" wire:model="trial"><span>Trial 30 hari</span></label>
                        <button type="button" wire:click="buatLangganan" data-sims-confirm="Buat langganan baru? Langganan aktif sebelumnya dibatalkan."
                            wire:loading.attr="disabled" wire:target="buatLangganan" class="btn btn-primary">
                            <span wire:loading.remove wire:target="buatLangganan">Simpan</span><span wire:loading wire:target="buatLangganan">Menyimpan...</span>
                        </button>
                    </div>
                </div>
            @endif
        </section>

        <section class="card" aria-labelledby="quota-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon orange"><x-icon name="bar-chart-3" class="h-[19px] w-[19px]" /></span>
                    <div><h3 id="quota-title">Kuota & Pemakaian</h3><p>Periode {{ now()->locale('id')->translatedFormat('F Y') }}</p></div>
                </div>
            </div>
            <dl class="platform-definition-list">
                <div><dt>Siswa aktif</dt><dd>{{ number_format($siswaCount, 0, ',', '.') }}</dd></div>
                @foreach ($usages as $usage)
                    <div wire:key="tenant-usage-{{ $usage->id }}"><dt>{{ str_replace('_', ' ', $usage->metrik) }}</dt><dd>{{ number_format($usage->nilai, 0, ',', '.') }}</dd></div>
                @endforeach
            </dl>

            <div class="platform-card-section">
                <h4>Override kuota</h4>
                <div class="platform-record-list">
                    @forelse ($overrides as $override)
                        <div class="platform-record-row" wire:key="tenant-override-{{ $override->id }}">
                            <span><strong>{{ str_replace('_', ' ', $override->metrik) }}</strong><small>{{ $override->batas_custom === null ? 'Tanpa batas' : number_format($override->batas_custom, 0, ',', '.') }}@if (! $override->isBerlaku()) · Kedaluwarsa @endif</small></span>
                            @if (auth()->user()?->hasPermissionTo('platform.tenant.create', 'platform'))
                                <button type="button" wire:click="hapusOverride({{ $override->id }})" data-sims-confirm="Hapus override ini?"
                                    wire:loading.attr="disabled" wire:target="hapusOverride({{ $override->id }})" class="table-action danger"
                                    aria-label="Hapus override {{ $override->metrik }}"><x-icon name="trash-2" class="h-4 w-4" /></button>
                            @endif
                        </div>
                    @empty
                        <div class="platform-inline-empty"><x-icon name="settings" class="h-5 w-5" /><span>Belum ada override khusus.</span></div>
                    @endforelse
                </div>

                @if (auth()->user()?->hasPermissionTo('platform.tenant.create', 'platform'))
                    <div class="platform-compact-form platform-quota-form">
                        <div class="form-group">
                            <label for="ov_metrik">Metrik</label>
                            <select id="ov_metrik" wire:model="ov_metrik" class="select-field">
                                <option value="">Pilih metrik</option><option value="siswa_aktif">Siswa aktif</option><option value="user_staf">User staf</option><option value="storage_mb">Storage MB</option><option value="wa_bulanan">WA bulanan</option>
                            </select>
                            @error('ov_metrik') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="ov_batas">Batas</label>
                            <input id="ov_batas" type="number" wire:model="ov_batas" min="0" class="field platform-short-field" placeholder="Tanpa batas">
                            @error('ov_batas') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group platform-form-grow">
                            <label for="ov_alasan">Alasan</label>
                            <input id="ov_alasan" type="text" wire:model="ov_alasan" class="field" placeholder="Contoh: penyesuaian kontrak">
                            @error('ov_alasan') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                        </div>
                        <button type="button" wire:click="tambahOverride" wire:loading.attr="disabled" wire:target="tambahOverride" class="btn btn-primary">
                            <span wire:loading.remove wire:target="tambahOverride">Tambah</span><span wire:loading wire:target="tambahOverride">Menyimpan...</span>
                        </button>
                    </div>
                @endif
            </div>
        </section>

        <section class="card" aria-labelledby="modules-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon purple"><x-icon name="puzzle" class="h-[19px] w-[19px]" /></span>
                    <div><h3 id="modules-title">Modul Tenant</h3><p>{{ $activeModuleCount }} dari {{ $modules->count() }} modul aktif</p></div>
                </div>
            </div>
            <div class="platform-module-list sims-scroll">
                @foreach ($modules as $module)
                    @php $tenantModule = $tenantModules->get($module->id); @endphp
                    <div class="platform-module-row" wire:key="tenant-module-{{ $module->id }}">
                        <span><strong>{{ $module->nama }}</strong><small>{{ ucfirst($module->kategori) }}</small></span>
                        @if (auth()->user()?->hasPermissionTo('platform.module.toggle', 'platform'))
                            <button type="button" wire:click="toggleModule({{ $module->id }})" wire:loading.attr="disabled"
                                wire:target="toggleModule({{ $module->id }})" class="platform-module-toggle {{ $tenantModule?->aktif ? 'is-active' : '' }}"
                                aria-pressed="{{ $tenantModule?->aktif ? 'true' : 'false' }}">
                                <span aria-hidden="true"></span>{{ $tenantModule?->aktif ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        @else
                            <span class="badge {{ $tenantModule?->aktif ? 'badge-green' : 'badge-gray' }}">{{ $tenantModule?->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card" aria-labelledby="invoices-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="receipt" class="h-[19px] w-[19px]" /></span>
                    <div><h3 id="invoices-title">Tagihan Langganan</h3><p>Sepuluh transaksi terbaru tenant</p></div>
                </div>
                <a href="{{ route('platform.invoices') }}" class="action-link">Semua tagihan <x-icon name="arrow-right" class="h-3.5 w-3.5" /></a>
            </div>
            <div class="platform-record-list">
                @forelse ($invoices as $invoice)
                    <div class="platform-record-row" wire:key="tenant-invoice-{{ $invoice->id }}">
                        <span><strong class="platform-tabular">{{ $invoice->nomor }}</strong><small>Jatuh tempo {{ $invoice->jatuh_tempo?->format('d M Y') ?? '—' }}</small></span>
                        <span class="platform-invoice-value"><strong>Rp{{ number_format($invoice->total, 0, ',', '.') }}</strong><span class="badge {{ $invoice->status === 'paid' ? 'badge-green' : ($invoice->status === 'overdue' ? 'badge-red' : 'badge-orange') }}">{{ ucfirst($invoice->status) }}</span></span>
                    </div>
                @empty
                    <div class="empty-state platform-compact-empty"><div class="empty-symbol"><x-icon name="receipt" class="h-6 w-6" /></div><h3>Belum ada tagihan</h3><p>Tagihan langganan tenant akan tampil di sini.</p></div>
                @endforelse
            </div>
        </section>
    </div>
</div>
