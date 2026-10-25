/**
 * Production PWA shell behaviour. Server-rendered identity, permissions, and
 * data remain authoritative; this module only owns browser capabilities.
 */

let deferredInstallPrompt = null;
let waitingServiceWorker = null;
let isRefreshing = false;

const notify = (detail) => window.dispatchEvent(new CustomEvent('sims-toast', { detail }));

const setHidden = (element, hidden) => {
    if (element) element.hidden = hidden;
};

const syncNetworkState = () => {
    const online = navigator.onLine;

    document.querySelectorAll('[data-pwa-offline]').forEach((banner) => setHidden(banner, online));
    document.querySelectorAll('[data-network-status]').forEach((badge) => {
        badge.textContent = online ? 'Online' : 'Offline';
        badge.classList.toggle('offline', !online);
        badge.classList.toggle('is-offline', !online);
    });

    if (online && document.body.matches('[data-pwa-offline-page]')) window.location.reload();
};

const installButtons = () => document.querySelectorAll('[data-pwa-install]');

const syncInstallButtons = () => {
    installButtons().forEach((button) => setHidden(button, deferredInstallPrompt === null));
};

const promptInstall = async () => {
    if (!deferredInstallPrompt) return;

    deferredInstallPrompt.prompt();
    const { outcome } = await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;
    syncInstallButtons();

    if (outcome === 'accepted') {
        notify({ type: 'success', title: 'Aplikasi dipasang', message: 'SIMS sedang ditambahkan ke perangkat Anda.' });
    }
};

const showUpdatePrompt = (worker) => {
    waitingServiceWorker = worker;
    setHidden(document.querySelector('[data-pwa-update]'), false);
};

const registerServiceWorker = async () => {
    const shell = document.querySelector('[data-pwa-shell]');
    const serviceWorkerUrl = shell?.dataset.serviceWorkerUrl;

    if (!serviceWorkerUrl || !('serviceWorker' in navigator)) return;

    try {
        const registration = await navigator.serviceWorker.register(serviceWorkerUrl, { scope: '/pwa' });

        if (registration.waiting && navigator.serviceWorker.controller) showUpdatePrompt(registration.waiting);

        registration.addEventListener('updatefound', () => {
            const installing = registration.installing;
            if (!installing) return;

            installing.addEventListener('statechange', () => {
                if (installing.state === 'installed' && navigator.serviceWorker.controller) showUpdatePrompt(installing);
            });
        });
    } catch {
        notify({ type: 'warning', title: 'Mode offline tidak tersedia', message: 'Aplikasi tetap dapat digunakan selama perangkat terhubung.' });
    }
};

const base64ToUint8Array = (base64) => {
    const padding = '='.repeat((4 - (base64.length % 4)) % 4);
    const value = (base64 + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = window.atob(value);

    return Uint8Array.from(raw, (character) => character.charCodeAt(0));
};

const readyServiceWorker = () => Promise.race([
    navigator.serviceWorker.ready,
    new Promise((resolve, reject) => {
        window.setTimeout(() => reject(new Error('service-worker-timeout')), 8000);
    }),
]);

const setPushBusy = (container, busy) => {
    container.setAttribute('aria-busy', busy ? 'true' : 'false');
    container.querySelectorAll('[data-pwa-push-enable], [data-pwa-push-disable]').forEach((button) => {
        button.disabled = busy;
    });
};

const syncPushState = async () => {
    const container = document.querySelector('[data-pwa-push]');
    if (!container) return;

    const status = container.querySelector('[data-pwa-push-status]');
    const enable = container.querySelector('[data-pwa-push-enable]');
    const disable = container.querySelector('[data-pwa-push-disable]');

    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        status.textContent = 'Perangkat ini tidak mendukung notifikasi push.';
        setHidden(enable, true);
        setHidden(disable, true);
        return;
    }

    try {
        const registration = await readyServiceWorker();
        const subscription = await registration.pushManager.getSubscription();
        const isEnabled = subscription !== null;

        status.textContent = isEnabled
            ? 'Notifikasi aktif di perangkat ini.'
            : Notification.permission === 'denied'
                ? 'Izin notifikasi diblokir melalui pengaturan browser.'
                : 'Notifikasi belum aktif.';
        setHidden(enable, isEnabled || Notification.permission === 'denied');
        setHidden(disable, !isEnabled);
    } catch {
        status.textContent = 'Status notifikasi tidak dapat diperiksa saat ini.';
    }
};

