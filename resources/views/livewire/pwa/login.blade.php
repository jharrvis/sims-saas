<div class="pt-10">
    <div class="text-center mb-8">
        <span class="inline-flex w-16 h-16 rounded-2xl bg-primary items-center justify-center text-white text-3xl font-extrabold shadow">S</span>
        <h1 class="text-2xl font-extrabold mt-4">SIMS</h1>
        <p class="text-sm text-slate-500 mt-1">Portal Siswa & Orang Tua</p>
    </div>

    @if ($step === 1)
        <form wire:submit="requestOtp" class="space-y-4">
            <div>
                <label for="phone" class="block text-sm font-semibold mb-1.5">Nomor WhatsApp</label>
                <input id="phone" type="tel" wire:model="phone" placeholder="08xxxxxxxxxx" inputmode="tel"
                       class="w-full rounded-xl border border-slate-200 py-3 px-4 text-base outline-none focus:border-primary">
                @error('phone') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-slate-400 mt-1.5">Gunakan nomor WA yang terdaftar di sekolah.</p>
            </div>
            @if ($error) <p class="text-sm text-danger">{{ $error }}</p> @endif
            <button type="submit" class="w-full rounded-xl bg-primary py-3.5 text-white font-bold hover:bg-primary/90">
                Kirim Kode OTP
            </button>
        </form>
    @else
        <form wire:submit="verify" class="space-y-4">
            <div>
                <label for="code" class="block text-sm font-semibold mb-1.5">Kode OTP</label>
                <input id="code" type="text" wire:model="code" placeholder="••••••" inputmode="numeric" maxlength="6"
                       class="w-full rounded-xl border border-slate-200 py-3 px-4 text-center text-2xl font-bold tracking-[0.5em] outline-none focus:border-primary">
                @error('code') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            @if ($info) <p class="text-sm text-success">{{ $info }}</p> @endif
            @if ($error) <p class="text-sm text-danger">{{ $error }}</p> @endif
            <button type="submit" class="w-full rounded-xl bg-primary py-3.5 text-white font-bold hover:bg-primary/90">
                Masuk
            </button>
            <div class="flex items-center justify-between text-sm">
                <button type="button" wire:click="back" class="text-slate-500 font-semibold">← Ganti nomor</button>
                <button type="button" wire:click="requestOtp" class="text-primary font-semibold">Kirim ulang</button>
            </div>
        </form>
    @endif
</div>
