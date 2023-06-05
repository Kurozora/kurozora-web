importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.4.1/workbox-sw.js');

const CACHE = "offline-page";
const offlineFallbackPage = "offline.html";

self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

self.addEventListener('install', async (event) => {
    event.waitUntil(
        caches.open(CACHE)
            .then((cache) => cache.add(offlineFallbackPage))
    );
});

if (workbox.navigationPreload.isSupported()) {
    workbox.navigationPreload.enable();
}

workbox.routing.registerRoute(
    /\.(?:js|css)$/,
    new workbox.strategies.StaleWhileRevalidate()
);

workbox.routing.registerRoute(
    /\.(?:png|jpg|jpeg|webp|svg|gif)$/,
    new workbox.strategies.CacheFirst()
);

self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const preloadResp = await event.preloadResponse;

                if (preloadResp) {
                    return preloadResp;
                }

                return await fetch(event.request);
            } catch (error) {

                const cache = await caches.open(CACHE);
                return await cache.match(offlineFallbackPage);
            }
        })());
    }
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});
