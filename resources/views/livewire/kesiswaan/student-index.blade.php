<div>
    {{-- Toolbar: filter kiri, aksi kanan — compact (text-xs, py-2, tanpa wrap label) --}}
    <div class="mb-5 flex flex-wrap items-center gap-2">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative w-full sm:w-64">
                <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama / NIS / NISN…"
                       aria-label="Cari siswa"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2 pl-9 pr-3 text-xs outline-none focus:border-primary focus:ring-1 focus:ring-primary dark:text-white">
            </div>
            <select wire:model.live="status" aria-label="Filter status"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2 px-2.5 text-xs outline-none focus:border-primary dark:text-white">
                <option value="">Semua status</option>
                <option value="aktif">Aktif</option>
                <option value="pindah">Pindah</option>
                <option value="lulus">Lulus</option>
                <option value="keluar">Keluar</option>
            </select>
            <select wire:model.live="rombel" aria-label="Filter kelas"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2 px-2.5 text-xs outline-none focus:border-primary dark:text-white">
                <option value="">Semua kelas</option>
                @foreach ($rombels as $rombel)
                    <option value="{{ $rombel->id }}">{{ $rombel->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 sm:ml-auto">
            @can('siswa.create')
                <a href="{{ route('siswa.create') }}" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg bg-primary px-3 py-2 text-xs font-medium text-white hover:bg-blue-700 transition-colors">
                    <x-icon name="plus" class="w-3.5 h-3.5" /> Tambah Siswa
                </a>
            @endcan
            @can('siswa.import')
                <a href="{{ route('siswa.impor') }}" title="Impor data siswa dari Excel" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark px-3 py-2 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <x-icon name="upload" class="w-3.5 h-3.5" /> Impor
                </a>
            @endcan
            <details class="relative">
                <summary class="inline-flex cursor-pointer items-center gap-1 whitespace-nowrap rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark px-3 py-2 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="download" class="w-3.5 h-3.5" /> Export
                    <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
                </summary>
                <div class="absolute right-0 z-20 mt-1.5 w-44 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-boxdark">
                    <a href="{{ route('siswa.ekspor.excel', array_filter(['search' => $search ?: null, 'status' => $status ?: null, 'rombel_id' => $rombel ?: null])) }}" onclick="this.closest('details').removeAttribute('open')" class="flex items-center gap-2 px-3 py-2 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800">
                        <x-icon name="file-spreadsheet" class="w-4 h-4 text-emerald-600" /> Excel (.xlsx)
                    </a>
                    <a href="{{ route('siswa.ekspor.pdf', array_filter(['search' => $search ?: null, 'status' => $status ?: null, 'rombel_id' => $rombel ?: null])) }}" onclick="this.closest('details').removeAttribute('open')" class="flex items-center gap-2 px-3 py-2 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800">
                        <x-icon name="file-text" class="w-4 h-4 text-red-500" /> PDF
                    </a>
                </div>
            </details>
            @if ($rombel !== '')
                <a href="{{ route('siswa.kartu.rombel', ['rombel_id' => $rombel]) }}" title="Cetak kartu pelajar untuk kelas terpilih" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark px-3 py-2 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <x-icon name="id-card" class="w-3.5 h-3.5" /> Cetak Kartu
                </a>
            @else
                <button type="button" disabled title="Pilih kelas dulu untuk mencetak kartu rombel" class="inline-flex cursor-not-allowed items-center gap-1.5 whitespace-nowrap rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-medium text-slate-400 dark:text-slate-500">
                    <x-icon name="id-card" class="w-3.5 h-3.5" /> Cetak Kartu
                </button>
            @endif
            @can('siswa.delete')
                <button type="button" wire:click="toggleTrashed" title="{{ $showTrashed ? 'Kembali ke daftar siswa' : 'Lihat siswa terhapus (sampah)' }}" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border px-3 py-2 text-xs font-medium transition-colors {{ $showTrashed ? 'border-primary bg-primary/10 text-primary' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <x-icon name="trash-2" class="w-3.5 h-3.5" /> {{ $showTrashed ? 'Kembali' : 'Sampah' }}
                    @if ($trashedCount > 0 && !$showTrashed)
                        <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-danger px-1 text-[10px] font-bold leading-none text-white">{{ $trashedCount }}</span>
                    @endif
                </button>
            @endcan
        </div>
    </div>

    @if ($showTrashed)
        <div class="mb-4 rounded-xl bg-amber-500/10 border border-amber-500/20 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
            Menampilkan sampah — {{ $students->total() }} siswa terhapus. Pulihkan untuk mengembalikan, atau hapus permanen (tidak dapat dibatalkan).
        </div>
    @endif

    @if (count($selected) > 0)
        <div class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-primary/30 bg-primary/5 px-3 py-2.5">
            <span class="text-xs font-medium">{{ count($selected) }} siswa dipilih</span>
            <select wire:model="bulkAksi" aria-label="Aksi massal"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-1.5 px-2.5 text-xs outline-none focus:border-primary dark:text-white">
                <option value="">— Pilih aksi —</option>
                @if ($showTrashed)
                    <option value="pulihkan">Pulihkan</option>
                    <option value="hapus_permanen">Hapus Permanen</option>
                @else
                    <option value="status">Ubah Status</option>
                    <option value="rombel">Pindah Rombel</option>
                    <option value="export">Export Excel</option>
                    <option value="hapus">Hapus (ke Sampah)</option>
                @endif
            </select>
            @if ($bulkAksi === 'status' && !$showTrashed)
                <select wire:model="bulkStatus" aria-label="Status baru"
                        class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-1.5 px-2.5 text-xs outline-none focus:border-primary dark:text-white">
                    <option value="">— Status baru —</option>
                    <option value="aktif">Aktif</option>
                    <option value="pindah">Pindah</option>
                    <option value="lulus">Lulus</option>
                    <option value="keluar">Keluar</option>
                    <option value="do">DO</option>
                </select>
            @endif
            @if ($bulkAksi === 'rombel' && !$showTrashed)
                <select wire:model="bulkRombelId" aria-label="Rombel tujuan"
                        class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-1.5 px-2.5 text-xs outline-none focus:border-primary dark:text-white">
                    <option value="">— Rombel tujuan —</option>
                    @foreach ($rombels as $rombel)
                        <option value="{{ $rombel->id }}">{{ $rombel->nama }}</option>
                    @endforeach
                </select>
            @endif
            <button type="button" wire:click="bulkTerapkan"
                    @if (in_array($bulkAksi, ['hapus', 'hapus_permanen'], true)) wire:confirm="Yakin menjalankan aksi ini untuk {{ count($selected) }} siswa?" @endif
                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700">
                Terapkan
            </button>
            @error('bulkAksi') <p class="w-full text-xs text-danger">{{ $message }}</p> @enderror
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="py-3 px-3 w-10">
                            <input type="checkbox" wire:model.live="selectAll" aria-label="Pilih semua di halaman ini" class="rounded">
                        </th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama Siswa</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Kelas</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">NIS / NISN</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">L/P</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</th>
                        <th class="py-3 px-5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $siswa)
                        <tr wire:key="siswa-{{ $siswa->id }}" class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ $siswa->id }}" aria-label="Pilih {{ $siswa->nama }}" class="rounded">
                            </td>
                            <td class="py-3 px-5">
                                <div class="flex items-center gap-3">
                                    @php
                                        $namaParts = preg_split('/\s+/', trim($siswa->nama));
                                        $inisial = strtoupper(mb_substr($namaParts[0], 0, 1) . (count($namaParts) > 1 ? mb_substr(end($namaParts), 0, 1) : ''));
                                        $avatarColors = [
                                            'bg-primary/10 text-primary',
                                            'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                                            'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                                            'bg-sky-500/10 text-sky-600 dark:text-sky-400',
                                            'bg-violet-500/10 text-violet-600 dark:text-violet-400',
                                            'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                                        ];
                                        $avatarColor = $avatarColors[abs(crc32($siswa->nama)) % count($avatarColors)];
                                    @endphp
                                    @if ($siswa->fotoUrl())
                                        <img src="{{ $siswa->fotoUrl() }}" alt="" class="h-9 w-9 shrink-0 rounded-full object-cover">
                                    @else
                                        <div class="h-9 w-9 shrink-0 rounded-full {{ $avatarColor }} flex items-center justify-center text-xs font-bold" aria-hidden="true">{{ $inisial }}</div>
                                    @endif
                                    <div class="font-medium text-slate-800 dark:text-white">{{ $siswa->nama }}</div>
                                </div>
                            </td>
                            <td class="py-3 px-5 text-sm text-slate-600 dark:text-slate-300">
                                {{ $siswa->rombels->first()?->nama ?? '—' }}
                            </td>
                            <td class="py-3 px-5 text-sm text-slate-600 dark:text-slate-300">
                                {{ $siswa->nis }}@if($siswa->nisn)<span class="text-slate-400"> / {{ $siswa->nisn }}</span>@endif
                            </td>
                            <td class="py-3 px-5 text-sm">{{ $siswa->jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="py-3 px-5">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $siswa->status === 'aktif' ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-500' }}">
                                    {{ ucfirst($siswa->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-5 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @if ($showTrashed)
                                        @can('siswa.delete')
                                            <button type="button" wire:click="restore({{ $siswa->id }})" class="p-2 rounded-lg text-success hover:bg-success/10" aria-label="Pulihkan {{ $siswa->nama }}">
                                                <x-icon name="undo-2" class="w-4 h-4" />
                                            </button>
                                            <button type="button" wire:click="forceDelete({{ $siswa->id }})" wire:confirm="Hapus permanen {{ $siswa->nama }}? Data tidak dapat dikembalikan lagi." class="p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" aria-label="Hapus permanen {{ $siswa->nama }}">
                                                <x-icon name="trash-2" class="w-4 h-4" />
                                            </button>
                                        @endcan
                                    @else
                                        @can('siswa.view')
                                            <a href="{{ route('siswa.show', $siswa) }}" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Lihat {{ $siswa->nama }}">
                                                <x-icon name="eye" class="w-4 h-4" />
                                            </a>
                                        @endcan
                                        @can('siswa.update')
                                            <a href="{{ route('siswa.edit', $siswa) }}" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Ubah {{ $siswa->nama }}">
                                                <x-icon name="pencil" class="w-4 h-4" />
                                            </a>
                                        @endcan
                                        @can('siswa.delete')
                                            <button type="button" wire:click="delete({{ $siswa->id }})" wire:confirm="Pindah data siswa {{ $siswa->nama }} ke sampah? Data bisa dipulihkan dari Sampah." class="p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" aria-label="Hapus {{ $siswa->nama }}">
                                                <x-icon name="trash-2" class="w-4 h-4" />
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                Tidak ada data siswa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($students->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
