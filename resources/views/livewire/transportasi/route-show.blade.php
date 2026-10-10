<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('transportasi.index') }}" class="text-sm text-slate-500 hover:text-primary">← Semua Rute</a>
        @can('transportasi.update')
            <a href="{{ route('transportasi.edit', $route) }}" class="text-sm font-semibold text-primary hover:underline">Ubah Rute</a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $route->nama }}</h2>
        <p class="text-sm text-slate-500 mt-1">
            @if ($route->no_kendaraan) {{ $route->no_kendaraan }} · @endif
            @if ($route->sopir) Sopir: {{ $route->sopir }} · @endif
            Kapasitas: {{ $route->kapasitas > 0 ? $route->kapasitas : 'tanpa batas' }}
        </p>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5">
            <h3 class="font-semibold text-slate-800 dark:text-white mb-3">Titik Penjemputan</h3>
            @forelse ($stops as $stop)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                    <span class="text-sm text-slate-700 dark:text-slate-200">
                        <span class="font-bold text-primary mr-1">{{ $stop->urutan }}</span> {{ $stop->nama }}
                        @if ($stop->jam_berangkat) <span class="text-xs text-slate-400">· {{ $stop->jam_berangkat->format('H:i') }}</span> @endif
                    </span>
                    @can('transportasi.update')
                        <button wire:click="hapusStop({{ $stop->id }})" class="text-xs font-semibold text-danger hover:underline">Hapus</button>
                    @endcan
                </div>
            @empty
                <p class="text-sm text-slate-500 mb-3">Belum ada titik penjemputan.</p>
            @endforelse
            @can('transportasi.update')
                <div class="flex items-center gap-2 mt-3">
                    <input type="text" wire:model="stop_nama" placeholder="Nama titik…"
                           class="flex-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    <input type="time" wire:model="stop_jam" aria-label="Jam berangkat"
                           class="w-28 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    <button wire:click="tambahStop" class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-primary/90">+</button>
                </div>
                @error('stop_nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            @endcan
        </div>

        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5">
            <h3 class="font-semibold text-slate-800 dark:text-white mb-3">Penumpang ({{ $penumpang->count() }})</h3>
            @can('transportasi.update')
                <div class="mb-3">
                    <input type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari siswa…"
                           aria-label="Cari siswa"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white mb-2">
                    <div class="flex items-center gap-2">
                        <select wire:model="student_id" aria-label="Pilih siswa"
                                class="flex-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm dark:text-white">
                            <option value="">— Pilih siswa —</option>
                            @foreach ($calon as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                            @endforeach
                        </select>
                        <select wire:model="stop_id" aria-label="Pilih titik"
                                class="w-32 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm dark:text-white">
                            <option value="">— Titik —</option>
                            @foreach ($stops as $stop)
                                <option value="{{ $stop->id }}">{{ $stop->nama }}</option>
                            @endforeach
                        </select>
                        <button wire:click="tambahPenumpang" class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-primary/90">+</button>
                    </div>
                </div>
            @endcan
            @forelse ($penumpang as $p)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                    <span class="text-sm text-slate-700 dark:text-slate-200">{{ $p->nama }} <span class="text-slate-400">· {{ $p->nis }}</span></span>
                    @can('transportasi.update')
                        <button wire:click="keluarkanPenumpang({{ $p->id }})" class="text-xs font-semibold text-danger hover:underline">Keluarkan</button>
                    @endcan
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada penumpang.</p>
            @endforelse
        </div>
    </div>
</div>
