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
}
