<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('alumni.index') }}" class="text-sm text-slate-500 hover:text-primary">← Semua Alumni</a>
        @can('alumni.update')
            <a href="{{ route('alumni.edit', $alumni) }}" class="text-sm font-semibold text-primary hover:underline">Ubah Data</a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $alumni->nama }}</h2>
        <p class="text-sm text-slate-500 mt-1">
            @if ($alumni->tahun_lulus) Lulus {{ $alumni->tahun_lulus }} · @endif
            @if ($alumni->phone) {{ $alumni->phone }} · @endif
            @if ($alumni->email) {{ $alumni->email }} @endif
        </p>
        @if ($alumni->alamat)
            <p class="text-sm text-slate-500 mt-0.5">{{ $alumni->alamat }}</p>
        @endif
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800 dark:text-white">Riwayat Karier (Tracer Study)</h3>
            @can('alumni.update')
                <button wire:click="$set('showForm', true)" class="text-xs font-semibold text-primary hover:underline">+ Tambah</button>
            @endcan
        </div>

        @if ($showForm)
            @can('alumni.update')
                <form wire:submit="tambahKarier" class="rounded-lg bg-slate-50 dark:bg-slate-800 p-4 mb-4 grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
                        <select wire:model="status"
                                class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm dark:text-white">
                            @foreach (\App\Models\AlumniCareer::STATUS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Tahun Mulai</label>
                        <input type="text" wire:model="tahun_mulai" maxlength="9" placeholder="2026"
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Institusi / Perusahaan</label>
                        <input type="text" wire:model="institusi" maxlength="200"
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Jurusan / Posisi</label>
                        <input type="text" wire:model="jurusan_posisi" maxlength="200"
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Keterangan</label>
                        <input type="text" wire:model="keterangan" maxlength="2000"
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    </div>
                    <div class="sm:col-span-2 flex items-center gap-3">
                        <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary/90">Simpan</button>
                        <button type="button" wire:click="$set('showForm', false)" class="text-sm text-slate-500 hover:text-primary">Batal</button>
                    </div>
                </form>
            @endcan
        @endif

        @forelse ($careers as $c)
            <div class="flex items-start justify-between py-3 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <div>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-primary/10 text-primary">{{ \App\Models\AlumniCareer::STATUS[$c->status] }}</span>
                    <p class="text-sm font-semibold text-slate-800 dark:text-white mt-1">{{ $c->institusi ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ $c->jurusan_posisi }}{{ $c->tahun_mulai ? ' · sejak '.$c->tahun_mulai : '' }}</p>
                    @if ($c->keterangan) <p class="text-xs text-slate-400 mt-0.5">{{ $c->keterangan }}</p> @endif
                </div>
                @can('alumni.update')
                    <button wire:click="hapusKarier({{ $c->id }})" class="text-xs font-semibold text-danger hover:underline">Hapus</button>
                @endcan
            </div>
        @empty
            <p class="text-sm text-slate-500">Belum ada riwayat karier.</p>
        @endforelse
    </div>
</div>
