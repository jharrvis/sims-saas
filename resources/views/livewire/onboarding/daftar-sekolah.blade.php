<div class="max-w-lg mx-auto">
    @if ($selesai)
        <div class="rounded-2xl bg-white p-8 shadow-sm text-center">
            <div class="w-14 h-14 rounded-full bg-success/10 text-success flex items-center justify-center mx-auto mb-4">
                <i data-lucide="check" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl font-extrabold mb-2">Pendaftaran Berhasil!</h1>
            <p class="text-slate-500 text-sm mb-1"><strong>{{ $namaSekolahTerdaftar }}</strong> resmi terdaftar.</p>
            <p class="text-slate-500 text-sm mb-6">Masa trial <strong>30 hari</strong> Anda sudah aktif (maks. 100 siswa). Masuk dengan nomor WA yang didaftarkan untuk mulai mengelola sekolah.</p>
            <a href="{{ route('login') }}" class="inline-block rounded-xl bg-primary px-8 py-3 font-semibold text-white">Masuk Sekarang</a>
        </div>
    @else
        <div class="rounded-2xl bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-extrabold mb-1">Daftar Trial Gratis</h1>
            <p class="text-slate-500 text-sm mb-6">30 hari, maks. 100 siswa, tanpa kartu kredit.</p>

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-semibold" for="nama">Nama Sekolah</label>
                    <input id="nama" type="text" wire:model="nama" placeholder="SDN Contoh 01"
                           class="mt-1 w-full rounded-xl border border-slate-200 py-2.5 px-4 text-sm outline-none focus:border-primary">
                    @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold" for="jenjang">Jenjang</label>
                        <select id="jenjang" wire:model="jenjang" class="mt-1 w-full rounded-xl border border-slate-200 py-2.5 px-4 text-sm outline-none focus:border-primary">
                            @foreach (['SD', 'SMP', 'SMA', 'SMK'] as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold" for="npsn">NPSN <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input id="npsn" type="text" wire:model="npsn" placeholder="20123456"
                               class="mt-1 w-full rounded-xl border border-slate-200 py-2.5 px-4 text-sm outline-none focus:border-primary">
                        @error('npsn') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="text-sm font-semibold" for="admin_nama">Nama Penanggung Jawab</label>
                    <input id="admin_nama" type="text" wire:model="admin_nama" placeholder="Nama kepala sekolah / operator"
                           class="mt-1 w-full rounded-xl border border-slate-200 py-2.5 px-4 text-sm outline-none focus:border-primary">
                    @error('admin_nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold" for="admin_phone">Nomor WhatsApp</label>
                    <input id="admin_phone" type="tel" wire:model="admin_phone" placeholder="6281234567890"
                           class="mt-1 w-full rounded-xl border border-slate-200 py-2.5 px-4 text-sm outline-none focus:border-primary">
                    <p class="text-xs text-slate-400 mt-1">Dipakai untuk masuk (kode OTP via WA).</p>
                    @error('admin_phone') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <button wire:click="daftar" wire:loading.attr="disabled"
                        class="w-full rounded-xl bg-primary py-3 font-semibold text-white disabled:opacity-50">
                    <span wire:loading.remove>Daftar & Mulai Trial</span>
                    <span wire:loading>Memproses…</span>
                </button>
                <p class="text-center text-sm text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Masuk</a></p>
            </div>
        </div>
    @endif
</div>
