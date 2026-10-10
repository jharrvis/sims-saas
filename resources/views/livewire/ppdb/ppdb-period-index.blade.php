<div>
    <div class="flex justify-end mb-5">
        @can('ppdb.periode.create')
            <a href="{{ route('ppdb.periode.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i> Buka Periode Baru
            </a>
        @endcan
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($periods as $p)
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white">{{ $p->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $p->academicYear->nama }}</p>
                    </div>
                    @if ($p->isOpen())
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-success/10 text-success">Dibuka</span>
                    @else
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-slate-500/10 text-slate-500">Ditutup</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-3">
                    {{ $p->tgl_buka->format('d M Y') }} – {{ $p->tgl_tutup->format('d M Y') }} ·
                    {{ $p->applicants_count }} pendaftar
                    @if ($p->kuota > 0) · Kuota {{ $p->kuota }} @endif
                </p>
                <a href="{{ route('ppdb.pendaftar.index', ['period' => $p->id]) }}"
                   class="block mt-4 py-2 text-center text-xs font-medium rounded-lg bg-primary/10 text-primary hover:bg-primary/20">
                    Kelola Pendaftar
                </a>
            </div>
        @empty
            <p class="col-span-full py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                Belum ada periode PPDB.
            </p>
        @endforelse
    </div>

    @if ($periods->hasPages())
        <div class="mt-4">{{ $periods->links() }}</div>
    @endif
</div>
