<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="flex items-center justify-between mb-5">
        <h1 class="text-2xl font-extrabold">Paket Langganan</h1>
        <button wire:click="edit(null)" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">+ Paket Baru</button>
    </div>

    <div class="grid md:grid-cols-3 gap-4 mb-8">
        @foreach ($plans as $p)
            <div class="rounded-xl bg-white p-5 shadow-sm {{ $p->aktif ? '' : 'opacity-60' }}">
                <div class="flex items-center justify-between">
                    <h2 class="font-extrabold text-lg">{{ $p->nama }}</h2>
                    @if (! $p->aktif) <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">NONAKTIF</span> @endif
                </div>
                <p class="text-2xl font-extrabold mt-2">Rp{{ number_format($p->harga_bulanan, 0, ',', '.') }}<span class="text-sm font-normal text-slate-400">/bln</span></p>
                @if ($p->harga_tahunan)
                    <p class="text-sm text-slate-500">Rp{{ number_format($p->harga_tahunan, 0, ',', '.') }}/thn</p>
                @endif
                <p class="text-sm text-slate-500 mt-2">{{ $p->deskripsi ?? '—' }}</p>
                <p class="text-xs text-slate-400 mt-1">Maks. siswa: {{ $p->max_siswa ?? 'tanpa batas' }}</p>
                <button wire:click="edit({{ $p->id }})" class="mt-3 text-sm font-semibold text-primary hover:underline">Ubah</button>
            </div>
        @endforeach
    </div>

    @if ($showForm)
        <div class="rounded-xl bg-white p-5 shadow-sm max-w-xl">
            <h2 class="font-bold mb-4">{{ $editId ? 'Ubah Paket' : 'Paket Baru' }}</h2>
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-slate-500">Nama</label>
                    <input type="text" wire:model="nama" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    @error('nama') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-slate-500">Slug</label>
                    <input type="text" wire:model="slug" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    @error('slug') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-slate-500">Harga bulanan (Rp)</label>
                    <input type="number" wire:model="harga_bulanan" min="0" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    @error('harga_bulanan') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-slate-500">Harga tahunan (Rp, opsional)</label>
                    <input type="number" wire:model="harga_tahunan" min="0" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500">Maks. siswa (kosong = tanpa batas)</label>
                    <input type="number" wire:model="max_siswa" min="0" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                </div>
                <div class="flex items-end pb-2">
                    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" wire:model="aktif" class="accent-primary"> Aktif</label>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs text-slate-500">Deskripsi</label>
                    <textarea wire:model="deskripsi" rows="2" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button wire:click="simpan" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white">Simpan</button>
                <button wire:click="$set('showForm', false)" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold">Batal</button>
            </div>
        </div>
    @endif
</div>
