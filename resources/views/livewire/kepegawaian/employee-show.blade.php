<div class="max-w-4xl mx-auto space-y-5">
    <a href="{{ route('guru.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
        <x-icon name="arrow-left" class="w-4 h-4" /> Kembali ke Data Guru
    </a>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                @php
                    $namaParts = preg_split('/\s+/', trim($employee->nama));
                    $inisial = strtoupper(mb_substr($namaParts[0], 0, 1) . (count($namaParts) > 1 ? mb_substr(end($namaParts), 0, 1) : ''));
                    $avatarColors = [
                        'bg-primary/10 text-primary',
                        'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                        'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                        'bg-sky-500/10 text-sky-600 dark:text-sky-400',
                        'bg-violet-500/10 text-violet-600 dark:text-violet-400',
                        'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                    ];
                    $avatarColor = $avatarColors[abs(crc32($employee->nama)) % count($avatarColors)];
                @endphp
                <span class="w-16 h-16 shrink-0 overflow-hidden rounded-full {{ $employee->fotoUrl() ? '' : $avatarColor }} flex items-center justify-center text-2xl font-bold">
                    @if ($employee->fotoUrl())
                        <img src="{{ $employee->fotoUrl() }}" alt="Foto {{ $employee->nama }}" class="h-full w-full object-cover">
                    @else
                        {{ $inisial }}
                    @endif
                </span>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ $employee->nama }}</h1>
                    <p class="text-sm text-slate-500">{{ $employee->labelJenis() }}@if($employee->jabatan) · {{ $employee->jabatan }}@endif</p>
                    <span class="inline-flex mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $employee->status_aktif ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-500' }}">
                        {{ $employee->status_aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
            @can('guru.update')
                <a href="{{ route('guru.edit', $employee) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">
                    <x-icon name="pencil" class="w-4 h-4" /> Ubah
                </a>
            @endcan
        </div>

        <dl class="mt-6 grid sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div><dt class="text-slate-500 dark:text-slate-400">NIP / NUPTK</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->nip_nuptk ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">NIK</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->nik ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Tempat, Tanggal Lahir</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->tempat_lahir ?? '—' }}, {{ $employee->tgl_lahir?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Jenis Kelamin</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->labelJk() }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Agama</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->agama ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Email</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->email ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">No. HP</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->phone ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Status</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->status_aktif ? 'Aktif' : 'Nonaktif' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Alamat</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->alamat ?? '—' }}</dd></div>
        </dl>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Pendidikan</h2>
        <dl class="grid sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div><dt class="text-slate-500 dark:text-slate-400">Pendidikan Terakhir</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->labelPendidikan() }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Tahun Lulus</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->tahun_lulus ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Jurusan</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->jurusan ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Perguruan Tinggi / Sekolah</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->perguruan_tinggi ?? '—' }}</dd></div>
        </dl>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Kepegawaian</h2>
        <dl class="grid sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div><dt class="text-slate-500 dark:text-slate-400">Jenis</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->labelJenis() }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Status Kepegawaian</dt><dd class="font-medium text-slate-800 dark:text-white">{{ ucfirst($employee->status_kepegawaian) }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Jabatan</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->jabatan ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">TMT</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->tmt?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Golongan / Pangkat</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->golongan ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Sertifikasi</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->labelSertifikasi() }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">No. Sertifikat Pendidik</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $employee->no_sertifikat ?? '—' }}</dd></div>
        </dl>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Wali Kelas</h2>
        @if ($employee->rombelsAsWali->isEmpty())
            <p class="text-sm text-slate-500">Bukan wali kelas di rombel manapun.</p>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach ($employee->rombelsAsWali as $rombel)
                    <li class="py-3 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-800 dark:text-white">{{ $rombel->nama }}</p>
                            <p class="text-xs text-slate-500">Tingkat {{ $rombel->tingkat }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Jadwal Mengajar</h2>
        @if ($jadwalPerHari->isEmpty())
            <p class="text-sm text-slate-500">Belum ada jadwal mengajar.</p>
        @else
            <div class="space-y-4">
                @foreach ($jadwalPerHari as $hari => $entries)
                    <div>
                        <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">{{ ucfirst($hari) }}</h3>
                        <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @foreach ($entries as $entry)
                                <li class="py-2 flex items-center justify-between text-sm">
                                    <div>
                                        <span class="font-medium text-slate-800 dark:text-white">{{ $entry->subject?->nama ?? '—' }}</span>
                                        <span class="text-slate-500"> · Kelas {{ $entry->rombel?->nama ?? '—' }}</span>
                                    </div>
                                    <span class="text-slate-500">Jam ke-{{ $entry->jam_ke }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
