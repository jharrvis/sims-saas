<div class="max-w-5xl mx-auto space-y-5">
    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ $rombel->nama }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Tingkat {{ $rombel->tingkat }} · {{ $rombel->academicYear->nama }} ·
                    Wali kelas: {{ $rombel->waliKelas?->nama ?? '—' }} ·
                    Kapasitas {{ $rombel->kapasitas }}
                </p>
            </div>
            @can('kelas.update')
                <a href="{{ route('kelas.edit', $rombel) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">
                    <i data-lucide="pencil" class="w-4 h-4"></i> Ubah
                </a>
            @endcan
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Siswa ({{ $siswa->count() }})</h2>
        @if ($siswa->isEmpty())
            <p class="text-sm text-slate-500">Belum ada siswa di kelas ini.</p>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach ($siswa as $s)
                    <li class="py-2.5 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-800 dark:text-white text-sm">{{ $s->nama }}</p>
                            <p class="text-xs text-slate-500">NIS {{ $s->nis }}</p>
                        </div>
                        @can('kelas.update')
                            <button wire:click="removeStudent({{ $s->id }})" wire:confirm="Keluarkan {{ $s->nama }} dari kelas ini?"
                                    class="p-2 rounded-lg text-danger hover:bg-red-50 dark:hover:bg-red-900/20" aria-label="Keluarkan {{ $s->nama }}">
                                <i data-lucide="user-minus" class="w-4 h-4"></i>
                            </button>
                        @endcan
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @can('kelas.update')
        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
            <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Tempatkan Siswa</h2>
            <div class="relative mb-4 max-w-sm">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari siswa…"
                       aria-label="Cari siswa untuk ditempatkan"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @forelse ($calon as $c)
                    <li class="py-2.5 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-800 dark:text-white text-sm">{{ $c->nama }}</p>
                            <p class="text-xs text-slate-500">NIS {{ $c->nis }}</p>
                        </div>
                        <button wire:click="assignStudent({{ $c->id }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary/20">
                            Tempatkan
                        </button>
                    </li>
                @empty
                    <li class="py-4 text-sm text-slate-500">Ketik nama siswa untuk mencari.</li>
                @endforelse
            </ul>
        </div>
    @endcan

    <a href="{{ route('kelas.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
        ← Kembali ke daftar kelas
    </a>
</div>
