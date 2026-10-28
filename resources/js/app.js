/**
 * SIMS application bootstrap.
 *
 * Alpine is provided by Livewire. This file only adds shell behaviour,
 * network status, toasts, and a request-scoped loading indicator. It never
 * contains demo data or role detection.
 */

import { createIcons, icons } from 'lucide';

/**
 * Ikon Lucide: elemen <i data-lucide> hanya berubah menjadi SVG setelah
 * createIcons() dipanggil. Paket lucide terpasang tapi tidak pernah
 * diinisialisasi, sehingga semua ikon (kartu bantuan, QR, auth) tampil
 * kosong. Dijalankan sekali saat muat dan diulang setiap Livewire selesai
 * me-render ulang DOM (morph), karena morph mengembalikan elemen <i>.
 */
const renderLucideIcons = () => {
    try {
        createIcons({ icons });
    } catch {
        // Lucide gagal dimuat: biarkan <i> kosong daripada merusak shell.
    }
};

renderLucideIcons();

document.addEventListener('livewire:init', () => {
    if (!window.Livewire) return;

    try {
        window.Livewire.hook('morph.updated', () => renderLucideIcons());
    } catch {
        // Hook tidak tersedia di versi Livewire ini.
    }
});

document.addEventListener('livewire:navigated', renderLucideIcons);

const STORAGE = {
    theme: 'sims-theme',
    sidebar: 'sims-sidebar-collapsed',
};

const read = (key) => {
    try {
        return window.localStorage.getItem(key);
    } catch {
        return null;
    }
};

const write = (key, value) => {
    try {
        window.localStorage.setItem(key, value);
    } catch {
        // Private mode / blocked storage: keep the UI usable.
    }
};

const applyTheme = (dark) => {
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
};

applyTheme(read(STORAGE.theme) === 'dark');

const network = () => {
    const online = navigator.onLine;
    document.querySelectorAll('[data-network-status]').forEach((element) => {
        element.textContent = online ? 'Online' : 'Offline';
        element.classList.toggle('is-offline', !online);
        element.classList.toggle('offline', !online);
    });
    document.documentElement.dataset.network = online ? 'online' : 'offline';
};

network();
window.addEventListener('online', network);
window.addEventListener('offline', network);

const toastTray = () => {
    let tray = document.querySelector('[data-sims-toasts]');
    if (tray) return tray;

    tray = document.createElement('div');
    tray.className = 'sims-toast-tray';
    tray.dataset.simsToasts = '';
    tray.setAttribute('aria-live', 'polite');
    document.body.appendChild(tray);

    return tray;
};

const showToast = (detail = {}) => {
    const tray = toastTray();
    const type = detail.type || 'info';
    const item = document.createElement('div');
    item.className = 'sims-toast';
    item.setAttribute('role', 'status');

    const icon = document.createElement('span');
    icon.className = `sims-icon-box ${type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'}`;
    icon.style.width = '34px';
    icon.style.height = '34px';
    icon.style.flexShrink = '0';
    icon.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>';

    const copy = document.createElement('div');
    const title = document.createElement('strong');
    title.textContent = detail.title || (type === 'success' ? 'Berhasil' : type === 'error' ? 'Gagal' : 'Informasi');
    const message = document.createElement('p');
    message.textContent = detail.message || '';
    copy.append(title, message);

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'sims-icon-btn';
    close.style.width = '28px';
    close.style.height = '28px';
    close.style.marginLeft = 'auto';
    close.setAttribute('aria-label', 'Tutup notifikasi');
    close.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>';
    close.addEventListener('click', () => item.remove());

    item.append(icon, copy, close);
    tray.appendChild(item);

    window.setTimeout(() => item.remove(), detail.duration ?? 5200);
};

window.addEventListener('sims-toast', (event) => showToast(event.detail));
window.addEventListener('toast', (event) => showToast(event.detail));

