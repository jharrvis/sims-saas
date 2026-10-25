<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Alumni</span>
            <h2 class="management-page-title">{{ $alumni->nama }}</h2>
            <p>
                @if ($alumni->tahun_lulus) Lulus {{ $alumni->tahun_lulus }} · @endif
                @if ($alumni->phone) {{ $alumni->phone }} · @endif
                @if ($alumni->email) {{ $alumni->email }} @endif
            </p>
            @if ($alumni->alamat)
                <p class="text-sm text-slate-500 mt-0.5">{{ $alumni->alamat }}</p>
            @endif
        </div>
        <div class="page-actions">
            <a href="{{ route('alumni.index') }}" class="back-link">← Semua Alumni</a>
            @can('alumni.update')
                <a href="{{ route('alumni.edit', $alumni) }}" class="btn btn-outline">Ubah Data</a>
            @endcan
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <section class="card" aria-labelledby="alumni-karier-title">
        <div class="flex items-center justify-between mb-4">
            <h3 id="alumni-karier-title" class="font-bold">Riwayat Karier (Tracer Study)</h3>
            @can('alumni.update')
                <button wire:click="$set('showForm', true)" class="text-xs font-semibold text-primary hover:underline">+ Tambah</button>
            @endcan
        </div>

        @if ($showForm)
            @can('alumni.update')
                <form wire:submit="tambahKarier" class="rounded-lg bg-slate-50 dark:bg-slate-800 p-4 mb-4 grid sm:grid-cols-2 gap-3">
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
                        <select wire:model="status" class="select-field">
                            @foreach (\App\Models\AlumniCareer::STATUS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Tahun Mulai</label>
                        <input type="text" wire:model="tahun_mulai" maxlength="9" placeholder="2026" class="field">
                        @error('tahun_mulai') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Institusi / Perusahaan</label>
                        <input type="text" wire:model="institusi" maxlength="200" class="field">
                        @error('institusi') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Jurusan / Posisi</label>
                        <input type="text" wire:model="jurusan_posisi" maxlength="200" class="field">
                        @error('jurusan_posisi') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Keterangan</label>
                        <input type="text" wire:model="keterangan" maxlength="2000" class="field">
                        @error('keterangan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="tambahKarier">
                            <span wire:loading.remove wire:target="tambahKarier">Simpan</span>
                            <span wire:loading wire:target="tambahKarier">Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="$set('showForm', false)" class="btn btn-outline">Batal</button>
                    </div>
                </form>
            @endcan
        @endif

        @forelse ($careers as $c)
            <div wire:key="karier-{{ $c->id }}" class="flex items-start justify-between py-3 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <div>
                    <span class="badge badge-blue">{{ \App\Models\AlumniCareer::STATUS[$c->status] }}</span>
                    <p class="text-sm font-semibold mt-1">{{ $c->institusi ?? '—' }}</p>
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
    </section>
</div>
