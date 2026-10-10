<div class="max-w-4xl mx-auto space-y-5">
    @if (session('saved'))
        <div class="rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('saved') }}</div>
    @endif
    @if (session('enrolled'))
        <div class="rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('enrolled') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-mono text-xs text-slate-500">{{ $applicant->no_pendaftaran }}</p>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white mt-1">{{ $applicant->nama }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $applicant->period->nama }} ·
                    {{ $applicant->jk === 'L' ? 'Laki-laki' : 'Perempuan' }} ·
                    Asal: {{ $applicant->asal_sekolah ?? '—' }}
                </p>
                @if ($applicant->nama_ortu)
                    <p class="text-sm text-slate-500">Ortu: {{ $applicant->nama_ortu }} ({{ $applicant->no_hp_ortu ?? '—' }})</p>
                @endif
            </div>
            <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-primary/10 text-primary">
                {{ $applicant->labelStatus() }}
            </span>
        </div>
    </div>

    @if ($applicant->selection)
        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
            <h2 class="font-semibold text-slate-800 dark:text-white mb-3">Hasil Seleksi</h2>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-slate-500">Nilai Tes</p>
                    <p class="font-bold">{{ $applicant->selection->nilai_tes ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Nilai Wawancara</p>
                    <p class="font-bold">{{ $applicant->selection->nilai_wawancara ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Nilai Akhir</p>
                    <p class="font-bold">{{ $applicant->selection->nilai_akhir ?? '—' }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Proses Pendaftaran</h2>

        <div class="mb-4">
            <label for="catatan" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Catatan</label>
            <textarea id="catatan" wire:model="catatan" rows="2"
                      class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white"></textarea>
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($applicant->status === 'draft')
                @can('ppdb.pendaftar.update')
                    <button wire:click="submit" class="px-4 py-2 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700">Kirim Formulir</button>
                @endcan
            @endif

            @if ($applicant->status === 'submitted')
                @can('ppdb.pendaftar.verify')
                    <button wire:click="verify" class="px-4 py-2 rounded-lg bg-success text-sm font-medium text-white hover:opacity-90">Verifikasi Berkas</button>
                @endcan
            @endif

            @if ($applicant->status === 'verified')
                @can('ppdb.seleksi.input')
                    <div class="flex items-end gap-2 mr-2">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Nilai Tes</label>
                            <input type="number" wire:model="nilai_tes" min="0" max="100" step="0.01" class="w-24 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2 px-3 text-sm dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Wawancara</label>
                            <input type="number" wire:model="nilai_wawancara" min="0" max="100" step="0.01" class="w-24 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2 px-3 text-sm dark:text-white">
                        </div>
                        <button wire:click="saveSelection" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Simpan</button>
                    </div>
                    <button wire:click="accept" wire:confirm="Terima pendaftar ini?" class="px-4 py-2 rounded-lg bg-success text-sm font-medium text-white hover:opacity-90">Terima</button>
                    <button wire:click="reject" wire:confirm="Tolak pendaftar ini?" class="px-4 py-2 rounded-lg bg-danger text-sm font-medium text-white hover:opacity-90">Tolak</button>
                @endcan
            @endif

            @if ($applicant->status === 'accepted')
                @can('ppdb.pendaftar.enroll')
                    <button wire:click="enroll" wire:confirm="Proses daftar ulang? Pendaftar akan menjadi data siswa."
                            class="px-4 py-2 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700">
                        Daftar Ulang → Jadi Siswa
                    </button>
                @endcan
            @endif
        </div>
    </div>

    <a href="{{ route('ppdb.pendaftar.index', ['period' => $applicant->ppdb_period_id]) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
        ← Kembali ke daftar pendaftar
    </a>
</div>
