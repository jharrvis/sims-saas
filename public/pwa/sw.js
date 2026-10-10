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
