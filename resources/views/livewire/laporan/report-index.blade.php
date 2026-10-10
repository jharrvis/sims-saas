<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="type" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Jenis Laporan</label>
                <select id="type" wire:model.live="type"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if (in_array($type, ['siswa', 'guru']))
                <div>
                    <label for="q" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Cari nama</label>
                    <input id="q" type="search" wire:model="q" placeholder="Nama…"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
            @endif

            @if ($type === 'siswa')
                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Status</label>
                    <select id="status" wire:model="status"
                            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                        <option value="">Semua</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                        <option value="lulus">Lulus</option>
                        <option value="pindah">Pindah</option>
                    </select>
                </div>
            @endif

            @if (in_array($type, ['mutasi', 'absensi']))
                <div>
                    <label for="dari" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Dari tanggal</label>
                    <input id="dari" type="date" wire:model="dari"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label for="sampai" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Sampai tanggal</label>
                    <input id="sampai" type="date" wire:model="sampai"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
            @endif

            @if ($type === 'nilai')
                <div>
                    <label for="semester" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Semester</label>
                    <select id="semester" wire:model="semesterId"
                            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                        <option value="">Semua</option>
                        @foreach ($semesters as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3 mt-4">
            <button wire:click="generate" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Tampilkan
            </button>
            @can('laporan.export')
                <button wire:click="export" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary">
                    <i data-lucide="download" class="w-4 h-4"></i> Ekspor CSV
                </button>
            @endcan
            <a href="{{ route('bantuan.show', 'laporan') }}" class="ml-auto text-xs text-slate-400 hover:text-primary inline-flex items-center gap-1">
                <i data-lucide="circle-help" class="w-3.5 h-3.5"></i> Bantuan
            </a>
        </div>
    </div>

    @if ($report)
        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                            @foreach ($report['columns'] as $col)
                                <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['rows'] as $row)
                            <tr class="border-t border-slate-100 dark:border-slate-700">
                                @foreach ($row as $cell)
                                    <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($report['columns']) }}" class="px-4 py-8 text-center text-slate-500">Tidak ada data untuk filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-sm text-slate-500">
            Pilih jenis laporan lalu klik <strong>Tampilkan</strong>.
        </div>
    @endif
</div>
