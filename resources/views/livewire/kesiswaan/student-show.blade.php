<div class="max-w-4xl mx-auto space-y-5">
    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <span class="w-16 h-16 shrink-0 overflow-hidden rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl font-bold">
                    @if ($student->fotoUrl())
                        <img src="{{ $student->fotoUrl() }}" alt="Foto {{ $student->nama }}" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr($student->nama, 0, 1)) }}
                    @endif
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
            @can('siswa.view')
                <a href="{{ route('siswa.kartu', $student) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">
                    <i data-lucide="credit-card" class="w-4 h-4"></i> Kartu Pelajar
                </a>
            @endcan
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

        @can('wali-murid.link')
            <form wire:submit="attachGuardian" class="flex flex-col sm:flex-row gap-3 mb-5">
                <select wire:model="guardian_id" aria-label="Pilih wali"
                        class="flex-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    <option value="">— Pilih wali —</option>
                    @foreach ($guardians as $w)
                        <option value="{{ $w->id }}">{{ $w->nama }}@if($w->no_wa) ({{ $w->no_wa }})@endif</option>
                    @endforeach
                </select>
                <select wire:model="hubungan" aria-label="Hubungan"
                        class="rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    <option value="ayah">Ayah</option>
                    <option value="ibu">Ibu</option>
                    <option value="orang_tua">Orang Tua</option>
                    <option value="kakek">Kakek</option>
                    <option value="nenek">Nenek</option>
                    <option value="saudara">Saudara</option>
                    <option value="wali">Wali</option>
                </select>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                    <i data-lucide="link" class="w-4 h-4"></i> Tautkan
                </button>
            </form>
            @error('guardian_id') <p class="mb-3 text-xs text-danger">{{ $message }}</p> @enderror
        @endcan

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
                        @can('wali-murid.link')
                            <button wire:click="detachGuardian({{ $wali->id }})" wire:confirm="Lepas tautan wali ini?"
                                    class="p-2 rounded-lg text-slate-400 hover:text-danger hover:bg-red-50 dark:hover:bg-red-900/20" aria-label="Lepas {{ $wali->nama }}">
                                <i data-lucide="unlink" class="w-4 h-4"></i>
                            </button>
                        @endcan
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <livewire:kepatuhan.consent-panel subject-type="siswa" :subject-id="$student->id" />

    <a href="{{ route('siswa.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
        ← Kembali ke daftar siswa
    </a>
</div>
