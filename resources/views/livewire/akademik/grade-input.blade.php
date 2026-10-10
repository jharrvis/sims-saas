<div>
    @if (session('saved'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('saved') }}</div>
    @endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
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
        <select wire:model.live="componentId" aria-label="Pilih komponen"
                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
            <option value="">— Komponen —</option>
            @foreach ($components as $c)
                <option value="{{ $c->id }}">{{ $c->nama }} ({{ $c->bobot }}%)</option>
            @endforeach
        </select>
        <select wire:model.live="semesterId" aria-label="Pilih semester"
                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
            @foreach ($semesters as $sem)
                <option value="{{ $sem->id }}">{{ $sem->label() }}</option>
            @endforeach
        </select>
    </div>

    @if ($rombelId && $subjectId && $componentId && $students->isNotEmpty())
        <form wire:submit="save">
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                                <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</th>
                                <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-40">Nilai (0–100)</th>
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
                                        <input type="number" wire:model="scores.{{ $s->id }}" min="0" max="100" step="0.01"
                                               aria-label="Nilai {{ $s->nama }}" placeholder="—"
                                               class="w-28 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-1.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                                        @error("scores.{$s->id}") <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                    Simpan Nilai
                </button>
            </div>
        </form>
    @elseif ($rombelId && $subjectId)
        <p class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
            Pilih komponen penilaian atau tidak ada siswa aktif di kelas ini.
        </p>
    @endif
</div>
