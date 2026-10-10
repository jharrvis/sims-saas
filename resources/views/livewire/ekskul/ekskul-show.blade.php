<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('ekskul.index') }}" class="text-sm text-slate-500 hover:text-primary">← Semua Ekstrakurikuler</a>
        @can('ekskul.update')
            <a href="{{ route('ekskul.edit', $ekskul) }}" class="text-sm font-semibold text-primary hover:underline">Ubah Ekstrakurikuler</a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $ekskul->nama }}</h2>
        <p class="text-sm text-slate-500 mt-1">
            @if ($ekskul->jadwal) Jadwal: {{ $ekskul->jadwal }} · @endif
            @if ($ekskul->pembina) Pembina: {{ $ekskul->pembina->nama }} @endif
        </p>
    </div>

    @can('ekskul.update')
        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
            <h3 class="font-semibold text-slate-800 dark:text-white mb-3">Tambah Anggota</h3>
            <input type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari siswa…"
                   aria-label="Cari siswa"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white mb-3">
            @forelse ($calon as $s)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                    <span class="text-sm text-slate-700 dark:text-slate-200">{{ $s->nama }} <span class="text-slate-400">· {{ $s->nis }}</span></span>
                    <button wire:click="tambahAnggota({{ $s->id }})" class="text-xs font-semibold text-primary hover:underline">Tambah</button>
                </div>
            @empty
                <p class="text-sm text-slate-500">Tidak ada siswa yang cocok.</p>
            @endforelse
        </div>
    @endcan

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">NIS</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Nama</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Bergabung</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($anggota as $a)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $a->nis }}</td>
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ $a->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $a->pivot->tanggal_gabung ? \Carbon\Carbon::parse($a->pivot->tanggal_gabung)->format('d M Y') : '—' }}</td>
                        <td class="px-4 py-2.5 text-right">
                            @can('ekskul.update')
                                <button wire:click="keluarkanAnggota({{ $a->id }})" class="text-xs font-semibold text-danger hover:underline">Keluarkan</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada anggota.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
