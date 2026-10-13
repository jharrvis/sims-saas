<div class="min-h-dvh flex flex-col items-center justify-center px-6 py-10">
    <div class="w-full max-w-sm">
        {{-- Logo (design pwa/login.html) --}}
        <div class="text-center mb-10">
            <div class="w-20 h-20 rounded-2xl bg-[#3c50e0] flex items-center justify-center mx-auto mb-4 shadow-lg">
                <x-icon name="graduation-cap" class="w-10 h-10 text-white" />
            </div>
            <h1 class="text-2xl font-extrabold text-slate-800">SIMS</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem Informasi Manajemen Sekolah</p>
        </div>

        @if (session('error'))
            <div class="mb-4 rounded-xl bg-danger/10 text-danger px-4 py-3 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        {{-- Tab Selector (design) — OTP / Pindai QR (fitur aplikasi) --}}
        <div class="flex bg-slate-100 rounded-xl p-1 mb-6">
            <button type="button" id="tab-otp" class="flex-1 py-2 rounded-lg text-sm transition-all bg-white shadow-sm text-[#3c50e0] font-bold" onclick="pwaTab('otp')">Kode OTP</button>
            <button type="button" id="tab-qr" class="flex-1 py-2 rounded-lg text-sm transition-all text-slate-500" onclick="pwaTab('qr')">Pindai QR</button>
        </div>

        <div id="panel-otp">
        @if ($step === 1)
            <form wire:submit="requestOtp" class="space-y-4 slide-up">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor WhatsApp</label>
                    <input id="phone" type="tel" wire:model="phone" placeholder="08xxxxxxxxxx" inputmode="tel"
                           class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm outline-none focus:border-[#3c50e0] focus:ring-1 focus:ring-[#3c50e0]">
                    @error('phone') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 mt-1.5">Gunakan nomor WA yang terdaftar di sekolah.</p>
                </div>
                @if ($error) <p class="text-sm text-danger">{{ $error }}</p> @endif
                <button type="submit" class="w-full py-3.5 bg-[#3c50e0] text-white font-bold rounded-xl hover:bg-[#2a3cb0] transition-colors text-sm shadow-lg shadow-[#3c50e0]/30">
                    Kirim Kode OTP
                </button>
                <p class="text-xs text-center text-slate-400">Kode verifikasi akan dikirim melalui WhatsApp</p>
            </form>
        @else
            <form wire:submit="verify" class="space-y-4 slide-up">
                <div>
                    <label for="code" class="block text-sm font-semibold text-slate-700 mb-1.5">Kode OTP</label>
                    <input id="code" type="text" wire:model="code" placeholder="••••••" inputmode="numeric" maxlength="6"
                           class="w-full border border-slate-200 rounded-xl py-3 px-4 text-center text-2xl font-bold tracking-[0.5em] outline-none focus:border-[#3c50e0] focus:ring-1 focus:ring-[#3c50e0]">
                    @error('code') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                @if ($info) <p class="text-sm text-success">{{ $info }}</p> @endif
                @if ($error) <p class="text-sm text-danger">{{ $error }}</p> @endif
                <button type="submit" class="w-full py-3.5 bg-[#3c50e0] text-white font-bold rounded-xl hover:bg-[#2a3cb0] transition-colors text-sm shadow-lg shadow-[#3c50e0]/30">
                    Masuk
                </button>
                <div class="flex items-center justify-between text-sm">
                    <button type="button" wire:click="back" class="text-slate-500 font-semibold">← Ganti nomor</button>
                    <button type="button" wire:click="requestOtp" class="text-primary font-semibold">Kirim ulang</button>
                </div>
            </form>
        @endif
        </div>

        <div id="panel-qr" class="hidden">
            <p class="text-sm text-slate-500 mb-3 text-center">Arahkan kamera ke kartu QR siswa.</p>
            <div id="qr-reader" class="rounded-xl overflow-hidden border border-slate-200"></div>
            <p id="qr-status" class="text-sm text-center mt-3 text-slate-500"></p>
            <button type="button" id="qr-start" onclick="pwaQrStart()"
                    class="mt-3 w-full py-3.5 bg-[#3c50e0] text-white font-bold rounded-xl hover:bg-[#2a3cb0] transition-colors text-sm shadow-lg shadow-[#3c50e0]/30">
                Nyalakan Kamera
            </button>
        </div>
    </div>
    <p class="text-xs text-slate-400 mt-10 text-center">SIMS &bull; Untuk pendidikan Indonesia</p>
</div>

@vite('resources/js/pwa-qr.js')
<script>
function pwaTab(which) {
    const otp = which === 'otp';
    document.getElementById('panel-otp').classList.toggle('hidden', !otp);
    document.getElementById('panel-qr').classList.toggle('hidden', otp);
    document.getElementById('tab-otp').className = 'flex-1 py-2 rounded-lg text-sm transition-all ' + (otp ? 'bg-white shadow-sm text-[#3c50e0] font-bold' : 'text-slate-500');
    document.getElementById('tab-qr').className = 'flex-1 py-2 rounded-lg text-sm transition-all ' + (!otp ? 'bg-white shadow-sm text-[#3c50e0] font-bold' : 'text-slate-500');
    if (!otp) pwaQrStop(); else pwaQrStop();
}
let pwaQrScanner = null;
function pwaQrStart() {
    const status = document.getElementById('qr-status');
    if (typeof Html5Qrcode === 'undefined') { status.textContent = 'Pustaka pemindai gagal dimuat.'; return; }
    pwaQrScanner = new Html5Qrcode('qr-reader');
    document.getElementById('qr-start').classList.add('hidden');
    status.textContent = 'Menyalakan kamera…';
    pwaQrScanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: 220 },
        (token) => {
            pwaQrStop();
            status.textContent = 'Memverifikasi…';
            fetch(@json(route('pwa.qr.redeem')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ token: token.trim() }),
            }).then(async (r) => {
                const data = await r.json();
                if (r.ok && data.ok) { window.location.href = data.redirect; return; }
                status.textContent = (data.errors && data.errors.token ? data.errors.token[0] : null) || data.message || 'Kode QR tidak valid.';
                document.getElementById('qr-start').classList.remove('hidden');
            }).catch(() => {
                status.textContent = 'Jaringan bermasalah. Coba lagi.';
                document.getElementById('qr-start').classList.remove('hidden');
            });
        },
        () => {}
    ).catch(() => {
        status.textContent = 'Kamera tidak dapat diakses. Izinkan akses kamera lalu coba lagi.';
        document.getElementById('qr-start').classList.remove('hidden');
    });
}
function pwaQrStop() {
    if (pwaQrScanner) { try { pwaQrScanner.stop(); pwaQrScanner.clear(); } catch (e) {} pwaQrScanner = null; }
}
</script>
