<div class="max-w-4xl">
    @include('livewire.pengaturan._tabs')
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    @can('tahun-ajaran.create')
        <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
            <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Tambah Tahun Ajaran</h2>
            <div class="grid sm:grid-cols-4 gap-4">
                <div>
                    <label for="ta-nama" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama</label>
                    <input id="ta-nama" type="text" wire:model="nama" placeholder="2026/2027" maxlength="50"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ta-mulai" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Mulai</label>
                    <input id="ta-mulai" type="date" wire:model="tgl_mulai"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    @error('tgl_mulai') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ta-selesai" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Selesai</label>
                    <input id="ta-selesai" type="date" wire:model="tgl_selesai"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    @error('tgl_selesai') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">Tambah</button>
                </div>
            </div>
        </form>
    @endcan

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Tahun Ajaran</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Periode</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ $item->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->tgl_mulai }} – {{ $item->tgl_selesai }}</td>
                        <td class="px-4 py-2.5">
                            @if ($item->aktif)
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-success/10 text-success">AKTIF</span>
                            @else
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            @can('tahun-ajaran.activate')
                                @if (! $item->aktif)
                                    <button wire:click="activate({{ $item->id }})" class="text-xs font-semibold text-primary hover:underline">Aktifkan</button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada tahun ajaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
