// Service Worker untuk SIMS PWA (Offline shell + Cache first)
const CACHE_NAME = 'sims-pwa-v1';
const STATIC_ASSETS = [
    '/pwa/login.html',
    '/pwa/manifest.json',
    '/pwa/siswa/index.html',
    '/pwa/siswa/jadwal.html',
    '/pwa/siswa/absensi.html',
    '/pwa/siswa/nilai.html',
    '/pwa/siswa/profil.html',
    '/pwa/ortu/index.html',
    '/pwa/ortu/anak.html',
    '/pwa/ortu/keuangan.html',
    '/pwa/ortu/pesan.html',
    '/pwa/ortu/profil.html'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => console.log('Precache warning:', err));
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Navigasi HTML atau aset statis: Cache First lalu Network Fallback
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(event.request).then((cached) => {
                    return cached || caches.match('/pwa/login.html');
                });
            })
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request).then((fetchRes) => {
                return fetchRes;
            }).catch(() => {
                // Offline fallback jika gagal
            });
        })
    );
});
