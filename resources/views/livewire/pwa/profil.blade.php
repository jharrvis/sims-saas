<div>
    <div class="rounded-2xl bg-slate-50 p-4 mb-4">
        <p class="font-bold">{{ $user->name }}</p>
        <p class="text-xs text-slate-500 mt-0.5">{{ $user->phone }}</p>
        <p class="text-[11px] text-slate-400 mt-1">
            {{ $role === 'orang-tua' ? 'Orang Tua/Wali' : 'Siswa' }}
            @if ($student) · {{ $student->nama }} @endif
        </p>
    </div>

    @if ($role === 'orang-tua' && $children->count() > 1)
        <h3 class="font-bold text-sm mb-2">Mode Profil Anak</h3>
        <div class="space-y-2 mb-4">
            @foreach ($children as $child)
                <form method="POST" action="{{ route('pwa.switch-child') }}">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $child->id }}">
                    <button class="w-full flex items-center justify-between rounded-xl border px-4 py-3 text-left {{ $child->id === $student?->id ? 'border-primary bg-primary/5' : 'border-slate-200' }}">
                        <span class="text-sm font-semibold">{{ $child->nama }}</span>
                        @if ($child->id === $student?->id)
                            <span class="text-[10px] font-bold text-primary">AKTIF</span>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
        <p class="text-[11px] text-slate-400 mb-4">Mode anak bersifat read-only dan tercatat atas nama akun Anda.</p>
    @endif

    <h3 class="font-bold text-sm mb-2">Perangkat Aktif</h3>
    @forelse ($tokens as $t)
        <div class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 mb-1.5">
            <div>
                <p class="text-sm font-semibold">{{ $t->name }}</p>
                <p class="text-[11px] text-slate-400">
                    Terakhir dipakai {{ $t->last_used_at?->diffForHumans() ?? 'belum pernah' }}
                </p>
            </div>
            <button wire:click="revokeToken({{ $t->id }})" class="text-xs font-bold text-danger">Cabut</button>
        </div>
    @empty
        <p class="text-xs text-slate-400 italic mb-4">Tidak ada token API aktif.</p>
    @endforelse

    <h3 class="font-bold text-sm mb-2">Notifikasi</h3>
    <div class="rounded-xl border border-slate-100 px-4 py-3 mb-4">
        <p id="push-status" class="text-xs text-slate-500">Memeriksa status notifikasi…</p>
        <div class="flex gap-2 mt-2">
            <button id="push-enable" onclick="pwaPushEnable()"
                    class="hidden flex-1 rounded-lg bg-primary py-2.5 text-white text-sm font-bold">Aktifkan Notifikasi</button>
            <button id="push-disable" onclick="pwaPushDisable()"
                    class="hidden flex-1 rounded-lg border border-slate-200 py-2.5 text-sm font-bold text-slate-600">Matikan Notifikasi</button>
        </div>
        <p class="text-[11px] text-slate-400 mt-2">Dapatkan kabar: pengumuman penting, nilai dirilis, tagihan, dan kehadiran anak.</p>
    </div>

    <button wire:click="logout"
            class="w-full mt-4 rounded-xl border border-danger/30 text-danger py-3 font-bold text-sm">
        Keluar
    </button>
</div>

<script>
function pwaPushBase64ToUint8(base64) {
    const pad = '='.repeat((4 - (base64.length % 4)) % 4);
    const b64 = (base64 + pad).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(b64);
    const out = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; i++) out[i] = raw.charCodeAt(i);
    return out;
}
async function pwaPushState() {
    const status = document.getElementById('push-status');
    const on = document.getElementById('push-enable');
    const off = document.getElementById('push-disable');
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        status.textContent = 'Perangkat ini tidak mendukung notifikasi push.';
        return;
    }
    try {
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.getSubscription();
        if (sub) {
            status.textContent = 'Notifikasi aktif di perangkat ini.';
            on.classList.add('hidden'); off.classList.remove('hidden');
        } else {
            status.textContent = Notification.permission === 'denied'
                ? 'Izin notifikasi diblokir di pengaturan browser.'
                : 'Notifikasi belum aktif.';
            on.classList.remove('hidden'); off.classList.add('hidden');
        }
    } catch (e) { status.textContent = 'Tidak dapat memeriksa status notifikasi.'; }
}
async function pwaPushEnable() {
    const status = document.getElementById('push-status');
    try {
        const perm = await Notification.requestPermission();
        if (perm !== 'granted') { status.textContent = 'Izin notifikasi tidak diberikan.'; return; }
        const keyRes = await fetch(@json(route('pwa.push.vapid-key')));
        const { key } = await keyRes.json();
        if (!key) { status.textContent = 'Layanan push belum dikonfigurasi.'; return; }
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: pwaPushBase64ToUint8(key) });
        const json = sub.toJSON();
        const csrf = document.querySelector('meta[name=csrf-token]').content;
        const res = await fetch(@json(route('pwa.push.subscribe')), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ endpoint: sub.endpoint, keys: json.keys }),
        });
        status.textContent = res.ok ? 'Notifikasi berhasil diaktifkan.' : 'Gagal mendaftarkan perangkat.';
    } catch (e) { status.textContent = 'Gagal mengaktifkan notifikasi.'; }
    pwaPushState();
}
async function pwaPushDisable() {
    const status = document.getElementById('push-status');
    try {
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.getSubscription();
        if (sub) {
            const endpoint = sub.endpoint;
            await sub.unsubscribe();
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            await fetch(@json(route('pwa.push.unsubscribe')), {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ endpoint }),
            });
        }
        status.textContent = 'Notifikasi dimatikan.';
    } catch (e) { status.textContent = 'Gagal mematikan notifikasi.'; }
    pwaPushState();
}
document.addEventListener('DOMContentLoaded', pwaPushState);
</script>
