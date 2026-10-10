<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari kode / nama…"
                       aria-label="Cari aset"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <select wire:model.live="kategori" aria-label="Filter kategori"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->nama }}</option>
                @endforeach
            </select>
            <select wire:model.live="kondisi" aria-label="Filter kondisi"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua kondisi</option>
                @foreach (\App\Models\Asset::KONDISI as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @can('aset.create')
            <a href="{{ route('aset.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Aset
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Kode</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Nama</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Kategori</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Lokasi</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Kondisi</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $item->kode }}</td>
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ $item->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->category->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->lokasi ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $item->kondisi === 'baik' ? 'bg-success/10 text-success' : ($item->kondisi === 'hilang' ? 'bg-danger/10 text-danger' : 'bg-warning/15 text-amber-600') }}">
                                {{ \App\Models\Asset::KONDISI[$item->kondisi] }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            <a href="{{ route('aset.show', $item) }}" class="text-xs font-semibold text-primary hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada aset.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
