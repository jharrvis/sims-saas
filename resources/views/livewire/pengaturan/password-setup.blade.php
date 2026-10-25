<section class="card" aria-labelledby="password-setup-title">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 shrink-0 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
            <x-icon name="key-round" class="w-6 h-6" />
        </div>
        <div class="flex-1 min-w-0">
            <h2 id="password-setup-title" class="text-base font-bold">Password Akun</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Status: <span class="font-semibold {{ $punyaPassword ? 'text-success' : 'text-slate-500' }}">{{ $punyaPassword ? 'Sudah diatur' : 'Belum diatur' }}</span>
            </p>
            <p class="text-xs text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                Daftarkan password sebagai cadangan bila login WhatsApp bermasalah.
                Username Anda: <span class="font-semibold font-mono text-slate-700 dark:text-slate-200">{{ auth()->user()->username }}</span>
            </p>

            @if ($sukses)
                <div class="management-alert management-alert-success mt-4" role="status" aria-live="polite">
                    <span>{{ $sukses }}</span>
                </div>
            @endif
            @if ($error)
                <div class="rounded-xl border border-danger/30 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-danger mt-4" role="alert">{{ $error }}</div>
            @endif

            <form wire:submit="simpan" class="mt-4 space-y-4 max-w-md">
                <div class="form-group">
                    <label for="pw-baru">Password baru (min. 8 karakter)</label>
                    <input id="pw-baru" type="password" wire:model="password" autocomplete="new-password" class="field">
                    @error('password') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="pw-konfirmasi">Konfirmasi password</label>
                    <input id="pw-konfirmasi" type="password" wire:model="password_confirmation" autocomplete="new-password" class="field">
                </div>
                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary" wire:target="simpan">
                    <x-icon name="key-round" class="w-4 h-4" />
                    <span wire:loading.remove wire:target="simpan">Simpan Password</span>
                    <span wire:loading wire:target="simpan">Menyimpan...</span>
                </button>
            </form>
        </div>
    </div>
</section>
