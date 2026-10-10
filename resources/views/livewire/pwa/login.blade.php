<div class="pt-10">
    <div class="text-center mb-8">
        <span class="inline-flex w-16 h-16 rounded-2xl bg-primary items-center justify-center text-white text-3xl font-extrabold shadow">S</span>
        <h1 class="text-2xl font-extrabold mt-4">SIMS</h1>
        <p class="text-sm text-slate-500 mt-1">Portal Siswa & Orang Tua</p>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-xl bg-danger/10 text-danger px-4 py-3 text-sm font-semibold">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1 mb-6 text-sm font-semibold">
        <button type="button" id="tab-otp" class="rounded-lg py-2.5 bg-white shadow text-slate-800" onclick="pwaTab('otp')">Kode OTP</button>
        <button type="button" id="tab-qr" class="rounded-lg py-2.5 text-slate-500" onclick="pwaTab('qr')">Pindai QR</button>
    </div>

    <div id="panel-otp">
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

    <div id="panel-qr" class="hidden">
        <p class="text-sm text-slate-500 mb-3 text-center">Arahkan kamera ke kartu QR siswa.</p>
        <div id="qr-reader" class="rounded-xl overflow-hidden border border-slate-200"></div>
        <p id="qr-status" class="text-sm text-center mt-3 text-slate-500"></p>
        <button type="button" id="qr-start" onclick="pwaQrStart()"
                class="mt-3 w-full rounded-xl bg-primary py-3.5 text-white font-bold hover:bg-primary/90">
            Nyalakan Kamera
        </button>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
function pwaTab(which) {
    const otp = which === 'otp';
    document.getElementById('panel-otp').classList.toggle('hidden', !otp);
    document.getElementById('panel-qr').classList.toggle('hidden', otp);
    document.getElementById('tab-otp').className = 'rounded-lg py-2.5 ' + (otp ? 'bg-white shadow text-slate-800' : 'text-slate-500');
    document.getElementById('tab-qr').className = 'rounded-lg py-2.5 ' + (!otp ? 'bg-white shadow text-slate-800' : 'text-slate-500');
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
