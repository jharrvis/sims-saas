<div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 shrink-0 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
            <x-icon name="key-round" class="w-6 h-6" />
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Password Akun</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Status: <span class="font-semibold {{ $punyaPassword ? 'text-success' : 'text-slate-500' }}">{{ $punyaPassword ? 'Sudah diatur' : 'Belum diatur' }}</span>
            </p>
            <p class="text-xs text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                Daftarkan password sebagai cadangan bila login WhatsApp bermasalah.
                Username Anda: <span class="font-semibold font-mono text-slate-700 dark:text-slate-200">{{ auth()->user()->username }}</span>
            </p>

            @if ($sukses)
                <div class="mt-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm" role="status">{{ $sukses }}</div>
            @endif
            @if ($error)
                <div class="mt-4 rounded-lg bg-danger/10 text-danger px-4 py-2.5 text-sm" role="alert">{{ $error }}</div>
            @endif

            <form wire:submit="simpan" class="mt-4 space-y-4 max-w-md">
                <div>
                    <label for="pw-baru" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Password baru (min. 8 karakter)</label>
                    <input id="pw-baru" type="password" wire:model="password" autocomplete="new-password"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                    @error('password') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="pw-konfirmasi" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Konfirmasi password</label>
                    <input id="pw-konfirmasi" type="password" wire:model="password_confirmation" autocomplete="new-password"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors disabled:opacity-60">
                    <x-icon name="key-round" class="w-4 h-4" /> Simpan Password
                </button>
            </form>
        </div>
    </div>
</div>
