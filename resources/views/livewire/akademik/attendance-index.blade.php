<div>
    @if (session('saved'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('saved') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-5">
        <select wire:model.live="rombelId" aria-label="Pilih kelas"
                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
            <option value="">— Pilih kelas —</option>
            @foreach ($rombels as $r)
                <option value="{{ $r->id }}">{{ $r->nama }}</option>
            @endforeach
        </select>
        <input type="date" wire:model.live="tanggal" aria-label="Tanggal"
               class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
        @if ($locked)
            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                <i data-lucide="lock" class="w-3.5 h-3.5"></i> Absensi terkunci
            </span>
        @endif
    </div>

    @if ($rombelId && $students->isNotEmpty())
        @can('absensi.create')
            @if (! $locked)
                <div class="flex gap-2 mb-4">
                    @foreach (['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alpa'] as $kode => $label)
                        <button wire:click="markAll('{{ $kode }}')" class="px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">
                            Semua {{ $label }}
                        </button>
                    @endforeach
                </div>
            @endif
        @endcan

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                            <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</th>
                            <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $s)
                            <tr class="border-b border-slate-100 dark:border-slate-700/50">
                                <td class="py-2.5 px-5">
                                    <div class="font-medium text-sm text-slate-800 dark:text-white">{{ $s->nama }}</div>
                                    <div class="text-xs text-slate-500">NIS {{ $s->nis }}</div>
                                </td>
                                <td class="py-2.5 px-5">
                                    <div class="flex gap-1" role="radiogroup" aria-label="Status {{ $s->nama }}">
                                        @foreach (['H', 'I', 'S', 'A'] as $kode)
                                            <button wire:click="$set('marks.{{ $s->id }}', '{{ $kode }}')" @disabled($locked)
                                                    aria-pressed="{{ ($marks[$s->id] ?? null) === $kode }}"
                                                    class="w-9 h-9 rounded-lg text-xs font-bold border transition-colors
                                                        {{ ($marks[$s->id] ?? null) === $kode
                                                            ? ($kode === 'H' ? 'bg-success text-white border-success' : ($kode === 'A' ? 'bg-danger text-white border-danger' : 'bg-warning text-white border-warning'))
                                                            : 'border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                                                {{ $kode }}
                                            </button>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-2.5 px-5">
                                    <input type="text" wire:model="notes.{{ $s->id }}" @disabled($locked) placeholder="—"
                                           aria-label="Keterangan {{ $s->nama }}"
                                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-1.5 px-3 text-xs outline-none focus:border-primary dark:text-white disabled:opacity-50">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @can('absensi.create')
            @if (! $locked)
                <div class="flex justify-end gap-3 mt-4">
                    @can('absensi.kunci')
                        <button wire:click="lock" wire:confirm="Kunci absensi tanggal ini? Data yang terkunci tidak bisa diubah."
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">
                            <i data-lucide="lock" class="w-4 h-4"></i> Kunci
                        </button>
                    @endcan
                    <button wire:click="save" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                        Simpan Absensi
                    </button>
                </div>
            @endif
        @endcan
    @elseif ($rombelId)
        <p class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">Tidak ada siswa aktif di kelas ini.</p>
    @endif
</div>
