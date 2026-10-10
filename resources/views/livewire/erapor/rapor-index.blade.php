<div>
    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label for="rombel" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Rombel</label>
                <select id="rombel" wire:model.live="rombelId"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    <option value="">— Pilih rombel —</option>
                    @foreach ($rombels as $r)
                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="semester" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Semester</label>
                <select id="semester" wire:model.live="semesterId"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    <option value="">— Pilih semester —</option>
                    @foreach ($semesters as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if ($rombelId && $semesterId)
        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                        <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">NIS</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Nama</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $s)
                        <tr class="border-t border-slate-100 dark:border-slate-700">
                            <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $s->nis }}</td>
                            <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ $s->nama }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <a href="{{ route('erapor.show', ['student' => $s->id, 'semester' => $semesterId]) }}"
                                   class="text-xs font-semibold text-primary hover:underline">Lihat Rapor</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Tidak ada siswa aktif di rombel ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-sm text-slate-500">
            Pilih rombel dan semester untuk menampilkan daftar rapor.
        </div>
    @endif
</div>
