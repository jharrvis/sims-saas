<div class="w-full max-w-md rounded-2xl bg-white dark:bg-boxdark shadow-2xl p-8 border border-slate-100 dark:border-slate-800">
    <div class="text-center mb-6">
        <div class="mx-auto w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mb-4">
            <i data-lucide="shield-check" class="w-7 h-7 text-primary"></i>
        </div>
        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Verifikasi Dua Faktor</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Masukkan kode dari aplikasi autentikator Anda</p>
    </div>

    @if ($error)
        <div class="mb-5 rounded-lg bg-danger/10 border border-danger/20 px-4 py-3 text-sm text-danger" role="alert">
            {{ $error }}
        </div>
    @endif

    <form wire:submit="verify">
        <div class="space-y-5">
            <input type="text" inputmode="numeric" wire:model="code" placeholder="••••••" required maxlength="8"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-3 px-4 text-center text-2xl tracking-[0.5em] outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all dark:text-white">
            <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-3 rounded-lg bg-primary text-white font-medium hover:bg-blue-700 transition-colors shadow-md disabled:opacity-60">
                Verifikasi
            </button>
            <p class="text-xs text-center text-slate-500 dark:text-slate-400">
                Kehilangan akses autentikator? Pakai salah satu kode pemulihan Anda.
            </p>
        </div>
    </form>
</div>
