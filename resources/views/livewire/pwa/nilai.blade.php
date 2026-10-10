<div>
    <div class="mb-4">
        <label for="semester" class="text-xs font-semibold text-slate-500">Semester</label>
        <select id="semester" wire:model.live="semester_id"
                class="mt-1 w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm outline-none focus:border-primary">
            @foreach ($semesters as $s)
                <option value="{{ $s->id }}">{{ $s->nama }}</option>
            @endforeach
        </select>
    </div>

    @if ($hasil === null)
        <p class="text-sm text-slate-400 italic">Pilih semester.</p>
    @elseif (! ($hasil['released'] ?? false))
        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 text-center">
            <i data-lucide="lock" class="w-8 h-8 mx-auto text-amber-400"></i>
            <p class="text-sm font-semibold text-amber-700 mt-2">Nilai belum dirilis</p>
            <p class="text-xs text-amber-600 mt-1">{{ $hasil['message'] ?? '' }}</p>
        </div>
    @else
        <div class="rounded-2xl bg-primary text-white p-4 mb-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-white/70">Rata-rata</p>
                <p class="text-3xl font-extrabold">{{ $hasil['rata_rata'] !== null ? number_format($hasil['rata_rata'], 1) : '—' }}</p>
            </div>
            <i data-lucide="award" class="w-10 h-10 text-white/40"></i>
        </div>

        @foreach ($hasil['mapel'] ?? [] as $m)
            <div class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 mb-1.5">
                <div class="min-w-0">
                    <p class="text-sm font-semibold truncate">{{ $m['nama'] }}</p>
                    <p class="text-[11px] text-slate-400">KKM {{ number_format($m['kkm'], 0) }}</p>
                </div>
                @if ($m['nilai_akhir'] === null)
                    <span class="text-sm font-bold text-slate-300">—</span>
                @else
                    <span class="text-lg font-extrabold {{ $m['nilai_akhir'] >= $m['kkm'] ? 'text-success' : 'text-danger' }}">
                        {{ number_format($m['nilai_akhir'], 0) }}
                    </span>
                @endif
            </div>
        @endforeach
    @endif
</div>
