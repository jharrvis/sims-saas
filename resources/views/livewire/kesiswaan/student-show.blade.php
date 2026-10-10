<div class="max-w-4xl mx-auto space-y-5">
    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <span class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl font-bold">
                    {{ strtoupper(substr($student->nama, 0, 1)) }}
                </span>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ $student->nama }}</h1>
                    <p class="text-sm text-slate-500">NIS {{ $student->nis }}@if($student->nisn) · NISN {{ $student->nisn }}@endif</p>
                    <span class="inline-flex mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $student->status === 'aktif' ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-500' }}">
                        {{ ucfirst($student->status) }}
                    </span>
                </div>
            </div>
            @can('siswa.update')
                <a href="{{ route('siswa.edit', $student) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">
                    <i data-lucide="pencil" class="w-4 h-4"></i> Ubah
                </a>
            @endcan
        </div>

        <dl class="mt-6 grid sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div><dt class="text-slate-500 dark:text-slate-400">Jenis Kelamin</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $student->jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Tempat, Tanggal Lahir</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $student->tempat_lahir ?? '—' }}, {{ $student->tgl_lahir?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Agama</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $student->agama ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">No. HP</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $student->phone ?? '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Alamat</dt><dd class="font-medium text-slate-800 dark:text-white">{{ $student->alamat ?? '—' }}</dd></div>
        </dl>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Wali Murid</h2>
        @if ($student->guardians->isEmpty())
            <p class="text-sm text-slate-500">Belum ada data wali.</p>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach ($student->guardians as $wali)
                    <li class="py-3 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-800 dark:text-white">{{ $wali->nama }}</p>
                            <p class="text-xs text-slate-500">{{ ucfirst($wali->pivot->hubungan) }} · {{ $wali->no_wa }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <a href="{{ route('siswa.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
        ← Kembali ke daftar siswa
    </a>
</div>
