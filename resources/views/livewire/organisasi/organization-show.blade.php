<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Organisasi</span>
            <h2 class="management-page-title">{{ $organization->nama }}</h2>
            <p>
                {{ \App\Models\Organization::JENIS[$organization->jenis] }}
                @if ($organization->pembina) · Pembina: {{ $organization->pembina->nama }} @endif
            </p>
        </div>
        <div class="page-actions">
            <a href="{{ route('organisasi.index') }}" class="back-link">← Semua Organisasi</a>
            @can('organisasi.update')
                <a href="{{ route('organisasi.edit', $organization) }}" class="btn btn-outline">Ubah Organisasi</a>
            @endcan
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-5">
        <section class="card" aria-labelledby="organisasi-pengurus-title">
            <h3 id="organisasi-pengurus-title" class="font-semibold mb-3">Pengurus ({{ $pengurus->count() }})</h3>
            @can('organisasi.update')
                <div class="mb-3">
                    <input type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari siswa…"
                           aria-label="Cari siswa" class="field mb-2">
                    <div class="grid grid-cols-2 gap-2">
                        <select wire:model="student_id" aria-label="Pilih siswa" class="select-field">
                            <option value="">— Pilih siswa —</option>
                            @foreach ($calon as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                            @endforeach
                        </select>
                        <input type="text" wire:model="jabatan" placeholder="Jabatan (mis. Ketua)" class="field">
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <input type="text" wire:model="periode" placeholder="Periode (mis. 2026/2027)" class="field flex-1">
                        <button wire:click="tambahPengurus" class="btn btn-primary">+</button>
                    </div>
                </div>
            @endcan
            @forelse ($pengurus as $p)
                <div wire:key="pengurus-{{ $p->id }}" class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                    <span class="text-sm text-slate-700 dark:text-slate-200">
                        {{ $p->nama }}
                        @if ($p->pivot->jabatan) <span class="badge badge-blue ml-1">{{ $p->pivot->jabatan }}</span> @endif
                    </span>
                    @can('organisasi.update')
                        <button wire:click="keluarkanPengurus({{ $p->id }})" class="text-xs font-semibold text-danger hover:underline">Nonaktifkan</button>
                    @endcan
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada pengurus.</p>
            @endforelse
        </section>

        <section class="card" aria-labelledby="organisasi-kegiatan-title">
            <div class="flex items-center justify-between mb-3">
                <h3 id="organisasi-kegiatan-title" class="font-semibold">Kegiatan</h3>
                @can('organisasi.update')
                    <button wire:click="$set('showKegiatanForm', true)" class="text-xs font-semibold text-primary hover:underline">+ Tambah</button>
                @endcan
            </div>

            @if ($showKegiatanForm)
                @can('organisasi.update')
                    <div class="rounded-lg bg-slate-50 dark:bg-slate-800 p-3 mb-3 grid grid-cols-2 gap-2">
                        <input type="text" wire:model="kegiatan_nama" placeholder="Nama kegiatan" class="field col-span-2">
                        <input type="date" wire:model="kegiatan_mulai" aria-label="Tanggal mulai" class="field">
                        <input type="date" wire:model="kegiatan_selesai" aria-label="Tanggal selesai" class="field">
                        <select wire:model="kegiatan_status" class="select-field">
                            @foreach (\App\Models\OrganizationActivity::STATUS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button wire:click="tambahKegiatan" class="btn btn-primary">Simpan</button>
                        @error('kegiatan_nama') <p class="management-field-error col-span-2" role="alert">{{ $message }}</p> @enderror
                    </div>
                @endcan
            @endif

            @forelse ($kegiatan as $k)
                <div wire:key="kegiatan-{{ $k->id }}" class="flex items-start justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                    <div>
                        <span class="text-sm font-semibold">{{ $k->nama }}</span>
                        <p class="text-xs text-slate-500">
                            @if ($k->tgl_mulai) {{ $k->tgl_mulai->format('d M Y') }}@endif
                            @if ($k->tgl_selesai) – {{ $k->tgl_selesai->format('d M Y') }}@endif
                        </p>
                    </div>
                    <span class="badge {{ $k->status === 'selesai' ? 'badge-green' : ($k->status === 'berjalan' ? 'badge-orange' : ($k->status === 'batal' ? 'badge-red' : 'badge-gray')) }}">
                        {{ \App\Models\OrganizationActivity::STATUS[$k->status] }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada kegiatan.</p>
            @endforelse
        </section>
    </div>
</div>
