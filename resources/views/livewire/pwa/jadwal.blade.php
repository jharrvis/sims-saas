<div class="screen app-screen screen-enter">
    @php
        $scheduledDays = collect($haris)->filter(fn ($day) => $items->has($day))->values();
        $today = strtolower(now()->locale('id')->dayName);
        $activeDay = $scheduledDays->contains($today) ? $today : $scheduledDays->first();
    @endphp

    <div class="page-title">
        <span class="overline">JADWAL MINGGUAN</span>
        <h1>{{ $rombel?->nama ?? 'Jadwal pelajaran' }}</h1>
        <p>{{ $rombel ? 'Pilih hari untuk melihat urutan mata pelajaran.' : 'Kelas aktif belum ditetapkan oleh sekolah.' }}</p>
    </div>

    @if ($scheduledDays->isNotEmpty())
        <div x-data="{ activeDay: @js($activeDay) }">
            <div class="day-tabs" role="tablist" aria-label="Pilih hari jadwal">
                @foreach ($scheduledDays as $day)
                    <button type="button"
                            class="day-tab"
                            :class="activeDay === @js($day) ? 'active' : ''"
                            :aria-selected="activeDay === @js($day)"
                            @click="activeDay = @js($day)"
                            role="tab">
                        {{ ucfirst($day) }}
                    </button>
                @endforeach
            </div>

            @foreach ($scheduledDays as $day)
                <section x-show="activeDay === @js($day)" x-cloak role="tabpanel" aria-label="Jadwal {{ ucfirst($day) }}">
                    <div class="section-head">
                        <h3>{{ ucfirst($day) }}</h3>
                        <span class="pwa-section-meta">{{ $items[$day]->count() }} pelajaran</span>
                    </div>
                    <div class="pwa-card-stack">
                        @foreach ($items[$day] as $schedule)
                            <article class="next-class-card schedule-card" wire:key="schedule-{{ $schedule->id }}">
                                <span class="class-time"><small>JAM KE</small><strong>{{ $schedule->jam_ke }}</strong></span>
                                <span class="class-accent" aria-hidden="true"></span>
                                <span class="class-info">
                                    <span class="schedule-code">{{ $schedule->subject?->kode ?: 'MAPEL' }}</span>
                                    <h3>{{ $schedule->subject?->nama ?? 'Mata pelajaran' }}</h3>
                                    <p>{{ $schedule->employee?->nama ?: 'Pengajar belum ditetapkan' }}{{ $schedule->ruang ? ' · '.$schedule->ruang : '' }}</p>
                                </span>
                                <span class="schedule-status" aria-label="Terjadwal"><x-icon name="calendar-days" class="h-4 w-4" /></span>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="surface-card pwa-state-card">
            <span class="row-icon violet"><x-icon name="calendar-days" class="h-5 w-5" /></span>
            <div>
                <strong>Belum ada jadwal</strong>
                <p>Jadwal mingguan akan tampil setelah sekolah menetapkan kelas dan mata pelajaran.</p>
            </div>
        </div>
    @endif
</div>
