<div>
    <div class="mb-5">
        <h1 class="text-2xl font-extrabold">Tambah Tenant</h1>
        <p class="text-sm text-slate-500 mt-1">Sekolah baru langsung mendapat trial 30 hari + akun admin superadmin. Nomor WA admin boleh sudah terdaftar (satu nomor bisa untuk banyak peran).</p>
    </div>

    <form wire:submit="simpan" class="max-w-2xl rounded-xl bg-white shadow-sm p-6 space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="nama">Nama sekolah</label>
            <input id="nama" type="text" wire:model="nama" placeholder="cth. SDN 01 Jakarta"
                   class="w-full rounded-lg border border-slate-200 py-2.5 px-4 text-sm outline-none focus:border-primary">
            @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="jenjang">Jenjang</label>
                <select id="jenjang" wire:model="jenjang"
                        class="w-full rounded-lg border border-slate-200 py-2.5 px-3 text-sm outline-none focus:border-primary">
                    <option value="">— Pilih —</option>
                    @foreach (['SD', 'SMP', 'SMA', 'SMK'] as $j)
                        <option value="{{ $j }}">{{ $j }}</option>
                    @endforeach
                </select>
                @error('jenjang') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="npsn">NPSN <span class="font-normal text-slate-400">(opsional)</span></label>
                <input id="npsn" type="text" wire:model="npsn" placeholder="cth. 20100123"
                       class="w-full rounded-lg border border-slate-200 py-2.5 px-4 text-sm outline-none focus:border-primary">
                @error('npsn') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="admin_nama">Nama admin sekolah</label>
                <input id="admin_nama" type="text" wire:model="admin_nama" placeholder="Nama penanggung jawab"
                       class="w-full rounded-lg border border-slate-200 py-2.5 px-4 text-sm outline-none focus:border-primary">
                @error('admin_nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="admin_phone">No. WA admin</label>
                <input id="admin_phone" type="text" wire:model="admin_phone" placeholder="6281234567890"
                       class="w-full rounded-lg border border-slate-200 py-2.5 px-4 text-sm outline-none focus:border-primary">
                @error('admin_phone') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="plan_slug">Paket trial</label>
            <select id="plan_slug" wire:model="plan_slug"
                    class="w-full rounded-lg border border-slate-200 py-2.5 px-3 text-sm outline-none focus:border-primary">
                @foreach ($plans as $p)
                    <option value="{{ $p->slug }}">{{ $p->nama }} — Rp{{ number_format($p->harga_bulanan, 0, ',', '.') }}/bln</option>
                @endforeach
            </select>
            @error('plan_slug') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                Buat Tenant
            </button>
            <a href="{{ route('platform.tenants') }}" class="text-sm text-slate-500 hover:underline">Batal</a>
        </div>
    </form>
</div>
