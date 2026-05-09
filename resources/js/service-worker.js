import {matchPrecache, precacheAndRoute} from 'workbox-precaching'
import {registerRoute, setCatchHandler} from 'workbox-routing'
import {NetworkFirst, NetworkOnly} from 'workbox-strategies'

const CACHE_VERSION = 'v1'
const CACHE_NAME = 'general-cache-' + CACHE_VERSION
const OFFLINE_URL = '/offline.html'

precacheAndRoute(self.__WB_MANIFEST)

// Don't cache these endpoints
const noCacheEndpoints = [
    '/forgot-password',
    '/merge-library',
    '/reset-password',
    '/sign-in',
    '/sign-out',
    '/sign-up',
    '/siwa',
    '/two-factor-authentication',
]

registerRoute(
    ({ url }) =>
        url.origin === self.location.origin
        && noCacheEndpoints.some(path => url.pathname.includes(path)),
    new NetworkOnly()
)

// Network-first, fall back to runtime cache when the network is unavailable
registerRoute(
    ({ url }) => url.origin === self.location.origin,
    new NetworkFirst({ cacheName: CACHE_NAME })
)

// When network and cache both miss, serve the precached offline page for navigations
setCatchHandler(async ({ request }) => {
    if (request.destination === 'document') {
        const offline = await matchPrecache(OFFLINE_URL)
        if (offline) return offline
    }
    return Response.error()
})

// Activate the new worker as soon as it is installed, replacing the previous version
self.addEventListener('install', () => {
    self.skipWaiting()
})

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim())
})

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting()
    }
})

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
    const data = event.data.json()

    self.registration.showNotification(data.title, {
        body: data.body,
        icon: '/images/static/icon/app_icon.webp',
    })
})

self.addEventListener('notificationclick', (event) => {
    // Handle notification click event
    const notification = event.notification
    const action = event.action

    // if (action === 'open-url') {
    //     // Open a specific URL when the notification is clicked
    //     clients.openWindow('https://kurozora.app')
    // }

    event.notification.close()
})

self.addEventListener('notificationclose', (event) => {
    console.log('Notification closed:', event.notification)
})
