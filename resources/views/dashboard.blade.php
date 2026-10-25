@php
    $dash = $dash ?? app(\App\Domains\Akademik\Services\DashboardService::class)->data(auth()->user());
    $trend = collect($dash['attendance_trend'] ?? []);
    $classes = collect($dash['class_distribution'] ?? []);
    $classTotal = max(1, (int) $classes->sum('value'));
    $colors = ['#4b83f1', '#4dbcb0', '#f5ad3c', '#ae72ee', '#e879b1', '#f7cf66', '#60c9d0', '#8b86fb'];
    $stops = [];
    $cursor = 0;

    foreach ($classes as $index => $class) {
        $next = $cursor + ((int) $class['value'] / $classTotal * 100);
        $stops[] = ($colors[$index % count($colors)]).' '.$cursor.'% '.$next.'%';
        $cursor = $next;
    }

    $donut = $stops !== [] ? 'conic-gradient('.implode(', ', $stops).')' : 'conic-gradient(#e8edf5 0% 100%)';
    $trendPoints = $trend->values()->map(function (array $item, int $index) use ($trend): string {
        $x = $trend->count() > 1 ? ($index * 100 / ($trend->count() - 1)) : 50;
        $y = 100 - (float) $item['value'];

        return round($x, 2).','.round($y, 2);
    })->implode(' ');
@endphp

