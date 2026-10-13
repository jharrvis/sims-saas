<div class="px-4 pt-4 pb-28">
    <div class="mb-4">
        <label for="semester" class="text-xs font-semibold text-slate-500">Semester</label>
        <select id="semester" wire:model.live="semester_id"
                class="mt-1 w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm outline-none focus:border-primary bg-white">
            @foreach ($semesters as $s)
                <option value="{{ $s->id }}">{{ $s->nama }}</option>
            @endforeach
        </select>
    </div>

    @if ($hasil === null)
        <p class="text-sm text-slate-400 italic">Pilih semester.</p>
    @elseif (! ($hasil['released'] ?? false))
        <div class="rounded-2xl bg-white shadow-sm border border-slate-100 p-5 text-center">
            <div class="w-12 h-12 rounded-xl bg-warning/10 flex items-center justify-center mx-auto">
                <x-icon name="lock" class="w-6 h-6 text-warning" />
            </div>
            <p class="text-sm font-semibold text-slate-700 mt-3">Nilai belum dirilis</p>
            <p class="text-xs text-slate-500 mt-1">{{ $hasil['message'] ?? '' }}</p>
        </div>
    @else
        @php
            $mapel = $hasil['mapel'] ?? [];
            $adaBelumRilis = collect($mapel)->contains(fn ($m) => $m['nilai_akhir'] === null);
            $tertinggi = collect($mapel)->map(fn ($m) => $m['nilai_akhir'])->filter(fn ($v) => $v !== null)->max();
        @endphp
        {{-- Ringkasan (design siswa/nilai.html) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5 flex items-center gap-4">
            <div class="flex-1 text-center border-r border-slate-100">
                <p class="text-2xl font-extrabold text-[#3c50e0]">{{ $hasil['rata_rata'] !== null ? number_format($hasil['rata_rata'], 1) : '—' }}</p>
                <p class="text-xs text-slate-500 font-medium">Rata-rata</p>
            </div>
            <div class="flex-1 text-center border-r border-slate-100">
                <p class="text-2xl font-extrabold text-slate-700">{{ count($mapel) }}</p>
                <p class="text-xs text-slate-500 font-medium">Mapel</p>
            </div>
            <div class="flex-1 text-center">
                <p class="text-2xl font-extrabold text-success">{{ $tertinggi !== null ? number_format($tertinggi, 0) : '—' }}</p>
                <p class="text-xs text-slate-500 font-medium">Tertinggi</p>
            </div>
        </div>

        @if ($adaBelumRilis)
            <p class="text-xs text-warning bg-warning/10 rounded-lg px-3 py-2 mb-4 flex items-center gap-2">
                <x-icon name="lock" class="w-3.5 h-3.5 shrink-0" /> Beberapa nilai belum dirilis oleh guru.
            </p>
        @endif

        <div class="space-y-3">
            @foreach ($mapel as $m)
                @php
                    $nilai = $m['nilai_akhir'];
                    $diAtasKkm = $nilai !== null && $nilai >= $m['kkm'];
                    $lebar = $nilai !== null ? min(100, (float) $nilai) : 0;
                @endphp
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
                    <div class="flex justify-between items-start mb-2 gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-800 text-sm truncate">{{ $m['nama'] }}</p>
                            <p class="text-xs text-slate-400">KKM {{ number_format($m['kkm'], 0) }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            @if ($nilai === null)
                                <p class="text-lg font-extrabold text-slate-400">—</p>
                                <p class="text-[10px] text-slate-400">Belum dirilis</p>
                            @elseif ($diAtasKkm)
                                <p class="text-lg font-extrabold text-success">{{ number_format($nilai, 0) }}</p>
                                <p class="text-[10px] text-success">Di atas KKM</p>
                            @else
                                <p class="text-lg font-extrabold text-danger">{{ number_format($nilai, 0) }}</p>
                                <p class="text-[10px] text-danger">Di bawah KKM</p>
                            @endif
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="{{ $nilai === null ? 'bg-slate-300' : ($diAtasKkm ? 'bg-success' : 'bg-danger') }} h-1.5 rounded-full" style="width: {{ $lebar }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
