<div>
    @php
        $hour = (int) now()->format('H');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
        $attendanceTotal = $rekapAbsensi->sum();
        $attendancePresent = (int) ($rekapAbsensi['H'] ?? 0);
        $attendancePercentage = $attendanceTotal > 0 ? (int) round($attendancePresent / $attendanceTotal * 100) : 0;
        $excusedAbsences = (int) (($rekapAbsensi['I'] ?? 0) + ($rekapAbsensi['S'] ?? 0));
        $displayName = $student?->nama ?? auth()->user()?->name ?? 'Pengguna';
    @endphp

    <div class="screen app-screen pwa-home-screen screen-enter">
        <div class="mobile-heading">
            <div class="min-w-0">
                <span class="overline">{{ $role === 'orang-tua' ? 'PORTAL ORANG TUA' : 'PORTAL SISWA' }}</span>
                <h1 class="truncate">{{ $greeting }}, {{ $displayName }}</h1>
                <p>{{ $rombel?->nama ?? 'Kelas belum ditetapkan' }}{{ $student?->nis ? ' · NIS '.$student->nis : '' }}</p>
            </div>
            <a href="{{ route('pwa.profil') }}" class="avatar" aria-label="Buka profil">
                @if ($student?->fotoUrl())
                    <img src="{{ $student->fotoUrl() }}" alt="" class="h-full w-full rounded-[inherit] object-cover">
                @else
                    {{ mb_substr($displayName, 0, 1) }}
                @endif
            </a>
        </div>

        <section class="performance-hero" aria-labelledby="attendance-summary">
            <span class="hero-orb one" aria-hidden="true"></span>
            <span class="hero-orb two" aria-hidden="true"></span>
            <div class="hero-top">
                <span id="attendance-summary">Kehadiran bulan ini</span>
                <span class="semester-pill">{{ $role === 'orang-tua' ? 'Anak aktif' : 'Data terkini' }}</span>
            </div>
            <div class="score-layout">
                <div>
                    <strong>{{ $attendancePercentage }}%</strong>
                    <small>{{ $attendancePresent }} hadir dari {{ $attendanceTotal }} catatan</small>
                </div>
                <div class="radial-progress" style="--value: {{ $attendancePercentage }}" aria-label="Kehadiran {{ $attendancePercentage }} persen">
                    <span>{{ $attendancePercentage }}<small>PERSEN</small></span>
                </div>
            </div>
            <div class="hero-trend">
                <x-icon name="check-circle" class="h-4 w-4" />
                <span><strong>{{ $excusedAbsences }}</strong> izin atau sakit pada periode ini</span>
            </div>
        </section>

        <div class="stat-grid">
            <a href="{{ route('pwa.nilai') }}" class="mini-stat">
                <span class="mini-icon violet"><x-icon name="chart-column" class="h-5 w-5" /></span>
                <span><strong>Nilai</strong><small>Lihat hasil belajar</small></span>
                <x-icon name="chevron-right" class="h-4 w-4" />
            </a>
            <a href="{{ route('pwa.tagihan') }}" class="mini-stat">
                <span class="mini-icon {{ $tagihanAktif > 0 ? 'amber' : 'green' }}"><x-icon name="wallet" class="h-5 w-5" /></span>
                <span><strong>{{ $tagihanAktif > 0 ? $tagihanAktif.' tagihan' : 'Lunas' }}</strong><small>Status keuangan</small></span>
                <x-icon name="chevron-right" class="h-4 w-4" />
            </a>
            <a href="{{ route('pwa.absensi') }}" class="mini-stat">
                <span class="mini-icon green"><x-icon name="check-circle" class="h-5 w-5" /></span>
                <span><strong>Absensi</strong><small>Riwayat kehadiran</small></span>
                <x-icon name="chevron-right" class="h-4 w-4" />
            </a>
            <a href="{{ route('pwa.pengumuman') }}" class="mini-stat">
                <span class="mini-icon blue"><x-icon name="megaphone" class="h-5 w-5" /></span>
                <span><strong>Pengumuman</strong><small>{{ $pengumuman->count() }} informasi terbaru</small></span>
                <x-icon name="chevron-right" class="h-4 w-4" />
            </a>
        </div>

        @if ($role === 'orang-tua')
            <section id="anak" class="scroll-mt-24">
                <div class="section-head">
                    <h3>Anak Saya</h3>
                    <span class="pwa-section-meta">{{ $children->count() }} anak</span>
                </div>
                <div class="pwa-card-stack">
                    @foreach ($children as $child)
                        @if ($child->id === $student?->id)
                            <article class="surface-card child-card is-active">
                                <span class="child-avatar">
                                    @if ($child->fotoUrl())
                                        <img src="{{ $child->fotoUrl() }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        {{ mb_substr($child->nama, 0, 1) }}
                                    @endif
                                </span>
                                <span class="child-copy">
                                    <strong>{{ $child->nama }}</strong>
                                    <small>{{ $rombel?->nama ?? 'Belum ada kelas' }} · NIS {{ $child->nis }}</small>
                                </span>
                                <span class="status-chip info">AKTIF</span>
                            </article>
                        @else
                            <form method="POST" action="{{ route('pwa.switch-child') }}">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $child->id }}">
                                <button type="submit" class="surface-card child-card">
                                    <span class="child-avatar">{{ mb_substr($child->nama, 0, 1) }}</span>
                                    <span class="child-copy">
                                        <strong>{{ $child->nama }}</strong>
                                        <small>NIS {{ $child->nis }}</small>
                                    </span>
                                    <x-icon name="chevron-right" class="h-4 w-4" />
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <div class="section-head">
                <h3>Jadwal Hari Ini</h3>
                <a href="{{ route('pwa.jadwal') }}">Lihat semua</a>
            </div>
            <div class="pwa-card-stack">
                @forelse ($jadwalHariIni as $schedule)
                    <a href="{{ route('pwa.jadwal') }}" class="next-class-card">
                        <span class="class-time"><small>JAM KE</small><strong>{{ $schedule->jam_ke }}</strong></span>
                        <span class="class-accent" aria-hidden="true"></span>
                        <span class="class-info">
                            @if ($loop->first)<span class="live-badge"><i></i>BERIKUTNYA</span>@endif
                            <h3>{{ $schedule->subject?->nama ?? 'Mata pelajaran' }}</h3>
                            <p>{{ $schedule->employee?->nama ?: 'Pengajar belum ditetapkan' }}{{ $schedule->ruang ? ' · '.$schedule->ruang : '' }}</p>
                        </span>
                        <x-icon name="chevron-right" class="h-4 w-4" />
                    </a>
                @empty
                    <div class="surface-card pwa-empty-state">
                        <span class="row-icon violet"><x-icon name="calendar-days" class="h-5 w-5" /></span>
                        <span><strong>Tidak ada jadwal hari ini</strong><small>Gunakan waktu untuk belajar mandiri atau beristirahat.</small></span>
                    </div>
                @endforelse
            </div>
        </section>

        <section>
            <div class="section-head">
                <h3>Pengumuman</h3>
                <a href="{{ route('pwa.pengumuman') }}">Lihat semua</a>
            </div>
            <div class="surface-card compact-list">
                @forelse ($pengumuman as $announcement)
                    <a href="{{ route('pwa.pengumuman.show', $announcement) }}" class="data-row">
                        <span class="row-icon {{ $announcement->penting ? 'amber' : 'blue' }}"><x-icon name="megaphone" class="h-5 w-5" /></span>
                        <span>
                            <strong>{{ $announcement->judul }}</strong>
                            <small>{{ $announcement->tayang_dari?->locale('id')->isoFormat('D MMMM Y') }} · {{ str(strip_tags($announcement->isi))->limit(70) }}</small>
                        </span>
                        @if ($announcement->penting)<span class="status-chip warning">PENTING</span>@endif
                    </a>
                @empty
                    <div class="pwa-empty-state pwa-empty-state-plain">
                        <span class="row-icon blue"><x-icon name="megaphone" class="h-5 w-5" /></span>
                        <span><strong>Belum ada pengumuman</strong><small>Informasi baru dari sekolah akan tampil di sini.</small></span>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
