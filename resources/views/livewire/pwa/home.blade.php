<div>
    @if ($role === 'orang-tua' && $children->count() > 1)
        <p class="text-xs text-slate-400 mb-3">Menampilkan data untuk:</p>
    @endif

    @if ($student)
        <div class="rounded-2xl bg-primary text-white p-4 mb-4">
            <p class="text-[11px] uppercase tracking-wide text-white/70">{{ $rombel?->nama ?? 'Belum ada kelas' }}</p>
            <h2 class="text-xl font-bold">{{ $student->nama }}</h2>
            <p class="text-xs text-white/70 mt-0.5">NIS {{ $student->nis }}</p>
        </div>
    @endif

    @if ($role === 'orang-tua')
        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2">Anak Saya</h3>
            <div class="space-y-2">
                @foreach ($children as $child)
                    <form method="POST" action="{{ route('pwa.switch-child') }}">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $child->id }}">
                        <button class="w-full flex items-center justify-between rounded-xl border px-4 py-3 text-left {{ $child->id === $student?->id ? 'border-primary bg-primary/5' : 'border-slate-200' }}">
                            <span>
                                <span class="block font-semibold text-sm">{{ $child->nama }}</span>
                                <span class="text-xs text-slate-400">NIS {{ $child->nis }}</span>
                            </span>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-3 gap-2 mb-4">
        <div class="rounded-xl bg-slate-50 p-3 text-center">
            <p class="text-lg font-extrabold text-success">{{ $rekapAbsensi['H'] ?? 0 }}</p>
            <p class="text-[10px] text-slate-500 font-semibold">Hadir bln ini</p>
        </div>
        <div class="rounded-xl bg-slate-50 p-3 text-center">
            <p class="text-lg font-extrabold text-warning">{{ ($rekapAbsensi['I'] ?? 0) + ($rekapAbsensi['S'] ?? 0) }}</p>
            <p class="text-[10px] text-slate-500 font-semibold">Izin/Sakit</p>
        </div>
        <a href="{{ route('pwa.tagihan') }}" class="rounded-xl bg-slate-50 p-3 text-center">
            <p class="text-lg font-extrabold {{ $tagihanAktif > 0 ? 'text-danger' : 'text-slate-400' }}">{{ $tagihanAktif }}</p>
            <p class="text-[10px] text-slate-500 font-semibold">Tagihan aktif</p>
        </a>
    </div>

    <div class="mb-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-bold text-sm">Jadwal Hari Ini <span class="font-normal text-slate-400">({{ $hariIni }})</span></h3>
            <a href="{{ route('pwa.jadwal') }}" class="text-xs font-semibold text-primary">Semua →</a>
        </div>
        @forelse ($jadwalHariIni as $j)
            <div class="flex items-center gap-3 rounded-xl border border-slate-100 px-3 py-2.5 mb-1.5">
                <span class="text-[10px] font-bold text-primary bg-primary/10 rounded-lg px-2 py-1 whitespace-nowrap">Jam {{ $j->jam_ke }}</span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold truncate">{{ $j->subject?->nama ?? '—' }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ $j->employee?->nama }}{{ $j->ruang ? ' · '.$j->ruang : '' }}</p>
                </div>
            </div>
        @empty
            <p class="text-xs text-slate-400 italic">Tidak ada jadwal hari ini.</p>
        @endforelse
    </div>

    <div>
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-bold text-sm">Pengumuman</h3>
            <a href="{{ route('pwa.pengumuman') }}" class="text-xs font-semibold text-primary">Semua →</a>
        </div>
        @forelse ($pengumuman as $p)
            <a href="{{ route('pwa.pengumuman.show', $p) }}" class="block rounded-xl border border-slate-100 px-3 py-2.5 mb-1.5">
                <p class="text-sm font-semibold leading-snug">{{ $p->judul }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $p->tayang_dari?->format('d M Y') }}</p>
            </a>
        @empty
            <p class="text-xs text-slate-400 italic">Belum ada pengumuman.</p>
        @endforelse
    </div>
</div>
