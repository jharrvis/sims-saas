<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Ekstrakurikuler</span>
            <h2 class="management-page-title">{{ $ekskul->nama }}</h2>
            <p>
                @if ($ekskul->jadwal) Jadwal: {{ $ekskul->jadwal }} · @endif
                @if ($ekskul->pembina) Pembina: {{ $ekskul->pembina->nama }} @endif
            </p>
        </div>
        <div class="page-actions">
            <a href="{{ route('ekskul.index') }}" class="back-link">← Semua Ekstrakurikuler</a>
            @can('ekskul.update')
                <a href="{{ route('ekskul.edit', $ekskul) }}" class="btn btn-outline">Ubah Ekstrakurikuler</a>
            @endcan
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    @can('ekskul.update')
        <section class="card" aria-labelledby="ekskul-tambah-title">
            <h3 id="ekskul-tambah-title" class="font-semibold mb-3">Tambah Anggota</h3>
            <input type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari siswa…"
                   aria-label="Cari siswa" class="field mb-3">
            @forelse ($calon as $s)
                <div wire:key="calon-{{ $s->id }}" class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                    <span class="text-sm text-slate-700 dark:text-slate-200">{{ $s->nama }} <span class="text-slate-400">· {{ $s->nis }}</span></span>
                    <button wire:click="tambahAnggota({{ $s->id }})" class="text-xs font-semibold text-primary hover:underline">Tambah</button>
                </div>
            @empty
                <p class="text-sm text-slate-500">Tidak ada siswa yang cocok.</p>
            @endforelse
        </section>
    @endcan

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">NIS</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Bergabung</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($anggota as $a)
                        <tr wire:key="anggota-{{ $a->id }}">
                            <td class="text-slate-600 dark:text-slate-300">{{ $a->nis }}</td>
                            <td class="font-semibold">{{ $a->nama }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $a->pivot->tanggal_gabung ? \Carbon\Carbon::parse($a->pivot->tanggal_gabung)->format('d M Y') : '—' }}</td>
                            <td class="text-right">
                                @can('ekskul.update')
                                    <button wire:click="keluarkanAnggota({{ $a->id }})" class="text-xs font-semibold text-danger hover:underline">Keluarkan</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-500">Belum ada anggota.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
