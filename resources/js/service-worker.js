importScripts('https://storage.googleapis.com/workbox-cdn/releases/7.0.0/workbox-sw.js')

const CACHE_VERSION = 'v1'
const CACHE_NAME = 'general-cache-' + CACHE_VERSION
const OFFLINE_URL = 'offline.html'

// Cache important files by default
const defaultCaches = [
    OFFLINE_URL,
    'css/app.css',
    'css/chat.css',
    'css/watch.css',
    'js/app.js',
    'js/vendor.js',
    'js/db.js',
    'js/chat.js',
    'js/listen.js',
    'js/theme.js',
    'js/watch.js',
    'images/static/icon/app_icon.webp',
]

// Don't cache these endpoints
const noCacheEndpoints = [
    '/forgot-password',
    '/merge-library',
    '/reset-password',
    '/sign-in',
    '/sign-out',
    '/sign-up',
    '/siwa',
    '/two-factor-authentication'
];

self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting()
    }
})

// Open a cache and add the default files during installation
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(defaultCaches)
        })
    )
})

// Activate the new service worker and clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name)
                    }
                })
            )
        })
    )
})

// Cache pages as the user browses
self.addEventListener('fetch', (event) => {
    const requestUrl = new URL(event.request.url);

    // Check if the request URL matches any endpoint that should not be cached
    if (noCacheEndpoints.some(endpoint => requestUrl.pathname.includes(endpoint))) {
        event.respondWith(fetch(event.request));
    } else {
        event.respondWith(
            caches.match(event.request).then((response) => {
                return response || fetch(event.request).then((fetchResponse) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, fetchResponse.clone())
                        return fetchResponse
                    })
                })
            }).catch(() => {
                return caches.match(OFFLINE_URL)
            })
        )
    }
})

// Background Sync - periodic sync
self.addEventListener('sync', (event) => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync())
    }
})

async function doBackgroundSync() {
    console.log('Background sync is triggered!')
}

// Notifications API
self.addEventListener('push', (event) => {
    const data = event.data.json();

    self.registration.showNotification(data.title, {
        body: data.body,
        icon: 'images/static/icon/app_icon.webp'
    });
});

self.addEventListener('notificationclick', (event) => {
    // Handle notification click event
    const notification = event.notification
    const action = event.action

    // if (action === 'open-url') {
    //     // Open a specific URL when the notification is clicked
    //     clients.openWindow('https://kurozora.app')
    // }

    notification.close()
})

self.addEventListener('notificationclose', (event) => {
    const dismissedNotification = event.notification
    console.log('Notification closed:', dismissedNotification)
})
