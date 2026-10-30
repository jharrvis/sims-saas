<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('ppdb.pendaftar.index', ['period' => $applicant->ppdb_period_id]) }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Daftar Pendaftar
            </a>
            <span class="eyebrow">PPDB / Pendaftar</span>
            <h2 class="management-page-title">{{ $applicant->nama }}</h2>
            <p>
                {{ $applicant->no_pendaftaran }} · {{ $applicant->period->nama }} ·
                {{ $applicant->jk === 'L' ? 'Laki-laki' : 'Perempuan' }} ·
                Asal: {{ $applicant->asal_sekolah ?? '—' }}
                @if ($applicant->nama_ortu)
                    · Ortu: {{ $applicant->nama_ortu }} ({{ $applicant->no_hp_ortu ?? '—' }})
                @endif
                <span class="badge badge-blue">{{ $applicant->labelStatus() }}</span>
            </p>
        </div>
    </div>

    @if (session('saved'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('saved') }}</span>
        </div>
    @endif
    @if (session('enrolled'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('enrolled') }}</span>
        </div>
    @endif

    @if ($applicant->selection)
        <section class="card" aria-labelledby="ppdb-seleksi-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="clipboard-list" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="ppdb-seleksi-title">Hasil Seleksi</h3>
                        <p>Nilai tes, wawancara, dan nilai akhir.</p>
                    </div>
                </div>
            </div>
            <div class="info-grid">
                <div class="info-tile">
                    <small>Nilai Tes</small>
                    <strong>{{ $applicant->selection->nilai_tes ?? '—' }}</strong>
                </div>
                <div class="info-tile">
                    <small>Nilai Wawancara</small>
                    <strong>{{ $applicant->selection->nilai_wawancara ?? '—' }}</strong>
                </div>
                <div class="info-tile">
                    <small>Nilai Akhir</small>
                    <strong>{{ $applicant->selection->nilai_akhir ?? '—' }}</strong>
                </div>
            </div>
        </section>
    @endif

    <section class="card management-form-card" aria-labelledby="ppdb-proses-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon green"><x-icon name="award" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="ppdb-proses-title">Proses Pendaftaran</h3>
                    <p>Tombol yang tampil mengikuti status pendaftar saat ini.</p>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="catatan">Catatan</label>
            <textarea id="catatan" wire:model="catatan" rows="2" class="textarea-field"></textarea>
            @error('catatan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-wrap gap-2 mt-5">
            @if ($applicant->status === 'draft')
                @can('ppdb.pendaftar.update')
                    <button wire:click="submit" class="btn btn-primary">Kirim Formulir</button>
                @endcan
            @endif

            @if ($applicant->status === 'submitted')
                @can('ppdb.pendaftar.verify')
                    <button wire:click="verify" class="btn btn-success">Verifikasi Berkas</button>
                @endcan
            @endif

            @if ($applicant->status === 'verified')
                @can('ppdb.seleksi.input')
                    <div class="flex items-end gap-2 mr-2">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Nilai Tes</label>
                            <input type="number" wire:model="nilai_tes" min="0" max="100" step="0.01" class="field w-24">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Wawancara</label>
                            <input type="number" wire:model="nilai_wawancara" min="0" max="100" step="0.01" class="field w-24">
                        </div>
                        <button wire:click="saveSelection" class="btn btn-outline">Simpan</button>
                    </div>
                    <button wire:click="accept" data-sims-confirm="Terima pendaftar ini?" class="btn btn-success">Terima</button>
                    <button wire:click="reject" data-sims-confirm="Tolak pendaftar ini?" class="btn btn-danger">Tolak</button>
                @endcan
            @endif

            @if ($applicant->status === 'accepted')
                @can('ppdb.pendaftar.enroll')
                    <button wire:click="enroll" data-sims-confirm="Proses daftar ulang? Pendaftar akan menjadi data siswa."
                            class="btn btn-primary">
                        Daftar Ulang → Jadi Siswa
                    </button>
                @endcan
            @endif
        </div>
    </section>
</div>
