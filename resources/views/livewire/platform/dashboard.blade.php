<div class="page-stack flex flex-col">
    <div class="page-header">
        <div>
            <span class="eyebrow">Sistem Informasi Manajemen Sekolah</span>
            <h2 style="font-size: 21px;">Ringkasan Platform</h2>
            <p>Pantau pertumbuhan tenant, langganan, dan kesehatan operasional layanan.</p>
        </div>
        <div class="page-actions flex flex-wrap">
            @if (\Illuminate\Support\Facades\Route::has('platform.tenants'))
                <a href="{{ route('platform.tenants') }}" class="btn btn-outline">
                    <x-icon name="school" class="h-4 w-4" /> Kelola Tenant
                </a>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('platform.invoices'))
                <a href="{{ route('platform.invoices') }}" class="btn btn-primary">
                    <x-icon name="receipt" class="h-4 w-4" /> Lihat Tagihan
                </a>
            @endif
        </div>
    </div>

    <div class="stats-grid grid">
        <article class="card stat-card">
            <div class="stat-top">
                <span class="mini-icon blue"><x-icon name="school" class="h-5 w-5" /></span>
                <span class="badge badge-blue">Tenant</span>
            </div>
            <div class="stat-value">{{ number_format($metrics['total_tenant'], 0, ',', '.') }}</div>
            <div class="stat-caption">Total Tenant</div>
            <div class="stat-footer"><span class="stat-note">Sekolah terdaftar di platform</span></div>
        </article>

        <article class="card stat-card">
            <div class="stat-top">
                <span class="mini-icon green"><x-icon name="wallet" class="h-5 w-5" /></span>
                <span class="badge badge-green">MRR</span>
            </div>
            <div class="stat-value" style="font-size: 25px;">Rp{{ number_format($metrics['mrr'], 0, ',', '.') }}</div>
            <div class="stat-caption">Pendapatan berulang bulanan</div>
            <div class="stat-footer"><span class="stat-note">Langganan aktif</span></div>
        </article>

        <article class="card stat-card">
            <div class="stat-top">
                <span class="mini-icon orange"><x-icon name="clock" class="h-5 w-5" /></span>
                <span class="badge badge-orange">≤ 7 hari</span>
            </div>
            <div class="stat-value">{{ number_format($metrics['trial_berakhir_7_hari'], 0, ',', '.') }}</div>
            <div class="stat-caption">Trial berakhir ≤ 7 hari</div>
            <div class="stat-footer"><span class="stat-note">Perlu tindak lanjut</span></div>
        </article>

        <article class="card stat-card">
            <div class="stat-top">
                <span class="mini-icon red"><x-icon name="receipt" class="h-5 w-5" /></span>
                <span class="badge badge-red">{{ $metrics['invoice_overdue'] }} overdue</span>
            </div>
            <div class="stat-value" style="font-size: 25px;">Rp{{ number_format($metrics['piutang'], 0, ',', '.') }}</div>
            <div class="stat-caption">Piutang</div>
            <div class="stat-footer"><span class="stat-note">Tagihan belum dibayar</span></div>
        </article>
    </div>

    <section class="card rounded-2xl border" style="margin-top: 16px;">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon purple"><x-icon name="repeat" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3>Tenant per Status Langganan</h3>
                    <p>Komposisi tenant berdasarkan status aktif</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @forelse ($metrics['per_status'] as $status => $jumlah)
                <span class="badge badge-blue">
                    <span class="capitalize">{{ str_replace('_', ' ', $status) }}</span>
                    <strong>{{ $jumlah }}</strong>
                </span>
            @empty
                <p class="text-sm text-slate-400">Belum ada tenant.</p>
            @endforelse
        </div>
    </section>

    <div class="grid gap-4 lg:grid-cols-2" style="margin-top: 16px;">
        <section class="card rounded-2xl border">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon orange"><x-icon name="clock" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3>Trial Segera Berakhir</h3>
                        <p>Tenant yang perlu dihubungi</p>
                    </div>
                </div>
            </div>

            @forelse ($trialSoon as $subscription)
                <div class="setting-row">
                    <div>
                        <strong>{{ $this->schoolName($subscription->school_id) }}</strong>
                        <p>{{ $subscription->plan?->nama ?? 'Paket trial' }}</p>
                    </div>
                    <span class="badge badge-orange">{{ $subscription->trial_berakhir_pada?->format('d M Y') ?? '—' }}</span>
                </div>
            @empty
                <div class="empty-state" style="padding: 28px 16px;">
                    <div class="empty-symbol"><x-icon name="check-circle" class="h-6 w-6" /></div>
                    <h3>Tidak ada trial mendesak</h3>
                    <p>Semua trial masih memiliki waktu lebih dari 7 hari.</p>
                </div>
            @endforelse
        </section>

        <section class="card rounded-2xl border">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon red"><x-icon name="alert-triangle" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3>Tagihan Overdue</h3>
                        <p>Prioritas penagihan platform</p>
                    </div>
                </div>
            </div>

            @forelse ($overdue as $invoice)
                <div class="setting-row">
                    <div>
                        <strong>{{ $invoice->nama_sekolah_snapshot }}</strong>
                        <p>{{ $invoice->nomor }}</p>
                    </div>
                    <span class="badge badge-red">Rp{{ number_format($invoice->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <div class="empty-state" style="padding: 28px 16px;">
                    <div class="empty-symbol"><x-icon name="check-circle" class="h-6 w-6" /></div>
                    <h3>Tidak ada tagihan overdue</h3>
                    <p>Seluruh tagihan platform dalam kondisi baik.</p>
                </div>
            @endforelse
        </section>
    </div>
</div>
