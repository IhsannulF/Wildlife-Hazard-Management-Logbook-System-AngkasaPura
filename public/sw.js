const CACHE_NAME = 'whms-v2.0';
const OFFLINE_URL = '/offline';

const PRECACHE_ASSETS = [
  '/',
  OFFLINE_URL,
  '/manifest.json',
  '/favicon.svg',
  '/favicon.ico',
  '/apple-touch-icon.png',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png',
  '/icons/icon-maskable-512x512.png',
  '/js/whms-offline.js',
  '/images/gridmap_juanda.jpg',
  '/images/logo_login.png'
];

// Install: Cache critical assets and offline fallback
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(PRECACHE_ASSETS).catch((err) => {
        console.warn('[WHMS SW] Pre-caching partial error:', err);
      });
    }).then(() => self.skipWaiting())
  );
});

// Activate: Clean up previous caches and claim clients
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            console.log('[WHMS SW] Menghapus cache usang:', cache);
            return caches.delete(cache);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch: Smart network-first for navigation, cache-first for static assets
self.addEventListener('fetch', (event) => {
  const req = event.request;

  // Hanya proses protokol HTTP/HTTPS dan metode GET
  if (req.method !== 'GET' || !req.url.startsWith('http')) {
    return;
  }

  const url = new URL(req.url);

  // Jangan tangani API internal sync atau upload dinamis
  if (url.pathname.includes('/sync-offline') || url.pathname.includes('/logout')) {
    return;
  }

  // 1. Navigation requests (HTML pages) -> Network first, fallback to cache, then fallback to /offline
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req)
        .then((networkRes) => {
          if (networkRes && networkRes.status === 200) {
            const resClone = networkRes.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(req, resClone));
          }
          return networkRes;
        })
        .catch(async () => {
          const cachedRes = await caches.match(req);
          if (cachedRes) {
            return cachedRes;
          }
          const offlinePage = await caches.match(OFFLINE_URL);
          return offlinePage || new Response('Aplikasi sedang offline.', {
            status: 503,
            statusText: 'Service Unavailable (Offline)',
            headers: { 'Content-Type': 'text/plain; charset=utf-8' }
          });
        })
    );
    return;
  }

  // 2. Static Assets (CSS, JS, Fonts, Images) -> Stale-while-revalidate / Cache First
  if (
    url.pathname.startsWith('/build/') ||
    url.pathname.startsWith('/icons/') ||
    url.pathname.startsWith('/images/') ||
    url.pathname.startsWith('/js/') ||
    req.destination === 'style' ||
    req.destination === 'script' ||
    req.destination === 'image' ||
    req.destination === 'font'
  ) {
    event.respondWith(
      caches.match(req).then((cached) => {
        const fetchPromise = fetch(req).then((networkRes) => {
          if (networkRes && networkRes.status === 200) {
            const resClone = networkRes.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(req, resClone));
          }
          return networkRes;
        }).catch(() => null);

        return cached || fetchPromise;
      })
    );
    return;
  }

  // Default passthrough
  event.respondWith(
    fetch(req).catch(() => caches.match(req))
  );
});
