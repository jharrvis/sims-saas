<div class="w-full max-w-lg rounded-2xl bg-white dark:bg-boxdark shadow-2xl p-8 sm:p-10 border border-slate-100 dark:border-slate-800">
    <div class="text-center mb-8">
        <div class="mx-auto w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
            <i data-lucide="graduation-cap" class="w-8 h-8 text-primary"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">SIMS</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Masuk ke sistem manajemen sekolah</p>
    </div>

    @if ($error)
        <div class="mb-5 rounded-lg bg-danger/10 border border-danger/20 px-4 py-3 text-sm text-danger" role="alert">
            {{ $error }}
        </div>
    @endif

    @if ($info)
        <div class="mb-5 rounded-lg bg-success/10 border border-success/20 px-4 py-3 text-sm text-success" role="status">
            {{ $info }}
        </div>
    @endif

    @if ($step === 1)
        <form wire:submit="requestOtp">
            <div class="space-y-5">
                <div>
                    <label for="phone" class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">Nomor WhatsApp</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i data-lucide="smartphone" class="w-5 h-5"></i>
                        </span>
                        <input id="phone" type="tel" wire:model="phone" placeholder="08xxxxxxxxxx" required
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-3 pl-12 pr-4 outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all dark:text-white">
                    </div>
                    @error('phone') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <button type="submit" wire:loading.attr="disabled"
                        class="w-full py-3 rounded-lg bg-primary text-white font-medium hover:bg-blue-700 transition-colors shadow-md disabled:opacity-60">
                    <span wire:loading.remove>Kirim Kode OTP</span>
                    <span wire:loading>Mengirim…</span>
                </button>
                <p class="text-xs text-center text-slate-500 dark:text-slate-400">
                    Kode 6 digit dikirim via WhatsApp dan berlaku 5 menit.
                </p>
            </div>
        </form>
    @else
        <form wire:submit="verify">
            <div class="space-y-5">
                <div>
                    <label for="code" class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                        Kode OTP <span class="font-normal text-slate-500">— dikirim ke {{ $phone }}</span>
                    </label>
                    <input id="code" type="text" inputmode="numeric" wire:model="code" placeholder="••••••" required maxlength="6"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-3 px-4 text-center text-2xl tracking-[0.5em] outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all dark:text-white">
                    @error('code') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <button type="submit" wire:loading.attr="disabled"
                        class="w-full py-3 rounded-lg bg-primary text-white font-medium hover:bg-blue-700 transition-colors shadow-md disabled:opacity-60">
                    <span wire:loading.remove>Masuk</span>
                    <span wire:loading>Memverifikasi…</span>
                </button>
                <div class="flex items-center justify-between text-sm">
                    <button type="button" wire:click="back" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                        ← Ganti nomor
                    </button>
                    <button type="button" wire:click="requestOtp" class="text-primary hover:underline font-medium">
                        Kirim ulang kode
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
