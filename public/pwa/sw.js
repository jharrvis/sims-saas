/* Service Worker PWA SIMS — cache-first untuk aset statis, network-first untuk halaman. */
const CACHE = 'sims-pwa-v1';
const OFFLINE_URL = '/pwa/offline';
const PRECACHE = [
  '/pwa/manifest.webmanifest',
  '/pwa/icons/icon-192.png',
  '/pwa/offline',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;

  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  // Hanya tangani origin yang sama di bawah /pwa/ dan aset publik.
  if (url.origin !== self.location.origin) {
    return;
  }

  const isPage = request.mode === 'navigate' || (request.headers.get('accept') || '').includes('text/html');

  if (isPage) {
    // Network-first untuk halaman; fallback ke cache lalu halaman offline.
    event.respondWith(
      fetch(request)
        .then((response) => {
          const copy = response.clone();
          caches.open(CACHE).then((cache) => cache.put(request, copy));
          return response;
        })
        .catch(() => caches.match(request).then((cached) => cached || caches.match(OFFLINE_URL)))
    );
    return;
  }

  // Cache-first untuk aset statis.
  event.respondWith(
    caches.match(request).then(
      (cached) =>
        cached ||
        fetch(request).then((response) => {
          const copy = response.clone();
          caches.open(CACHE).then((cache) => cache.put(request, copy));
          return response;
        })
    )
  );
});

// Web Push (FR-1304): tampilkan notifikasi & buka URL saat diketuk.
self.addEventListener('push', (event) => {
  const data = event.data ? event.data.json() : {};
  event.waitUntil(
    self.registration.showNotification(data.title || 'SIMS', {
      body: data.body || '',
      icon: data.icon || '/pwa/icons/icon-192.png',
      badge: '/pwa/icons/icon-192.png',
      data: { url: data.url || '/pwa' },
    })
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = (event.notification.data && event.notification.data.url) || '/pwa';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((list) => {
      for (const c of list) {
        try {
          if (new URL(c.url).pathname.startsWith('/pwa')) {
            c.navigate(url);
            return c.focus();
          }
        } catch (e) {}
      }
      return clients.openWindow(url);
    })
  );
});