<div class="page-stack flex flex-col">
    <div class="page-header">
        <div>
            <span class="eyebrow">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
            <h2 style="font-size: 21px;">Selamat datang kembali</h2>
            <p>Berikut ringkasan aktivitas dan kondisi sekolah hari ini.</p>
        </div>
        <div class="page-actions flex flex-wrap">
            @can('laporan.view')
                <a href="{{ route('laporan.index') }}" class="btn btn-outline">
                    <x-icon name="bar-chart-3" class="h-4 w-4" /> Lihat laporan
                </a>
            @endcan
            @can('siswa.create')
                <a href="{{ route('siswa.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah siswa
                </a>
            @endcan
        </div>
    </div>

    <div class="stats-grid grid">
        <article class="card stat-card">
            <div class="stat-top">
                <span class="mini-icon blue"><x-icon name="users" class="h-5 w-5" /></span>
                <span class="badge badge-blue">Siswa</span>
            </div>
            <div class="stat-value">{{ number_format($dash['total_students'], 0, ',', '.') }}</div>
            <div class="stat-caption">Siswa Aktif</div>
            <div class="stat-footer"><span class="stat-note">Terdaftar di sekolah</span></div>
        </article>

        <article class="card stat-card">
            <div class="stat-top">
                <span class="mini-icon green"><x-icon name="user-round-check" class="h-5 w-5" /></span>
                <span class="badge badge-green">{{ $dash['attendance_percent'] }}%</span>
            </div>
            <div class="stat-value">{{ number_format($dash['attendance_today'], 0, ',', '.') }}</div>
            <div class="stat-caption">Kehadiran Hari Ini</div>
            <div class="stat-footer"><span class="stat-note">{{ $dash['attendance_present'] }} siswa hadir</span></div>
        </article>

        <article class="card stat-card">
            <div class="stat-top">
                <span class="mini-icon purple"><x-icon name="contact-round" class="h-5 w-5" /></span>
                <span class="badge badge-purple">Guru</span>
            </div>
            <div class="stat-value">{{ number_format($dash['total_employees'], 0, ',', '.') }}</div>
            <div class="stat-caption">Guru & Tenaga Pendidik</div>
            <div class="stat-footer"><span class="stat-note">Status aktif</span></div>
        </article>

        <article class="card stat-card">
            <div class="stat-top">
                <span class="mini-icon orange"><x-icon name="wallet" class="h-5 w-5" /></span>
                <span class="badge badge-orange">{{ $dash['unpaid_invoices'] }} tagihan</span>
            </div>
            <div class="stat-value" style="font-size: 24px;">Rp{{ number_format($dash['unpaid_total'], 0, ',', '.') }}</div>
            <div class="stat-caption">Tagihan Belum Dibayar</div>
            <div class="stat-footer"><span class="stat-note">Perlu tindak lanjut</span></div>
        </article>
    </div>

    <div class="dashboard-main-grid" style="margin-top: 16px;">
        <section class="card rounded-2xl border chart-card">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="bar-chart-3" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3>Tren Kehadiran Siswa</h3>
                        <p>Persentase kehadiran 7 hari terakhir</p>
                    </div>
                </div>
                <span class="badge badge-green">{{ $dash['attendance_percent'] }}% hari ini</span>
            </div>

            @if ($trend->isNotEmpty())
                <div class="chart-area">
                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="attendance-chart" role="img"
                         aria-label="Grafik tren kehadiran siswa tujuh hari terakhir">
                        <defs>
                            <linearGradient id="attendanceFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#739cfe" stop-opacity=".24" />
                                <stop offset="100%" stop-color="#739cfe" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        @foreach ([0, 25, 50, 75, 100] as $line)
                            <line x1="0" y1="{{ 100 - $line }}" x2="100" y2="{{ 100 - $line }}"
                                  stroke="var(--line)" stroke-width="0.35" stroke-dasharray="1.2 1.6" />
                        @endforeach
                        <polygon points="0,100 {{ $trendPoints }} 100,100" fill="url(#attendanceFill)" />
                        <polyline points="{{ $trendPoints }}" fill="none" stroke="#4775f4" stroke-width="1.3"
                                  stroke-linejoin="round" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="flex justify-between gap-2 pt-2">
                    @foreach ($trend as $item)
                        <span class="text-[9px] font-semibold text-slate-400">{{ $item['label'] }}</span>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-symbol"><x-icon name="bar-chart-3" class="h-6 w-6" /></div>
                    <h3>Belum ada data kehadiran</h3>
                    <p>Data tren akan muncul setelah absensi siswa dicatat.</p>
                </div>
            @endif
        </section>

        <section class="card rounded-2xl border chart-card">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon purple"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3>Distribusi Siswa</h3>
                        <p>Jumlah siswa per kelas</p>
                    </div>
                </div>
                <span class="badge badge-gray">{{ $classes->sum('value') }} siswa</span>
            </div>

            @if ($classes->isNotEmpty())
                <div class="donut-wrap">
                    <div class="donut" style="background: {{ $donut }};">
                        <div class="donut-center">
                            <strong>{{ $classes->sum('value') }}</strong>
                            <small>Siswa</small>
                        </div>
                    </div>
                    <div class="legend">
                        @foreach ($classes as $index => $class)
                            <div class="legend-row">
                                <span class="legend-dot" style="--dot: {{ $colors[$index % count($colors)] }};"></span>
                                <span>{{ $class['label'] }}</span>
                                <strong>{{ $class['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
                @can('kelas.view')
                    <a class="action-link" href="{{ route('kelas.index') }}">Kelola seluruh kelas <x-icon name="arrow-right" class="h-[13px] w-[13px]" /></a>
                @endcan
            @else
                <div class="empty-state">
                    <div class="empty-symbol"><x-icon name="school" class="h-6 w-6" /></div>
                    <h3>Belum ada kelas</h3>
                    <p>Tambahkan rombongan belajar untuk melihat distribusi siswa.</p>
                </div>
            @endif
        </section>

        <section class="card rounded-2xl border chart-card">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon orange"><x-icon name="wallet" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3>Ringkasan Keuangan</h3>
                        <p>Status tagihan bulan ini</p>
                    </div>
                </div>
                <span class="badge badge-gray">{{ now()->locale('id')->isoFormat('MMMM') }}</span>
            </div>

            <small class="muted">Total tagihan</small>
            <div class="finance-amount">Rp{{ number_format($dash['month_total'], 0, ',', '.') }}</div>
            <small class="muted">Sudah dibayar</small>
            <div style="font-weight: 800; margin: 4px 0;">Rp{{ number_format($dash['month_paid'], 0, ',', '.') }}</div>
            <div class="progress" style="--progress: {{ $dash['paid_percent'] }}%; --progress-color: var(--green);">
                <div></div>
            </div>
            <small class="muted" style="display: block; margin-top: 8px;">Belum dibayar: Rp{{ number_format(max(0, $dash['month_total'] - $dash['month_paid']), 0, ',', '.') }}</small>

            @can('keuangan.tagihan.view')
                <a class="action-link" style="margin-top: 15px;" href="{{ route('keuangan.index') }}">Detail keuangan <x-icon name="arrow-right" class="h-[13px] w-[13px]" /></a>
            @endcan
        </section>
    </div>

    <div class="dashboard-lower-grid" style="margin-top: 16px;">
        <div class="dash-col">
            <section class="card rounded-2xl border">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon green"><x-icon name="calendar-days" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3>Jadwal Pelajaran Hari Ini</h3>
                            <p>{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                        </div>
                    </div>
                    @can('jadwal.view')
                        <a class="action-link" href="{{ route('jadwal.index') }}">Semua jadwal <x-icon name="arrow-right" class="h-[13px] w-[13px]" /></a>
                    @endcan
                </div>

                @forelse ($dash['schedule'] as $schedule)
                    <div class="schedule-row">
                        <span class="status-dot" style="--dot: #4361ee;"></span>
                        <span class="schedule-time">Jam {{ $schedule->jam_ke }}</span>
                        <span class="schedule-main">
                            <strong>{{ $schedule->subject?->nama ?? 'Mata pelajaran' }}</strong>
                            <small>{{ $schedule->rombel?->nama ?? 'Kelas' }} · {{ $schedule->employee?->nama ?? 'Guru' }}</small>
                        </span>
                    </div>
                @empty
                    <div class="empty-state" style="padding: 28px 16px;">
                        <div class="empty-symbol"><x-icon name="calendar-days" class="h-6 w-6" /></div>
                        <h3>Tidak ada jadwal hari ini</h3>
                        <p>Jadwal akan tampil saat hari efektif sekolah berjalan.</p>
                    </div>
                @endforelse
            </section>

            <section class="card rounded-2xl border" style="margin-top: 15px;">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon blue"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3>Siswa Terbaru</h3>
                            <p>Pendaftaran siswa terbaru</p>
                        </div>
                    </div>
                    @can('siswa.view')
                        <a class="action-link" href="{{ route('siswa.index') }}">Lihat semua <x-icon name="arrow-right" class="h-[13px] w-[13px]" /></a>
                    @endcan
                </div>

                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                        <tr><th>Nama</th><th>NIS</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        @forelse ($dash['recent_students'] as $student)
                            <tr>
                                <td>
                                    <div class="person-cell">
                                        <span class="table-avatar">{{ mb_substr($student->nama, 0, 2) }}</span>
                                        <strong>{{ $student->nama }}</strong>
                                    </div>
                                </td>
                                <td>{{ $student->nis }}</td>
                                <td><span class="badge {{ $student->status === 'aktif' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($student->status ?? 'aktif') }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-slate-400">Belum ada siswa.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="dash-col">
            <section class="card rounded-2xl border">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon purple"><x-icon name="megaphone" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3>Pengumuman Terbaru</h3>
                            <p>Informasi untuk komunitas sekolah</p>
                        </div>
                    </div>
                    @can('pengumuman.view')
                        <a class="action-link" href="{{ route('pengumuman.index') }}">Lihat semua <x-icon name="arrow-right" class="h-[13px] w-[13px]" /></a>
                    @endcan
                </div>

                @forelse ($dash['announcements'] as $announcement)
                    <div class="announcement">
                        <span class="mini-icon sm purple"><x-icon name="megaphone" class="h-4 w-4" /></span>
                        <div>
                            <strong>{{ $announcement->judul }}</strong>
                            <small>{{ $announcement->tayang_dari?->locale('id')->isoFormat('D MMM Y') ?? '—' }} · Admin Sekolah</small>
                            <p>{{ Str::limit(strip_tags($announcement->isi), 80) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="padding: 28px 16px;">
                        <div class="empty-symbol"><x-icon name="megaphone" class="h-6 w-6" /></div>
                        <h3>Belum ada pengumuman</h3>
                        <p>Pengumuman sekolah akan tampil di sini.</p>
                    </div>
                @endforelse
            </section>

            <section class="card rounded-2xl border" style="margin-top: 15px;">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon blue"><x-icon name="clock" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3>Aktivitas Terkini</h3>
                            <p>Audit aktivitas pada sistem</p>
                        </div>
                    </div>
                </div>

                @forelse ($dash['activities'] as $activity)
                    <div class="timeline-item">
                        <span class="mini-icon sm blue"><x-icon name="edit-3" class="h-[14px] w-[14px]" /></span>
                        <div>
                            <strong>{{ ucfirst(str_replace('_', ' ', $activity->aksi)) }}</strong>
                            <small>{{ $activity->created_at?->diffForHumans() ?? 'Baru saja' }}</small>
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="padding: 28px 16px;">
                        <div class="empty-symbol"><x-icon name="clock" class="h-6 w-6" /></div>
                        <h3>Belum ada aktivitas</h3>
                        <p>Aktivitas penting akan tercatat di sini.</p>
                    </div>
                @endforelse
            </section>
        </div>

        <div class="dash-col">
            <section class="card rounded-2xl border">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon orange"><x-icon name="zap" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3>Aksi Cepat</h3>
                            <p>Pintas aktivitas harian</p>
                        </div>
                    </div>
                </div>

                <div class="quick-grid">
                    @can('siswa.create')
                        <a class="quick-action" href="{{ route('siswa.create') }}">
                            <span class="mini-icon blue"><x-icon name="user-plus" class="h-[19px] w-[19px]" /></span>
                            <span>Tambah Siswa</span>
                            <x-icon name="chevron-right" class="h-[14px] w-[14px]" />
                        </a>
                    @endcan
                    @can('nilai.input')
                        <a class="quick-action" href="{{ route('nilai.input') }}">
                            <span class="mini-icon green"><x-icon name="pen-line" class="h-[19px] w-[19px]" /></span>
                            <span>Input Nilai</span>
                            <x-icon name="chevron-right" class="h-[14px] w-[14px]" />
                        </a>
                    @endcan
                    @can('absensi.view')
                        <a class="quick-action" href="{{ route('absensi.index') }}">
                            <span class="mini-icon orange"><x-icon name="clipboard-check" class="h-[19px] w-[19px]" /></span>
                            <span>Absensi Hari Ini</span>
                            <x-icon name="chevron-right" class="h-[14px] w-[14px]" />
                        </a>
                    @endcan
                    @can('laporan.view')
                        <a class="quick-action" href="{{ route('laporan.index') }}">
                            <span class="mini-icon purple"><x-icon name="bar-chart-3" class="h-[19px] w-[19px]" /></span>
                            <span>Lihat Laporan</span>
                            <x-icon name="chevron-right" class="h-[14px] w-[14px]" />
                        </a>
                    @endcan
                </div>
            </section>

            <section class="card rounded-2xl border" style="margin-top: 15px;">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon green"><x-icon name="shield-check" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3>Status Sekolah</h3>
                            <p>Informasi operasional</p>
                        </div>
                    </div>
                </div>
                <div class="setting-row">
                    <div><strong>Kehadiran hari ini</strong><p>{{ $dash['attendance_present'] }} dari {{ $dash['attendance_today'] }} siswa</p></div>
                    <span class="badge badge-green">{{ $dash['attendance_percent'] }}%</span>
                </div>
                <div class="setting-row">
                    <div><strong>Tagihan aktif</strong><p>Perlu ditindaklanjuti</p></div>
                    <span class="badge {{ $dash['unpaid_invoices'] > 0 ? 'badge-orange' : 'badge-green' }}">{{ $dash['unpaid_invoices'] }}</span>
                </div>
                <div class="setting-row">
                    <div><strong>Kelas terdaftar</strong><p>Rombongan belajar</p></div>
                    <span class="badge badge-blue">{{ $dash['class_distribution']->count() }}</span>
                </div>
            </section>
        </div>
    </div>
</div>
