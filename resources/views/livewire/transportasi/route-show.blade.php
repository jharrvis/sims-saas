<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Operasional / Transportasi</span>
            <h2 class="management-page-title">{{ $route->nama }}</h2>
            <p>
                @if ($route->no_kendaraan) {{ $route->no_kendaraan }} · @endif
                @if ($route->sopir) Sopir: {{ $route->sopir }} · @endif
                Kapasitas: {{ $route->kapasitas > 0 ? $route->kapasitas : 'tanpa batas' }}
            </p>
        </div>
        <div class="page-actions">
            <a href="{{ route('transportasi.index') }}" class="back-link">← Semua Rute</a>
            @can('transportasi.update')
                <a href="{{ route('transportasi.edit', $route) }}" class="btn btn-outline">Ubah Rute</a>
            @endcan
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-5">
        <section class="card" aria-labelledby="rute-stop-title">
            <h3 id="rute-stop-title" class="font-semibold mb-3">Titik Penjemputan</h3>
            @forelse ($stops as $stop)
                <div wire:key="stop-{{ $stop->id }}" class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
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
                    <input type="text" wire:model="stop_nama" placeholder="Nama titik…" class="field flex-1">
                    <input type="time" wire:model="stop_jam" aria-label="Jam berangkat" class="field w-28">
                    <button wire:click="tambahStop" class="btn btn-primary">+</button>
                </div>
                @error('stop_nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            @endcan
        </section>

        <section class="card" aria-labelledby="rute-penumpang-title">
            <h3 id="rute-penumpang-title" class="font-semibold mb-3">Penumpang ({{ $penumpang->count() }})</h3>
            @can('transportasi.update')
                <div class="mb-3">
                    <input type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari siswa…"
                           aria-label="Cari siswa" class="field mb-2">
                    <div class="flex items-center gap-2">
                        <select wire:model="student_id" aria-label="Pilih siswa" class="select-field flex-1">
                            <option value="">— Pilih siswa —</option>
                            @foreach ($calon as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                            @endforeach
                        </select>
                        <select wire:model="stop_id" aria-label="Pilih titik" class="select-field w-32">
                            <option value="">— Titik —</option>
                            @foreach ($stops as $stop)
                                <option value="{{ $stop->id }}">{{ $stop->nama }}</option>
                            @endforeach
                        </select>
                        <button wire:click="tambahPenumpang" class="btn btn-primary">+</button>
                    </div>
                </div>
            @endcan
            @forelse ($penumpang as $p)
                <div wire:key="penumpang-{{ $p->id }}" class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                    <span class="text-sm text-slate-700 dark:text-slate-200">{{ $p->nama }} <span class="text-slate-400">· {{ $p->nis }}</span></span>
                    @can('transportasi.update')
                        <button wire:click="keluarkanPenumpang({{ $p->id }})" class="text-xs font-semibold text-danger hover:underline">Keluarkan</button>
                    @endcan
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada penumpang.</p>
            @endforelse
        </section>
    </div>
</div>
