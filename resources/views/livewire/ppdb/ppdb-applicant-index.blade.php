<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <a href="{{ route('ppdb.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← PPDB</a>
            <h1 class="text-lg font-bold text-slate-800 dark:text-white">{{ $period->nama }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama / no. daftar…"
                       aria-label="Cari pendaftar"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <select wire:model.live="status" aria-label="Filter status"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua status</option>
                @foreach (\App\Models\PpdbApplicant::STATUS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            @can('ppdb.pendaftar.create')
                <a href="{{ route('ppdb.pendaftar.create', ['period' => $period->id]) }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                    <i data-lucide="plus" class="w-4 h-4"></i> Daftarkan
                </a>
            @endcan
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">No. Pendaftaran</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Asal Sekolah</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="py-3 px-5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applicants as $a)
                        <tr class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-5 font-mono text-xs">{{ $a->no_pendaftaran }}</td>
                            <td class="py-3 px-5 font-medium text-sm text-slate-800 dark:text-white">{{ $a->nama }}</td>
                            <td class="py-3 px-5 text-sm text-slate-500">{{ $a->asal_sekolah ?? '—' }}</td>
                            <td class="py-3 px-5">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                    {{ $a->labelStatus() }}
                                </span>
                            </td>
                            <td class="py-3 px-5 text-right">
                                <a href="{{ route('ppdb.pendaftar.show', $a) }}" class="inline-flex px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary/20">
                                    Proses
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-sm text-slate-500">Tidak ada pendaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($applicants->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700">{{ $applicants->links() }}</div>
        @endif
    </div>
</div>
