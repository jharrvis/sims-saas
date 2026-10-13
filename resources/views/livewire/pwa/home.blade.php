<div>
    @php
        $jam = (int) now()->format('H');
        $salam = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
        $absTotal = $rekapAbsensi->sum();
        $absPersen = $absTotal > 0 ? (int) round(($rekapAbsensi['H'] ?? 0) / $absTotal * 100) : 0;
        $izinSakit = ($rekapAbsensi['I'] ?? 0) + ($rekapAbsensi['S'] ?? 0);
        $jadwalWarna = ['bg-blue-500', 'bg-purple-400', 'bg-emerald-500'];
    @endphp

    @if ($role === 'orang-tua')
        {{-- Hero orang tua (design ortu/index.html): gradasi emerald + lingkaran dekoratif --}}
        <div class="px-5 pt-12 pb-16 relative overflow-hidden" style="background: linear-gradient(135deg, #059669, #047857)">
            <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/5 -mr-10 -mt-10"></div>
            <div class="flex justify-between items-start relative z-10">
                <div class="min-w-0">
                    <p class="text-emerald-100 text-sm">{{ $salam }}</p>
                    <h1 class="text-white text-2xl font-extrabold mt-0.5 truncate">{{ auth()->user()?->name ?? 'Orang Tua' }}</h1>
                    <p class="text-emerald-100 text-sm mt-1">Orang Tua &bull; {{ $children->count() }} Anak terdaftar</p>
                </div>
                <div class="w-12 h-12 rounded-2xl overflow-hidden border-2 border-white/40 bg-white/20 flex items-center justify-center text-white font-extrabold shrink-0">
                    {{ mb_substr(auth()->user()?->name ?? 'W', 0, 1) }}
                </div>
            </div>
        </div>
    @else
        {{-- Hero siswa (design siswa/index.html): biru + lingkaran dekoratif --}}
        <div class="bg-[#3c50e0] px-5 pt-12 pb-16 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/5 -mr-10 -mt-10"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full bg-white/5 -ml-10 -mb-10"></div>
            <div class="flex justify-between items-start relative z-10">
                <div class="min-w-0">
                    <p class="text-blue-200 text-sm font-medium">{{ $salam }}</p>
                    <h1 class="text-white text-2xl font-extrabold mt-0.5 truncate">{{ $student?->nama ?? auth()->user()?->name ?? 'Siswa' }}</h1>
                    @if ($student)
                        <p class="text-blue-100 text-sm mt-1">{{ $rombel?->nama ?? 'Belum ada kelas' }} &bull; NIS: {{ $student->nis }}</p>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-2xl overflow-hidden border-2 border-white/40 bg-white/20 flex items-center justify-center text-white font-extrabold shrink-0">
                    @if ($student?->fotoUrl())
                        <img src="{{ $student->fotoUrl() }}" alt="" class="w-full h-full object-cover">
                    @else
                        {{ mb_substr($student?->nama ?? 'S', 0, 1) }}
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="px-4 -mt-8 relative z-10 pb-28">
        @if ($role === 'orang-tua')
            {{-- Anak Saya (design ortu/index.html + ortu/anak.html) --}}
            <div id="anak" class="space-y-4 mb-5 scroll-mt-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-base">Anak Saya</h3>
                    <span class="text-xs text-slate-400">{{ $children->count() }} anak</span>
                </div>
                @foreach ($children as $child)
                    @if ($child->id === $student?->id)
                        <div class="bg-white rounded-2xl shadow-xl border border-primary/20 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-xl overflow-hidden bg-primary/10 text-primary flex items-center justify-center font-extrabold shrink-0">
                                        @if ($child->fotoUrl())
                                            <img src="{{ $child->fotoUrl() }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            {{ mb_substr($child->nama, 0, 1) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-extrabold text-slate-800 truncate">{{ $child->nama }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $student->school?->nama ?? '' }}{{ $student->school ? ' &bull; ' : '' }}{{ $rombel?->nama ?? 'Belum ada kelas' }} &bull; NIS {{ $child->nis }}</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded-full font-bold shrink-0 ml-2">Aktif</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-slate-50 rounded-xl p-2.5 text-center">
                                    <p class="font-extrabold text-primary text-base">{{ $absPersen }}%</p>
                                    <p class="text-[10px] text-slate-400">Kehadiran</p>
                                </div>
                                <div class="bg-slate-50 rounded-xl p-2.5 text-center">
                                    <p class="font-extrabold text-warning text-base">{{ $izinSakit }}</p>
                                    <p class="text-[10px] text-slate-400">Izin/Sakit</p>
                                </div>
                                <div class="bg-slate-50 rounded-xl p-2.5 text-center">
                                    @if ($tagihanAktif > 0)
                                        <p class="font-extrabold text-danger text-base">{{ $tagihanAktif }}</p>
                                        <p class="text-[10px] text-slate-400">Tagihan</p>
                                    @else
                                        <p class="font-extrabold text-success text-sm leading-6">Lunas</p>
                                        <p class="text-[10px] text-slate-400">Tagihan</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <a href="{{ route('pwa.absensi') }}" class="flex-1 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center text-xs font-semibold text-slate-600">Absensi</a>
                                <a href="{{ route('pwa.nilai') }}" class="flex-1 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center text-xs font-semibold text-slate-600">Nilai</a>
                                <a href="{{ route('pwa.jadwal') }}" class="flex-1 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center text-xs font-semibold text-slate-600">Jadwal</a>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('pwa.switch-child') }}">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $child->id }}">
                            <button class="w-full bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex items-center gap-3 text-left">
                                <div class="w-11 h-11 rounded-xl overflow-hidden bg-slate-100 text-slate-500 flex items-center justify-center font-extrabold shrink-0">
                                    @if ($child->fotoUrl())
                                        <img src="{{ $child->fotoUrl() }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        {{ mb_substr($child->nama, 0, 1) }}
                                    @endif
                                </div>
                                <span class="flex-1 min-w-0">
                                    <span class="block font-extrabold text-slate-800 truncate">{{ $child->nama }}</span>
                                    <span class="text-xs text-slate-400">NIS {{ $child->nis }}</span>
                                </span>
                                <x-icon name="chevron-right" class="w-4 h-4 text-slate-300 shrink-0" />
                            </button>
                        </form>
                    @endif
                @endforeach
            </div>
        @else
            {{-- Statistik cepat (design siswa/index.html) --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-4 grid grid-cols-3 gap-3 mb-5">
                <div class="text-center">
                    <p class="text-2xl font-extrabold text-[#3c50e0]">{{ $absPersen }}%</p>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Kehadiran</p>
                </div>
                <div class="text-center border-x border-slate-100">
                    <p class="text-2xl font-extrabold text-warning">{{ $izinSakit }}</p>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Izin/Sakit</p>
                </div>
                <a href="{{ route('pwa.tagihan') }}" class="text-center block">
                    <p class="text-2xl font-extrabold {{ $tagihanAktif > 0 ? 'text-danger' : 'text-slate-400' }}">{{ $tagihanAktif }}</p>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Tagihan aktif</p>
                </a>
            </div>
        @endif

        {{-- Jadwal Hari Ini (design siswa/index.html) --}}
        <div class="mb-5">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-slate-800 text-base">Jadwal Hari Ini</h3>
                <a href="{{ route('pwa.jadwal') }}" class="text-xs text-[#3c50e0] font-semibold">Lihat Semua →</a>
            </div>
            <div class="space-y-3">
                @forelse ($jadwalHariIni as $j)
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4">
                        <div class="text-center min-w-[52px]">
                            <p class="text-[10px] text-slate-400 font-medium">Jam ke</p>
                            <p class="text-lg font-extrabold text-slate-800">{{ $j->jam_ke }}</p>
                        </div>
                        <div class="w-1 h-12 rounded-full {{ $jadwalWarna[$loop->index % 3] }} flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-800 text-sm truncate">{{ $j->subject?->nama ?? '—' }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $j->employee?->nama }}{{ $j->ruang ? ' · '.$j->ruang : '' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">Tidak ada jadwal hari ini.</p>
                @endforelse
            </div>
        </div>

        {{-- Pengumuman (design siswa/index.html) --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-slate-800 text-base">Pengumuman</h3>
                <a href="{{ route('pwa.pengumuman') }}" class="text-xs text-[#3c50e0] font-semibold">Semua →</a>
            </div>
            @forelse ($pengumuman as $p)
                @if ($loop->first)
                    <a href="{{ route('pwa.pengumuman.show', $p) }}" class="block bg-gradient-to-r from-[#3c50e0] to-[#2a3cb0] rounded-2xl p-4 text-white mb-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                <x-icon name="megaphone" class="w-5 h-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-blue-200 font-medium">{{ $p->tayang_dari?->locale('id')->isoFormat('D MMM Y') }}</p>
                                <p class="font-bold text-white mt-1">{{ $p->judul }}</p>
                                <p class="text-xs text-blue-100 mt-1 line-clamp-2">{{ strip_tags($p->isi) }}</p>
                            </div>
                        </div>
                    </a>
                @else
                    <a href="{{ route('pwa.pengumuman.show', $p) }}" class="block bg-white rounded-2xl p-4 shadow-sm border border-slate-100 mb-2">
                        <p class="text-sm font-semibold text-slate-800">{{ $p->judul }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $p->tayang_dari?->locale('id')->isoFormat('D MMM Y') }}</p>
                    </a>
                @endif
            @empty
                <p class="text-xs text-slate-400 italic">Belum ada pengumuman.</p>
            @endforelse
        </div>
    </div>
</div>
