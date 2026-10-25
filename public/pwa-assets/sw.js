const CACHE_NAME = 'sims-pwa-v4';
const OFFLINE_URL = '/pwa/offline';
const PRECACHE_URLS = [
  '/pwa-assets/manifest.webmanifest',
  '/pwa-assets/icons/icon-192.png',
  '/pwa-assets/icons/icon-512.png',
  OFFLINE_URL,
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(PRECACHE_URLS)),
  );
});

self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
      .then(() => self.clients.claim()),
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;

  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  const isNavigation = request.mode === 'navigate';
  const isCacheableAsset = url.pathname.startsWith('/build/assets/')
    || url.pathname.startsWith('/pwa-assets/icons/')
    || url.pathname === '/pwa-assets/manifest.webmanifest';

  if (isNavigation) {
    event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
    return;
  }

  if (!isCacheableAsset) return;

  event.respondWith(
    caches.match(request).then((cached) => cached || fetch(request).then((response) => {
      if (response.ok) {
        const copy = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
      }

      return response;
    })),
  );
});

self.addEventListener('push', (event) => {
  const data = event.data ? event.data.json() : {};

  event.waitUntil(
    self.registration.showNotification(data.title || 'SIMS', {
      body: data.body || '',
      icon: data.icon || '/pwa-assets/icons/icon-192.png',
      badge: '/pwa-assets/icons/icon-192.png',
      data: { url: data.url || '/pwa' },
    }),
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const destination = event.notification.data?.url || '/pwa';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      const pwaClient = clientList.find((client) => {
        try {
          return new URL(client.url).pathname.startsWith('/pwa');
        } catch {
          return false;
        }
      });

      if (pwaClient) {
        pwaClient.navigate(destination);
        return pwaClient.focus();
      }

      return clients.openWindow(destination);
    }),
  );
});
