<div>
    @if (session('released'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('released') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-5">
        <select wire:model.live="rombelId" aria-label="Pilih kelas"
                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
            <option value="">— Kelas —</option>
            @foreach ($rombels as $r)
                <option value="{{ $r->id }}">{{ $r->nama }}</option>
            @endforeach
        </select>
        <select wire:model.live="subjectId" aria-label="Pilih mata pelajaran"
                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
            <option value="">— Mata pelajaran —</option>
            @foreach ($subjects as $s)
                <option value="{{ $s->id }}">{{ $s->nama }}</option>
            @endforeach
        </select>
        <select wire:model.live="semesterId" aria-label="Pilih semester"
                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
            @foreach ($semesters as $sem)
                <option value="{{ $sem->id }}">{{ $sem->label() }}</option>
            @endforeach
        </select>

        <div class="sm:ml-auto flex items-center gap-2">
            @if ($released)
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-success">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Sudah dirilis
                </span>
            @endif
            @can('nilai.release')
                @if ($rombelId && $semesterId && ! $released)
                    <button wire:click="release" wire:confirm="Rilis nilai kelas ini ke PWA siswa/orang tua?"
                            class="inline-flex items-center gap-2 rounded-lg bg-success px-4 py-2.5 text-sm font-medium text-white hover:opacity-90">
                        <i data-lucide="send" class="w-4 h-4"></i> Rilis Nilai
                    </button>
                @endif
            @endcan
        </div>
    </div>

    @if ($leger->isNotEmpty())
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                            <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</th>
                            <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">NIS</th>
                            <th class="py-3 px-5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leger as $row)
                            <tr class="border-b border-slate-100 dark:border-slate-700/50">
                                <td class="py-2.5 px-5 font-medium text-sm text-slate-800 dark:text-white">{{ $row['nama'] }}</td>
                                <td class="py-2.5 px-5 text-sm text-slate-500">{{ $row['nis'] }}</td>
                                <td class="py-2.5 px-5 text-right">
                                    @if ($row['nilai_akhir'] !== null)
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold
                                            {{ $row['nilai_akhir'] >= 75 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                            {{ number_format($row['nilai_akhir'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($rombelId && $subjectId)
        <p class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada data nilai.</p>
    @endif
</div>
