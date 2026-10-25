<div class="auth-platform">
    <span class="eyebrow">Portal Platform</span>
    <h1>Masuk ke Panel Platform</h1>
    <p>Khusus operator SIMS. Akun sekolah tidak dapat masuk melalui pintu ini.</p>

    @if ($error)
        <div class="auth-alert auth-alert-error" role="alert">{{ $error }}</div>
    @endif

    @if ($info)
        <div class="auth-alert auth-alert-info" role="status">{{ $info }}</div>
    @endif

    <div class="method-tabs auth-tabs two" role="tablist" aria-label="Metode masuk platform">
        <button type="button" class="method-tab {{ $tab === 'wa' ? 'active' : '' }}" role="tab"
                aria-selected="{{ $tab === 'wa' ? 'true' : 'false' }}" wire:click="$set('tab', 'wa')">
            WhatsApp OTP
        </button>
        <button type="button" class="method-tab {{ $tab === 'password' ? 'active' : '' }}" role="tab"
                aria-selected="{{ $tab === 'password' ? 'true' : 'false' }}" wire:click="$set('tab', 'password')">
            Username + Password
        </button>
    </div>

    <div class="auth-card">
        @if ($tab === 'wa')
            @if ($step === 1)
                <form wire:submit="requestOtp" class="space-y-4">
                    <div class="field-group">
                        <label for="platform-phone">Nomor WhatsApp</label>
                        <div class="phone-field">
                            <span>+62</span>
                            <input id="platform-phone" type="tel" wire:model="phone" placeholder="812 3456 7890"
                                   inputmode="tel" autocomplete="tel" required>
                        </div>
                        @error('phone') <small class="sims-error">{{ $message }}</small> @enderror
                        <small>Kode 6 digit dikirim melalui WhatsApp dan berlaku 5 menit.</small>
                    </div>

                    <button type="submit" class="sims-btn sims-btn-primary sims-btn-lg w-full" wire:loading.attr="disabled">
                        <span wire:loading.remove>Kirim Kode OTP</span>
                        <span wire:loading>Mengirim...</span>
                    </button>
                </form>
            @else
                <form wire:submit="verify" class="space-y-4">
                    <div class="field-group">
                        <label for="platform-code">Kode OTP <span class="font-normal text-slate-400">— dikirim ke {{ $phone }}</span></label>
                        <input id="platform-code" type="text" wire:model="code" placeholder="000000" inputmode="numeric"
                               autocomplete="one-time-code" maxlength="6" required class="field auth-otp">
                        @error('code') <small class="sims-error">{{ $message }}</small> @enderror
                    </div>

                    <button type="submit" class="sims-btn sims-btn-primary sims-btn-lg w-full" wire:loading.attr="disabled">
                        <span wire:loading.remove>Masuk</span>
                        <span wire:loading>Memverifikasi...</span>
                    </button>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <button type="button" wire:click="back" class="font-bold text-slate-500 hover:text-slate-700">Ganti nomor</button>
                        <button type="button" wire:click="requestOtp" class="font-bold text-primary">Kirim ulang kode</button>
                    </div>
                </form>
            @endif
        @else
            <form wire:submit="loginPassword" class="space-y-4" x-data="{ show: false }">
                <div class="field-group">
                    <label for="platform-username">Username</label>
                    <input id="platform-username" type="text" wire:model="username" placeholder="Username operator"
                           autocomplete="username" required class="field">
                    @error('username') <small class="sims-error">{{ $message }}</small> @enderror
                </div>

                <div class="field-group">
                    <label for="platform-password">Password</label>
                    <div class="password-field">
                        <input id="platform-password" :type="show ? 'text' : 'password'" wire:model="password"
                               placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" @click="show = !show" :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                            <x-icon name="eye" class="h-4 w-4" x-show="!show" />
                            <x-icon name="eye-off" class="h-4 w-4" x-show="show" x-cloak />
                        </button>
                    </div>
                    @error('password') <small class="sims-error">{{ $message }}</small> @enderror
                </div>

                <button type="submit" class="sims-btn sims-btn-primary sims-btn-lg w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Masuk</span>
                    <span wire:loading>Memverifikasi...</span>
                </button>
            </form>
        @endif
    </div>

    <div class="auth-trust">
        <span class="sims-icon-box"><x-icon name="shield-check" class="h-4 w-4" /></span>
        <div>
            <strong>Akses operator terbatas</strong>
            <small>Setiap aktivitas login platform dicatat dan diaudit.</small>
        </div>
    </div>

    <div class="auth-footer-link">
        Bukan operator platform? <a href="{{ route('login') }}">Kembali ke portal sekolah</a>
    </div>

    <div class="auth-note">
        <x-icon name="lock" class="h-4 w-4" />
        <span>Jangan bagikan kode OTP atau password operator kepada siapa pun.</span>
    </div>
</div>
