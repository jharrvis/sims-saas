<div>
<div class="auth-card">
    @if (session('error'))
        <div class="auth-alert auth-alert-error" role="alert">{{ session('error') }}</div>
    @endif

    @if ($error)
        <div class="auth-alert auth-alert-error" role="alert">{{ $error }}</div>
    @endif

    @if ($info)
        <div class="auth-alert auth-alert-info" role="status">{{ $info }}</div>
    @endif

    <div class="method-tabs auth-tabs three" role="tablist" aria-label="Metode masuk PWA">
        <button type="button" class="method-tab {{ $tab === 'otp' ? 'active' : '' }}" role="tab"
                aria-selected="{{ $tab === 'otp' ? 'true' : 'false' }}"
                wire:click="$set('tab', 'otp')" onclick="typeof pwaQrStop === 'function' && pwaQrStop()">OTP</button>
        <button type="button" class="method-tab {{ $tab === 'qr' ? 'active' : '' }}" role="tab"
                aria-selected="{{ $tab === 'qr' ? 'true' : 'false' }}"
                wire:click="$set('tab', 'qr')">Pindai QR</button>
        <button type="button" class="method-tab {{ $tab === 'password' ? 'active' : '' }}" role="tab"
                aria-selected="{{ $tab === 'password' ? 'true' : 'false' }}"
                wire:click="$set('tab', 'password')" onclick="typeof pwaQrStop === 'function' && pwaQrStop()">Password</button>
    </div>

    @if ($tab === 'otp')
        @if ($step === 1)
            <form wire:submit="requestOtp" class="space-y-4">
                <div class="field-group">
                    <label for="phone">Nomor WhatsApp</label>
                    <div class="phone-field">
                        <span>+62</span>
                        <input id="phone" type="tel" wire:model="phone" placeholder="812 3456 7890"
                               inputmode="tel" autocomplete="tel" required>
                    </div>
                    @error('phone') <small class="sims-error">{{ $message }}</small> @enderror
                    <small>Gunakan nomor WA yang terdaftar di sekolah.</small>
                </div>

                <button type="submit" class="sims-btn sims-btn-primary sims-btn-lg w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Kirim Kode OTP</span>
                    <span wire:loading>Mengirim...</span>
                </button>
                <p class="text-center text-[11px] text-slate-500">Kode verifikasi dikirim melalui WhatsApp dan berlaku 5 menit.</p>
            </form>
        @else
            <form wire:submit="verify" class="space-y-4">
                <div class="field-group">
                    <label for="code">Kode OTP <span class="font-normal text-slate-400">— {{ $phone }}</span></label>
                    <input id="code" type="text" wire:model="code" placeholder="000000" inputmode="numeric"
                           autocomplete="one-time-code" maxlength="6" required class="field auth-otp">
                    @error('code') <small class="sims-error">{{ $message }}</small> @enderror
                </div>

                <button type="submit" class="sims-btn sims-btn-primary sims-btn-lg w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Masuk</span>
                    <span wire:loading>Memverifikasi...</span>
                </button>

                <div class="flex items-center justify-between gap-3 text-xs">
                    <button type="button" wire:click="back" class="font-bold text-slate-500">Ganti nomor</button>
                    <button type="button" wire:click="requestOtp" class="font-bold text-primary">Kirim ulang</button>
                </div>
            </form>
        @endif
    @elseif ($tab === 'password')
        <form wire:submit="loginPassword" class="space-y-4" x-data="{ show: false }">
            <div class="field-group">
                <label for="pwa-username">Username atau nomor WA</label>
                <input id="pwa-username" type="text" wire:model="username" placeholder="Username atau 08xxxxxxxxxx"
                       autocomplete="username" required class="field">
                @error('username') <small class="sims-error">{{ $message }}</small> @enderror
            </div>

            <div class="field-group">
                <label for="pwa-password">Password</label>
                <div class="password-field">
                    <input id="pwa-password" :type="show ? 'text' : 'password'" wire:model="password"
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
            <p class="text-center text-[11px] leading-5 text-slate-500">
                Atur password di <strong>Profil → Keamanan Akun</strong> saat WhatsApp masih berfungsi.
            </p>
        </form>
    @else
        <div wire:ignore>
            <p class="mb-3 text-center text-[11px] text-slate-500">Arahkan kamera ke kartu QR siswa atau kartu identitas sekolah.</p>
            <div id="qr-reader" class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50"></div>
            <p id="qr-status" class="mt-3 text-center text-xs text-slate-500"></p>
            <button type="button" id="qr-start" onclick="pwaQrStart()"
                    class="sims-btn sims-btn-primary sims-btn-lg mt-3 w-full">
                Nyalakan Kamera
            </button>
        </div>
    @endif

    <div class="auth-note">
        <x-icon name="shield-check" class="h-4 w-4" />
        <span>Jangan bagikan kode OTP atau password. SIMS tidak pernah memintanya melalui telepon.</span>
    </div>
</div>

<div class="auth-footer-link">
    Guru atau staf sekolah? <a href="{{ route('login') }}">Masuk portal desktop</a>
</div>

@vite('resources/js/pwa-qr.js')
<script>
    let pwaQrScanner = null;

    function pwaQrStart() {
        const status = document.getElementById('qr-status');
        const start = document.getElementById('qr-start');
        if (!status || !start) return;

        if (typeof Html5Qrcode === 'undefined') {
            status.textContent = 'Pustaka pemindai gagal dimuat.';
            return;
        }

        start.classList.add('hidden');
        status.textContent = 'Menyalakan kamera...';
        pwaQrScanner = new Html5Qrcode('qr-reader');

        pwaQrScanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: 220 },
            (token) => {
                pwaQrStop();
                status.textContent = 'Memverifikasi...';
                fetch(@json(route('pwa.qr.redeem')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ token: token.trim() }),
                }).then(async (response) => {
                    const data = await response.json();
                    if (response.ok && data.ok) {
                        window.location.href = data.redirect;
                        return;
                    }
                    status.textContent = (data.errors && data.errors.token ? data.errors.token[0] : null)
                        || data.message
                        || 'Kode QR tidak valid.';
                    start.classList.remove('hidden');
                }).catch(() => {
                    status.textContent = 'Jaringan bermasalah. Coba lagi.';
                    start.classList.remove('hidden');
                });
            },
            () => {}
        ).catch(() => {
            status.textContent = 'Kamera tidak dapat diakses. Izinkan akses kamera lalu coba lagi.';
            start.classList.remove('hidden');
        });
    }

    function pwaQrStop() {
        if (pwaQrScanner) {
            try {
                pwaQrScanner.stop();
                pwaQrScanner.clear();
            } catch (error) {
                // Scanner may already be stopped by the browser.
            }
            pwaQrScanner = null;
        }
    }
</script>
</div>
