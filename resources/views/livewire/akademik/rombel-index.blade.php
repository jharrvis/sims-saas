<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <select wire:model.live="academicYearId" aria-label="Tahun ajaran"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                @foreach ($tahunAjaran as $ta)
                    <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                @endforeach
            </select>
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari kelas…"
                       aria-label="Cari kelas"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary dark:text-white">
            </div>
        </div>
        @can('kelas.create')
            <a href="{{ route('kelas.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kelas
            </a>
        @endcan
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($rombels as $rombel)
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white">{{ $rombel->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tingkat {{ $rombel->tingkat }} · {{ $rombel->academicYear->nama }}</p>
                    </div>
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                        {{ $rombel->siswa_count }}/{{ $rombel->kapasitas }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-3 flex items-center gap-1.5">
                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                    Wali kelas: {{ $rombel->waliKelas?->nama ?? '—' }}
                </p>
                <div class="flex gap-2 mt-4">
                    <a href="{{ route('kelas.show', $rombel) }}" class="flex-1 py-2 text-center text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">Lihat Siswa</a>
                    @can('kelas.update')
                        <a href="{{ route('kelas.edit', $rombel) }}" class="flex-1 py-2 text-center text-xs font-medium rounded-lg bg-primary/10 text-primary hover:bg-primary/20">Ubah</a>
                    @endcan
                </div>
            </div>
        @empty
            <p class="col-span-full py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                Tidak ada data kelas.
            </p>
        @endforelse
    </div>

    @if ($rombels->hasPages())
        <div class="mt-4">{{ $rombels->links() }}</div>
    @endif
</div>