const enablePush = async (container) => {
    const status = container.querySelector('[data-pwa-push-status]');
    setPushBusy(container, true);
    status.textContent = 'Mengaktifkan notifikasi...';

    try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            status.textContent = 'Izin notifikasi tidak diberikan.';
            return;
        }

        const keyResponse = await fetch(container.dataset.vapidUrl, { headers: { Accept: 'application/json' } });
        if (!keyResponse.ok) throw new Error('vapid-key');

        const { key } = await keyResponse.json();
        if (!key) {
            status.textContent = 'Layanan notifikasi belum dikonfigurasi sekolah.';
            return;
        }

        const registration = await readyServiceWorker();
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: base64ToUint8Array(key),
        });
        const payload = subscription.toJSON();
        const response = await fetch(container.dataset.subscribeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ endpoint: subscription.endpoint, keys: payload.keys }),
        });

        if (!response.ok) {
            await subscription.unsubscribe();
            throw new Error('subscribe');
        }

        notify({ type: 'success', title: 'Notifikasi aktif', message: 'Perangkat ini akan menerima informasi penting dari sekolah.' });
    } catch {
        status.textContent = 'Notifikasi gagal diaktifkan. Silakan coba lagi.';
    } finally {
        setPushBusy(container, false);
        await syncPushState();
    }
};

const disablePush = async (container) => {
    const status = container.querySelector('[data-pwa-push-status]');
    setPushBusy(container, true);
    status.textContent = 'Mematikan notifikasi...';

    try {
        const registration = await readyServiceWorker();
        const subscription = await registration.pushManager.getSubscription();

        if (subscription) {
            const response = await fetch(container.dataset.unsubscribeUrl, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ endpoint: subscription.endpoint }),
            });

            if (!response.ok) throw new Error('unsubscribe');
            await subscription.unsubscribe();
        }

        notify({ type: 'success', title: 'Notifikasi dimatikan', message: 'Perangkat ini tidak lagi menerima notifikasi push.' });
    } catch {
        status.textContent = 'Notifikasi gagal dimatikan. Silakan coba lagi.';
    } finally {
        setPushBusy(container, false);
        await syncPushState();
    }
};

document.addEventListener('click', async (event) => {
    if (event.target.closest('[data-pwa-install]')) {
        await promptInstall();
        return;
    }

    if (event.target.closest('[data-pwa-retry]')) {
        window.location.reload();
        return;
    }

    const update = event.target.closest('[data-pwa-update-action]');
    if (update && waitingServiceWorker) {
        update.disabled = true;
        waitingServiceWorker.postMessage({ type: 'SKIP_WAITING' });
        return;
    }

    if (event.target.closest('[data-pwa-update-dismiss]')) {
        setHidden(document.querySelector('[data-pwa-update]'), true);
        return;
    }

    const pushContainer = event.target.closest('[data-pwa-push]');
    if (pushContainer && event.target.closest('[data-pwa-push-enable]')) await enablePush(pushContainer);
    if (pushContainer && event.target.closest('[data-pwa-push-disable]')) await disablePush(pushContainer);
});

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    syncInstallButtons();
});

window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    syncInstallButtons();
});

window.addEventListener('online', syncNetworkState);
window.addEventListener('offline', syncNetworkState);

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (isRefreshing) return;
        isRefreshing = true;
        window.location.reload();
    });
}

const initPwa = () => {
    syncNetworkState();
    syncInstallButtons();
    syncPushState();
};

document.addEventListener('livewire:navigated', initPwa);
window.addEventListener('load', () => {
    initPwa();
    registerServiceWorker();
});
