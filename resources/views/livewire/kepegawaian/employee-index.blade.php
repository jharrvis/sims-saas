<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="relative w-full sm:max-w-xs">
            <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama / NIP…"
                   aria-label="Cari guru"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary dark:text-white">
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="jenis" aria-label="Filter jenis"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua jenis</option>
                <option value="guru_mapel">Guru Mapel</option>
                <option value="guru_kelas">Guru Kelas</option>
                <option value="tendik">Tendik</option>
            </select>
            @can('guru.create')
                <a href="{{ route('guru.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    <x-icon name="plus" class="w-4 h-4" /> Tambah Guru
                </a>
            @endcan
            @can('guru.import')
                @if (\Illuminate\Support\Facades\Route::has('guru.impor'))
                    <a href="{{ route('guru.impor') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <x-icon name="upload" class="w-4 h-4" /> Impor Excel
                    </a>
                @endif
            @endcan
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama Lengkap</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">NIP / NUPTK</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Jenis</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</th>
                        <th class="py-3 px-5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $guru)
                        <tr wire:key="guru-{{ $guru->id }}" class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-5">
                                <div class="flex items-center gap-3">
                                    @php
                                        $namaParts = preg_split('/\s+/', trim($guru->nama));
                                        $inisial = strtoupper(mb_substr($namaParts[0], 0, 1) . (count($namaParts) > 1 ? mb_substr(end($namaParts), 0, 1) : ''));
                                        $avatarColors = [
                                            'bg-primary/10 text-primary',
                                            'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                                            'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                                            'bg-sky-500/10 text-sky-600 dark:text-sky-400',
                                            'bg-violet-500/10 text-violet-600 dark:text-violet-400',
                                            'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                                        ];
                                        $avatarColor = $avatarColors[abs(crc32($guru->nama)) % count($avatarColors)];
                                    @endphp
                                    @if ($guru->fotoUrl())
                                        <img src="{{ $guru->fotoUrl() }}" alt="" class="h-9 w-9 shrink-0 rounded-full object-cover">
                                    @else
                                        <div class="h-9 w-9 shrink-0 rounded-full {{ $avatarColor }} flex items-center justify-center text-xs font-bold" aria-hidden="true">{{ $inisial }}</div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-slate-800 dark:text-white">{{ $guru->nama }}</div>
                                        @if($guru->jabatan)<div class="text-xs text-slate-500">{{ $guru->jabatan }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-5 text-sm text-slate-600 dark:text-slate-300">{{ $guru->nip_nuptk ?? '—' }}</td>
                            <td class="py-3 px-5 text-sm">{{ $guru->labelJenis() }}</td>
                            <td class="py-3 px-5">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $guru->status_aktif ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-500' }}">
                                    {{ $guru->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3 px-5 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @can('guru.view')
                                        <a href="{{ route('guru.show', $guru) }}" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Lihat {{ $guru->nama }}">
                                            <x-icon name="eye" class="w-4 h-4" />
                                        </a>
                                    @endcan
                                    @can('guru.update')
                                        <a href="{{ route('guru.edit', $guru) }}" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Ubah {{ $guru->nama }}">
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </a>
                                    @endcan
                                    @can('guru.delete')
                                        <button type="button" wire:click="delete({{ $guru->id }})" wire:confirm="Hapus data {{ $guru->nama }}? Data yang dihapus tidak dapat dikembalikan lagi." class="p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" aria-label="Hapus {{ $guru->nama }}">
                                            <x-icon name="trash-2" class="w-4 h-4" />
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                Tidak ada data guru/pegawai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($employees->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
