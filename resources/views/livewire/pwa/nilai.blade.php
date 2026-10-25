<div class="screen app-screen screen-enter">
    <div class="field-group pwa-filter-field">
        <label for="semester">Periode nilai</label>
        <select id="semester" wire:model.live="semester_id" class="field">
            @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}">{{ ucfirst($semester->nama) }}</option>
            @endforeach
        </select>
    </div>

    <div wire:loading wire:target="semester_id" class="page-skeleton" aria-live="polite">
        <span class="sr-only">Memuat nilai semester...</span>
        <div class="skeleton skeleton-hero"></div>
        <div class="skeleton skeleton-line"></div>
        <div class="skeleton skeleton-list"></div>
        <div class="skeleton skeleton-list"></div>
    </div>

    <div wire:loading.remove wire:target="semester_id">
        @if ($hasil === null)
            <div class="surface-card pwa-empty-state pwa-empty-state-centered">
                <span class="row-icon violet"><x-icon name="book-open" class="h-5 w-5" /></span>
                <span><strong>Pilih semester</strong><small>Ringkasan dan rincian nilai akan tampil sesuai periode.</small></span>
            </div>
        @elseif (! ($hasil['released'] ?? false))
            <div class="surface-card pwa-state-card">
                <span class="row-icon amber"><x-icon name="lock" class="h-5 w-5" /></span>
                <div>
                    <strong>Nilai belum dirilis</strong>
                    <p>{{ $hasil['message'] ?? 'Sekolah belum membuka nilai pada periode ini.' }}</p>
                </div>
            </div>
        @else
            @php
                $subjects = collect($hasil['mapel'] ?? []);
                $unreleasedCount = $subjects->whereNull('nilai_akhir')->count();
                $highestScore = $subjects->pluck('nilai_akhir')->filter(fn ($score) => $score !== null)->max();
            @endphp

            <section class="academic-summary three" aria-label="Ringkasan nilai semester">
                <span><small>Rata-rata</small><strong>{{ $hasil['rata_rata'] !== null ? number_format($hasil['rata_rata'], 1) : '—' }}</strong><small>Nilai akhir</small></span>
                <i class="summary-divider" aria-hidden="true"></i>
                <span><small>Mata pelajaran</small><strong>{{ $subjects->count() }}</strong><small>Dalam rapor</small></span>
                <i class="summary-divider" aria-hidden="true"></i>
                <span><small>Nilai tertinggi</small><strong>{{ $highestScore !== null ? number_format($highestScore, 0) : '—' }}</strong><small>Pencapaian terbaik</small></span>
            </section>

            @if ($unreleasedCount > 0)
                <div class="pwa-inline-alert warning" role="status">
                    <x-icon name="lock" class="h-4 w-4" />
                    <span>{{ $unreleasedCount }} mata pelajaran masih menunggu rilis guru.</span>
                </div>
            @endif

            <section>
                <div class="section-head">
                    <h3>Rincian Nilai</h3>
                    <span class="pwa-section-meta">KKM per mapel</span>
                </div>
                <div class="surface-card grade-list">
                    @forelse ($subjects as $subject)
                        @php
                            $score = $subject['nilai_akhir'];
                            $passes = $score !== null && $score >= $subject['kkm'];
                            $progress = $score !== null ? min(100, max(0, (float) $score)) : 0;
                            $mark = str($subject['nama'])->substr(0, 2)->upper();
                        @endphp
                        <div class="grade-row" wire:key="grade-{{ $loop->index }}">
                            <span class="subject-mark">{{ $mark }}</span>
                            <span>
                                <strong>{{ $subject['nama'] }}</strong>
                                <small>KKM {{ number_format($subject['kkm'], 0) }}</small>
                                <span class="grade-progress" aria-hidden="true"><i class="{{ $score === null ? 'muted' : ($passes ? 'success' : 'danger') }}" style="width: {{ $progress }}%"></i></span>
                            </span>
                            <span class="grade-score">
                                <strong>{{ $score !== null ? number_format($score, 0) : '—' }}</strong>
                                <small class="{{ $score === null ? 'is-muted' : ($passes ? '' : 'is-danger') }}">{{ $score === null ? 'Belum rilis' : ($passes ? 'Tuntas' : 'Perlu tindak lanjut') }}</small>
                            </span>
                        </div>
                    @empty
                        <div class="pwa-empty-state pwa-empty-state-plain">
                            <span class="row-icon violet"><x-icon name="book-open" class="h-5 w-5" /></span>
                            <span><strong>Belum ada rincian nilai</strong><small>Nilai per mata pelajaran akan tampil setelah diproses sekolah.</small></span>
                        </div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
</div>