document.addEventListener('alpine:init', () => {
    if (!window.Alpine) return;

    window.Alpine.data('simsShell', (config = {}) => ({
        collapsed: read(STORAGE.sidebar) === '1',
        mobileOpen: false,
        dark: read(STORAGE.theme) === 'dark',
        commandOpen: false,
        commandQuery: '',
        profileOpen: false,
        notificationsOpen: false,
        quickOpen: false,
        commands: config.commands || [],
        init() {
            applyTheme(this.dark);
            this.$watch('dark', (value) => {
                write(STORAGE.theme, value ? 'dark' : 'light');
                applyTheme(value);
            });
        },
        toggleSidebar() {
            this.collapsed = !this.collapsed;
            write(STORAGE.sidebar, this.collapsed ? '1' : '0');
        },
        toggleTheme() {
            this.dark = !this.dark;
        },
        openCommand() {
            this.commandOpen = true;
            this.$nextTick(() => this.$refs.commandInput?.focus());
        },
        filteredCommands() {
            const query = this.commandQuery.trim().toLowerCase();
            if (!query) return this.commands;
            return this.commands.filter((item) => `${item.title} ${item.group || ''}`.toLowerCase().includes(query));
        },
        runFirstCommand() {
            const first = this.filteredCommands()[0];
            if (first) window.location.href = first.href;
        },
        toast(message, type = 'info') {
            showToast({ message, type });
        },
    }));
});

/*
 * Loading manager: a request-scoped progress bar with a timeout guard.
 * Never leaves a blocking overlay behind on success, validation error,
 * exception, or timeout.
 *
 * The modal overlay (data-sims-loading, gaya .app-loading-* dari referensi)
 * hanya muncul bila permintaan berjalan lebih dari 400ms, supaya interaksi
 * cepat (mis. ketik di kolom cari) tidak berkedip tertutup modal.
 */
document.addEventListener('livewire:init', () => {
    if (!window.Livewire) return;

    let active = 0;
    let navigationTimer = null;
    let overlayTimer = null;
    const timers = new Set();
    const bar = () => document.querySelector('[data-sims-progress]');
    const overlay = () => document.querySelector('[data-sims-loading]');

    const showOverlay = () => {
        const element = overlay();
        if (!element) return;

        element.removeAttribute('hidden');
        element.setAttribute('aria-hidden', 'false');
    };

    const hideOverlay = () => {
        const element = overlay();
        if (!element) return;

        element.setAttribute('hidden', '');
        element.setAttribute('aria-hidden', 'true');
    };

    const start = () => {
        active += 1;
        bar()?.classList.add('is-active');
        if (active === 1 && overlayTimer === null) {
            overlayTimer = window.setTimeout(() => {
                overlayTimer = null;
                if (active > 0) showOverlay();
            }, 400);
        }
        const timer = window.setTimeout(() => {
            if (!timers.delete(timer)) return;

            active = Math.max(0, active - 1);
            if (active === 0) {
                bar()?.classList.remove('is-active');
                if (overlayTimer !== null) {
                    window.clearTimeout(overlayTimer);
                    overlayTimer = null;
                }
                hideOverlay();
            }
            showToast({ type: 'error', title: 'Koneksi lambat', message: 'Permintaan memakan waktu terlalu lama. Silakan coba lagi.' });
        }, 20000);
        timers.add(timer);

        return timer;
    };

    const end = (timer) => {
        if (!timers.delete(timer)) return;

        window.clearTimeout(timer);
        active = Math.max(0, active - 1);
        if (active === 0) {
            bar()?.classList.remove('is-active');
            if (overlayTimer !== null) {
                window.clearTimeout(overlayTimer);
                overlayTimer = null;
            }
            hideOverlay();
        }
    };

    try {
        window.Livewire.hook('request', ({ succeed, fail }) => {
            const timer = start();
            succeed(() => end(timer));
            fail(() => end(timer));
        });
    } catch {
        // Older/newer Livewire versions may expose different hook names.
        document.addEventListener('livewire:navigating', () => {
            navigationTimer = start();
        });
        document.addEventListener('livewire:navigated', () => {
            if (navigationTimer !== null) end(navigationTimer);
            navigationTimer = null;
        });
    }

    document.addEventListener('livewire:navigated', () => {
        bar()?.classList.remove('is-active');
        hideOverlay();
    });
});
