<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    {{-- Rekap poin tertinggi --}}
    @if ($rekap->isNotEmpty())
        <div class="rounded-xl border border-warning/30 bg-amber-50 dark:bg-amber-900/10 p-4 mb-5">
            <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Poin Tertinggi</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($rekap as $r)
                    <span class="text-xs px-2.5 py-1 rounded-full bg-white dark:bg-boxdark border border-slate-200 dark:border-slate-700">
                        <strong>{{ $r->nama }}</strong> · {{ $r->total_poin }} poin ({{ $r->jumlah }}×)
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari siswa…"
                       aria-label="Cari pelanggaran"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <select wire:model.live="kategori" aria-label="Filter kategori"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua kategori</option>
                @foreach (\App\Models\ViolationType::KATEGORI as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            @can('tatib.create')
                <a href="{{ route('tatib.jenis') }}" class="text-xs font-semibold text-slate-500 hover:text-primary whitespace-nowrap">Kelola Jenis</a>
            @endcan
        </div>
        @can('tatib.create')
            <a href="{{ route('tatib.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Catat Pelanggaran
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Tanggal</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Siswa</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Pelanggaran</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Poin</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $item->tanggal->format('d M Y') }}</td>
                        <td class="px-4 py-2.5">
                            <span class="font-semibold text-slate-800 dark:text-white">{{ $item->student->nama }}</span>
                            <span class="text-xs text-slate-500">· {{ $item->student->nis }}</span>
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="text-slate-700 dark:text-slate-300">{{ $item->type->nama }}</span>
                            <span class="ml-1 text-[11px] font-semibold px-1.5 py-0.5 rounded {{ $item->type->kategori === 'berat' ? 'bg-danger/10 text-danger' : ($item->type->kategori === 'sedang' ? 'bg-warning/15 text-amber-600' : 'bg-slate-100 text-slate-500 dark:bg-slate-800') }}">
                                {{ \App\Models\ViolationType::KATEGORI[$item->type->kategori] }}
                            </span>
                            @if ($item->keterangan)
                                <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($item->keterangan, 80) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 font-bold text-danger">+{{ $item->type->poin }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            @can('tatib.update')
                                <a href="{{ route('tatib.edit', $item) }}" class="text-xs font-semibold text-primary hover:underline">Ubah</a>
                            @endcan
                            @can('tatib.delete')
                                <button wire:click="destroy({{ $item->id }})" wire:confirm="Hapus catatan ini?" class="ml-2 text-xs font-semibold text-danger hover:underline">Hapus</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada catatan pelanggaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
