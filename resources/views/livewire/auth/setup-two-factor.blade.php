<div class="max-w-xl mx-auto">
    <div class="rounded-2xl bg-white dark:bg-boxdark border border-slate-200 dark:border-slate-700 p-6 sm:p-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-800 dark:text-white">Autentikasi Dua Faktor (2FA)</h1>
                <p class="text-xs text-slate-500">Opsional — amankan akun Anda</p>
            </div>
        </div>

        @if ($error)
            <div class="mb-5 rounded-lg bg-danger/10 border border-danger/20 px-4 py-3 text-sm text-danger" role="alert">{{ $error }}</div>
        @endif

        @if ($success)
            <div class="rounded-lg bg-success/10 border border-success/20 px-4 py-3 text-sm text-success" role="status">{{ $success }}</div>
            <div class="pt-5">
                <a href="{{ route('pengaturan.keamanan') }}" class="block w-full py-3 text-center bg-primary text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-colors">
                    Selesai
                </a>
            </div>
        @else
            <div class="space-y-4">
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    1. Pindai kode QR berikut menggunakan aplikasi autentikator (Google Authenticator, Microsoft Authenticator, atau 1Password).
                </p>

                <div class="bg-slate-50 dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center">
                    <div class="w-44 h-44 bg-white p-2 border-2 border-slate-800 rounded-xl flex items-center justify-center overflow-hidden">
                        {!! $qrSvg !!}
                    </div>
                    <p class="text-[11px] text-slate-400 mt-3 font-mono">Kode rahasia: {{ $secret }}</p>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300">
                    2. Masukkan 6 digit kode yang muncul di aplikasi Anda:
                </p>

                <form wire:submit="confirm" class="space-y-4">
                    <input type="text" inputmode="numeric" wire:model="code" placeholder="••••••" required maxlength="6"
                           class="w-full max-w-xs mx-auto block rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent py-3 px-4 text-center text-2xl tracking-[0.5em] outline-none focus:border-primary dark:text-white">
                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('pengaturan.keamanan') }}" class="flex-1 py-3 text-center border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Batal</a>
                        <button type="submit" wire:loading.attr="disabled" class="flex-1 py-3 bg-primary text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-colors disabled:opacity-60">
                            Verifikasi & Aktifkan
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @if ($recoveryCodes)
            <div class="mt-6 rounded-2xl border border-warning/30 bg-warning/5 p-5 space-y-3" role="note" aria-label="Kode pemulihan">
                <div class="flex items-center gap-2">
                    <i data-lucide="key-round" class="w-4 h-4 text-warning"></i>
                    <h2 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wide">Kode Pemulihan (Recovery Codes)</h2>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                    Simpan 8 kode berikut di tempat aman. Setiap kode hanya berlaku <strong>satu kali</strong> — dipakai saat Anda kehilangan akses ke aplikasi autentikator.
                </p>
                <div class="grid grid-cols-2 gap-2 font-mono text-xs">
                    @foreach ($recoveryCodes as $rc)
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-center tracking-widest">{{ $rc }}</div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
