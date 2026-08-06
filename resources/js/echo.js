import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const key = import.meta.env.VITE_REVERB_APP_KEY
const host = import.meta.env.VITE_REVERB_HOST

if (key && host) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: host,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    })

    if (!isAuthenticated()) {
        window.Echo.channel('visitors.' + resolveVisitorToken())
    }
}

/**
 * Returns whether the current page is rendered for a signed-in user, based on the `is-authenticated` meta tag.
 *
 * @returns {boolean}
 */
function isAuthenticated() {
    return document.querySelector('meta[name="is-authenticated"]') !== null
}

/**
 * Returns the stable visitor token for this browser context, minting one if absent.
 *
 * @returns {string}
 */
function resolveVisitorToken() {
    const storageKey = 'kurozora_visitor_token'

    try {
        const existing = window.localStorage.getItem(storageKey)

        if (existing) {
            return existing
        }

        const minted = generateUUID()

        window.localStorage.setItem(storageKey, minted)

        return minted
    } catch (error) {
        return generateUUID()
    }
}

/**
 * Generates a UUID.
 *
 * @returns {string}
 */
function generateUUID() {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
        return window.crypto.randomUUID()
    }

    const bytes = new Uint8Array(16)

    if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
        window.crypto.getRandomValues(bytes)
    } else {
        for (let i = 0; i < 16; i++) {
            bytes[i] = Math.floor(Math.random() * 256)
        }
    }

    bytes[6] = (bytes[6] & 0x0f) | 0x40
    bytes[8] = (bytes[8] & 0x3f) | 0x80

    const hex = Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0'))

    return hex.slice(0, 4).join('') + '-'
        + hex.slice(4, 6).join('') + '-'
        + hex.slice(6, 8).join('') + '-'
        + hex.slice(8, 10).join('') + '-'
        + hex.slice(10, 16).join('')
}
