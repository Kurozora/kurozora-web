/**
 * Online Users Card: live counts of signed-in users and guest contexts holding an active WebSocket.
 */
Nova.booting((app, store) => {
    app.component('online-users-card', {
        props: {
            card: {
                type: Object,
                required: true,
            },
        },
        data() {
            return {
                web: {signedIn: null, guests: null},
                api: {signedIn: null, guests: null},
                lastUpdatedAt: null,
                seedError: false,
                status: 'connecting',
                statusReason: '',
                socket: null,
                socketId: null,
                reconnectTimer: null,
                reconnectDelayMs: 1000,
                clockTick: 0,
                clockTimer: null,
            }
        },
        computed: {
            total() {
                const parts = [this.web.signedIn, this.web.guests, this.api.signedIn, this.api.guests]

                if (parts.some(value => value === null)) {
                    return null
                }

                return parts.reduce((sum, value) => sum + value, 0)
            },
            statusLabel() {
                if (this.status === 'live') {
                    return 'Live'
                }

                if (this.status === 'misconfigured') {
                    return 'Not configured'
                }

                if (this.status === 'failed') {
                    return 'Disconnected'
                }

                return 'Connecting…'
            },
            statusDotClass() {
                if (this.status === 'live') {
                    return 'bg-green-500'
                }

                if (this.status === 'connecting') {
                    return 'bg-amber-500 animate-pulse'
                }

                return 'bg-red-500'
            },
            relativeUpdatedAt() {
                // Recompute when clockTick advances so "x seconds ago" stays current.
                // eslint-disable-next-line no-unused-expressions
                this.clockTick

                if (!this.lastUpdatedAt) {
                    return ''
                }

                const diffSeconds = Math.max(0, Math.round((Date.now() - this.lastUpdatedAt) / 1000))

                if (diffSeconds < 5) {
                    return 'just now'
                }

                if (diffSeconds < 60) {
                    return diffSeconds + 's ago'
                }

                if (diffSeconds < 3600) {
                    return Math.floor(diffSeconds / 60) + 'm ago'
                }

                return Math.floor(diffSeconds / 3600) + 'h ago'
            },
            absoluteUpdatedAt() {
                if (!this.lastUpdatedAt) {
                    return ''
                }

                return new Date(this.lastUpdatedAt).toLocaleTimeString()
            },
        },
        async mounted() {
            this.clockTimer = setInterval(() => {
                this.clockTick++
            }, 5000)

            await this.seed()
            this.connect()
        },
        beforeUnmount() {
            this.teardown()
        },
        methods: {
            async seed() {
                try {
                    const response = await Nova.request().get(this.card.seed_url)
                    this.web.signedIn = response.data.web.signed_in
                    this.web.guests = response.data.web.guests
                    this.api.signedIn = response.data.api.signed_in
                    this.api.guests = response.data.api.guests
                    this.lastUpdatedAt = response.data.at ? Date.parse(response.data.at) : Date.now()
                    this.seedError = false
                } catch (error) {
                    this.seedError = true

                    console.error('[online-users-card] seed failed', error)
                }
            },
            connect() {
                const config = this.card.reverb

                if (!config || !config.key || !config.host) {
                    this.status = 'misconfigured'
                    this.statusReason = 'Reverb client config missing (VITE_REVERB_HOST / VITE_REVERB_APP_KEY).'
                    return
                }

                const scheme = config.scheme === 'https' ? 'wss' : 'ws'
                const url = scheme + '://' + config.host + ':' + config.port + '/app/' + config.key + '?protocol=7&client=nova-online-users-card&version=1.0&flash=false'

                try {
                    this.socket = new WebSocket(url)
                } catch (error) {
                    this.status = 'failed'
                    this.statusReason = 'WebSocket open threw: ' + error.message

                    console.error('[online-users-card]', this.statusReason, error)

                    this.scheduleReconnect()
                    return
                }

                this.socket.addEventListener('message', this.onMessage)
                this.socket.addEventListener('close', this.onClose)
                this.socket.addEventListener('error', this.onError)
            },
            async onMessage(event) {
                const message = this.decode(event.data)

                if (!message) {
                    return
                }

                if (message.event === 'pusher:connection_established') {
                    const data = this.decode(message.data)

                    if (data && data.socket_id) {
                        this.socketId = data.socket_id
                        await this.subscribe(data.socket_id)
                    }

                    return
                }

                if (message.event === 'pusher:ping') {
                    this.send({event: 'pusher:pong', data: {}})
                    return
                }

                if (message.event === 'pusher:error') {
                    const data = this.decode(message.data)
                    this.status = 'failed'
                    this.statusReason = 'Pusher error: ' + (data && data.message ? data.message : 'unknown')

                    console.error('[online-users-card]', this.statusReason, data)

                    return
                }

                if (message.event === 'pusher_internal:subscription_succeeded') {
                    this.status = 'live'
                    this.statusReason = ''
                    this.reconnectDelayMs = 1000

                    // Re-seed once the channel is open so the counts reflect transitions
                    // that fired between the initial fetch and subscription handshake.
                    await this.seed()

                    return
                }

                if (message.event === '.presence.changed' || message.event === 'presence.changed') {
                    const data = this.decode(message.data)

                    if (!data || typeof data.delta !== 'number') {
                        return
                    }

                    const target = this[data.source]
                    const key = data.kind === 'signed_in' ? 'signedIn' : 'guests'

                    if (target && typeof target[key] === 'number') {
                        target[key] += data.delta
                        this.lastUpdatedAt = Date.now()
                    }
                }
            },
            onClose(event) {
                this.status = 'failed'
                this.statusReason = 'WebSocket closed (code ' + event.code + ')'
                this.scheduleReconnect()
            },
            onError(event) {
                console.error('[online-users-card] websocket error', event)
            },
            async subscribe(socketId) {
                const channel = 'private-' + this.card.channel

                let auth

                try {
                    const response = await Nova.request().post('/broadcasting/auth', {
                        socket_id: socketId,
                        channel_name: channel,
                    })
                    auth = response.data.auth
                } catch (error) {
                    this.status = 'failed'
                    const reasonStatus = error.response ? error.response.status : 'network'
                    this.statusReason = 'Channel auth failed (' + reasonStatus + ')'

                    console.error('[online-users-card]', this.statusReason, error.response ? error.response.data : error)

                    return
                }

                if (!auth) {
                    this.status = 'failed'
                    this.statusReason = 'Channel auth response missing `auth` field'

                    console.error('[online-users-card]', this.statusReason)

                    return
                }

                this.send({
                    event: 'pusher:subscribe',
                    data: {
                        auth: auth,
                        channel: channel,
                    },
                })
            },
            send(payload) {
                if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                    this.socket.send(JSON.stringify(payload))
                }
            },
            decode(value) {
                if (typeof value !== 'string') {
                    return value
                }

                try {
                    return JSON.parse(value)
                } catch (error) {
                    return null
                }
            },
            scheduleReconnect() {
                if (this.reconnectTimer) {
                    return
                }

                if (this.status === 'misconfigured') {
                    return
                }

                this.reconnectTimer = setTimeout(() => {
                    this.reconnectTimer = null
                    this.reconnectDelayMs = Math.min(this.reconnectDelayMs * 2, 30000)
                    this.status = 'connecting'
                    this.connect()
                }, this.reconnectDelayMs)
            },
            teardown() {
                if (this.clockTimer) {
                    clearInterval(this.clockTimer)
                    this.clockTimer = null
                }

                if (this.reconnectTimer) {
                    clearTimeout(this.reconnectTimer)
                    this.reconnectTimer = null
                }

                if (this.socket) {
                    this.socket.removeEventListener('message', this.onMessage)
                    this.socket.removeEventListener('close', this.onClose)
                    this.socket.removeEventListener('error', this.onError)
                    this.socket.close()
                    this.socket = null
                }
            },
        },
        template: `
            <Card class="flex flex-col px-6 py-5 h-full">
                <div class="flex items-center justify-between text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-2" :title="statusReason">
                        <span :class="['inline-block w-2 h-2 rounded-full', statusDotClass]"></span>
                        <span>{{ statusLabel }}</span>
                    </div>
                    <span
                        v-if="lastUpdatedAt"
                        class="normal-case tracking-normal text-[11px] text-gray-400 dark:text-gray-500"
                        :title="absoluteUpdatedAt"
                    >
                        Updated {{ relativeUpdatedAt }}
                    </span>
                </div>

                <h1
                    class="mt-4 text-5xl font-light leading-none tracking-tight text-gray-800 dark:text-gray-100"
                    title="Web dedupes by user/tab. App dedupes by user/launch."
                >
                    <template v-if="seedError">—</template>
                    <template v-else-if="total === null">…</template>
                    <template v-else>{{ total.toLocaleString() }}</template>
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Active visitors</p>

                <div class="mt-5 space-y-3 text-sm">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Web</div>
                        <div class="flex items-baseline justify-between pl-3">
                            <span class="text-gray-500 dark:text-gray-400">Signed-in</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-100 tabular-nums">
                                <template v-if="seedError">—</template>
                                <template v-else-if="web.signedIn === null">…</template>
                                <template v-else>{{ web.signedIn.toLocaleString() }}</template>
                            </span>
                        </div>
                        <div class="flex items-baseline justify-between pl-3">
                            <span class="text-gray-500 dark:text-gray-400">Guests</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-100 tabular-nums">
                                <template v-if="seedError">—</template>
                                <template v-else-if="web.guests === null">…</template>
                                <template v-else>{{ web.guests.toLocaleString() }}</template>
                            </span>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">App</div>
                        <div class="flex items-baseline justify-between pl-3">
                            <span class="text-gray-500 dark:text-gray-400">Signed-in</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-100 tabular-nums">
                                <template v-if="seedError">—</template>
                                <template v-else-if="api.signedIn === null">…</template>
                                <template v-else>{{ api.signedIn.toLocaleString() }}</template>
                            </span>
                        </div>
                        <div class="flex items-baseline justify-between pl-3">
                            <span class="text-gray-500 dark:text-gray-400">Guests</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-100 tabular-nums">
                                <template v-if="seedError">—</template>
                                <template v-else-if="api.guests === null">…</template>
                                <template v-else>{{ api.guests.toLocaleString() }}</template>
                            </span>
                        </div>
                    </div>
                </div>
            </Card>
        `,
    })
})
