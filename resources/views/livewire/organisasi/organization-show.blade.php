<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('organisasi.index') }}" class="text-sm text-slate-500 hover:text-primary">← Semua Organisasi</a>
        @can('organisasi.update')
            <a href="{{ route('organisasi.edit', $organization) }}" class="text-sm font-semibold text-primary hover:underline">Ubah Organisasi</a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $organization->nama }}</h2>
        <p class="text-sm text-slate-500 mt-1">
            {{ \App\Models\Organization::JENIS[$organization->jenis] }}
            @if ($organization->pembina) · Pembina: {{ $organization->pembina->nama }} @endif
        </p>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5">
            <h3 class="font-semibold text-slate-800 dark:text-white mb-3">Pengurus ({{ $pengurus->count() }})</h3>
            @can('organisasi.update')
                <div class="mb-3">
                    <input type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari siswa…"
                           aria-label="Cari siswa"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white mb-2">
                    <div class="grid grid-cols-2 gap-2">
                        <select wire:model="student_id" aria-label="Pilih siswa"
                                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm dark:text-white">
                            <option value="">— Pilih siswa —</option>
                            @foreach ($calon as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                            @endforeach
                        </select>
                        <input type="text" wire:model="jabatan" placeholder="Jabatan (mis. Ketua)"
                               class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <input type="text" wire:model="periode" placeholder="Periode (mis. 2026/2027)"
                               class="flex-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                        <button wire:click="tambahPengurus" class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-primary/90">+</button>
                    </div>
                </div>
            @endcan
            @forelse ($pengurus as $p)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                    <span class="text-sm text-slate-700 dark:text-slate-200">
                        {{ $p->nama }}
                        @if ($p->pivot->jabatan) <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-primary/10 text-primary ml-1">{{ $p->pivot->jabatan }}</span> @endif
                    </span>
                    @can('organisasi.update')
                        <button wire:click="keluarkanPengurus({{ $p->id }})" class="text-xs font-semibold text-danger hover:underline">Nonaktifkan</button>
                    @endcan
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada pengurus.</p>
            @endforelse
        </div>

        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-slate-800 dark:text-white">Kegiatan</h3>
                @can('organisasi.update')
                    <button wire:click="$set('showKegiatanForm', true)" class="text-xs font-semibold text-primary hover:underline">+ Tambah</button>
                @endcan
            </div>

            @if ($showKegiatanForm)
                @can('organisasi.update')
                    <div class="rounded-lg bg-slate-50 dark:bg-slate-800 p-3 mb-3 grid grid-cols-2 gap-2">
                        <input type="text" wire:model="kegiatan_nama" placeholder="Nama kegiatan"
                               class="col-span-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                        <input type="date" wire:model="kegiatan_mulai" aria-label="Tanggal mulai"
                               class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                        <input type="date" wire:model="kegiatan_selesai" aria-label="Tanggal selesai"
                               class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                        <select wire:model="kegiatan_status"
                                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm dark:text-white">
                            @foreach (\App\Models\OrganizationActivity::STATUS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button wire:click="tambahKegiatan" class="rounded-lg bg-primary py-2 text-sm font-semibold text-white hover:bg-primary/90">Simpan</button>
                        @error('kegiatan_nama') <p class="col-span-2 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                @endcan
            @endif

            @forelse ($kegiatan as $k)
                <div class="flex items-start justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                    <div>
                        <span class="text-sm font-semibold text-slate-800 dark:text-white">{{ $k->nama }}</span>
                        <p class="text-xs text-slate-500">
                            @if ($k->tgl_mulai) {{ $k->tgl_mulai->format('d M Y') }}@endif
                            @if ($k->tgl_selesai) – {{ $k->tgl_selesai->format('d M Y') }}@endif
                        </p>
                    </div>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $k->status === 'selesai' ? 'bg-success/10 text-success' : ($k->status === 'berjalan' ? 'bg-warning/15 text-amber-600' : ($k->status === 'batal' ? 'bg-danger/10 text-danger' : 'bg-slate-100 text-slate-500 dark:bg-slate-800')) }}">
                        {{ \App\Models\OrganizationActivity::STATUS[$k->status] }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada kegiatan.</p>
            @endforelse
        </div>
    </div>
</div>
