import PlyrManager from './plyr'

export default class TrailerHero {
    /**
     * The width the pinned player takes.
     *
     * @type {number}
     */
    static pinWidth = 360

    /**
     * The distance the pinned player keeps from the page's header.
     *
     * @type {number}
     */
    static pinGap = 12

    /**
     * The share of the player that has to leave the top before it pins.
     *
     * @type {number}
     */
    static pinThreshold = 2 / 3

    /**
     * The player options the hero's trailers are built with.
     *
     * @type {Object}
     */
    static playerOptions = {
        autoplay: false,
        ratio: '16:9',
        youtube: {
            autoplay: 0,
        },
    }

    /**
     * The hero section.
     *
     * @type {?HTMLElement}
     */
    #root = null

    /**
     * The space the player occupies inline, which is kept while the player is pinned.
     *
     * @type {?HTMLElement}
     */
    #slot = null

    /**
     * The element that moves to the corner while the reader scrolls.
     *
     * @type {?HTMLElement}
     */
    #stage = null

    /**
     * The frame the player was built in.
     *
     * @type {?HTMLElement}
     */
    #frame = null

    /**
     * The player.
     *
     * @type {?PlyrManager}
     */
    #manager = null

    /**
     * The identifier of the trailer in the player.
     *
     * @type {?string}
     */
    #code = null

    /**
     * Whether the trailer waiting on the player should start once it is ready.
     *
     * @type {boolean}
     */
    #playsWhenReady = false

    /**
     * Whether the reader has started a trailer.
     *
     * @type {boolean}
     */
    #hasPlayed = false

    /**
     * Whether the player sits in the corner.
     *
     * @type {boolean}
     */
    #isPinned = false

    /**
     * Whether the reader sent the pinned player away for the current scroll.
     *
     * @type {boolean}
     */
    #isDismissed = false

    /**
     * The place the pinned player was last put.
     *
     * @type {?Object}
     */
    #pinGeometry = null

    /**
     * Whether a sync is already scheduled for the next frame.
     *
     * @type {boolean}
     */
    #syncScheduled = false

    constructor() {
        new MutationObserver((records) => {
            // The player rewrites its own controls constantly, and none of that changes the page.
            if (records.every((record) => this.#frame?.contains(record.target))) {
                return
            }

            this.#scheduleSync()
        }).observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['data-code'] })

        document.addEventListener('click', (event) => this.#handleClick(event))
        document.addEventListener('livewire:navigated', () => this.#scheduleSync())
        window.addEventListener('scroll', () => this.#updatePin(), { passive: true })
        window.addEventListener('resize', () => this.#updatePin(), { passive: true })

        this.#sync()
    }

    /**
     * Coalesces the syncs a single render triggers into one.
     */
    #scheduleSync() {
        if (this.#syncScheduled) {
            return
        }

        this.#syncScheduled = true

        requestAnimationFrame(() => {
            this.#syncScheduled = false
            this.#sync()
        })
    }

    /**
     * Matches the player to the last render.
     */
    #sync() {
        const root = document.querySelector('[data-trailer-hero]')

        if (!root) {
            this.#teardown()

            return
        }

        this.#root = root
        this.#slot = root.querySelector('[data-trailer-hero-slot]')
        this.#stage = root.querySelector('[data-trailer-hero-stage]')

        const frame = root.querySelector('[data-trailer-hero-frame]')

        if (!frame) {
            return
        }

        if (frame !== this.#frame) {
            this.#teardown()
            this.#frame = frame
            this.#build()
        } else if (root.dataset.code !== this.#code) {
            this.#load(root.dataset.code, false)
        }

        this.#markPlaying()
        this.#updatePin()
    }

    /**
     * Builds the player on the hero's frame.
     */
    #build() {
        const code = this.#root.dataset.code

        if (!code) {
            return
        }

        this.#frame.setAttribute('player-src', 'https://www.youtube.com/watch?v=' + code)
        this.#frame.replaceChildren(document.createElement('iframe'))

        this.#code = code
        this.#manager = new PlyrManager(this.#frame, {
            ...TrailerHero.playerOptions,
            poster: this.#root.dataset.poster ?? '',
            youtube: {
                ...TrailerHero.playerOptions.youtube,
                origin: window.location.origin,
            },
        })

        const player = this.#manager.player

        player?.on('ready', () => {
            if (!this.#playsWhenReady) {
                return
            }

            this.#playsWhenReady = false
            player.play()
        })

        player?.on('playing', () => {
            this.#hasPlayed = true
            this.#markPlaying()
            this.#updatePin()
        })

        player?.on('pause', () => this.#markPlaying())
        player?.on('ended', () => this.#advance())
    }

    /**
     * Puts the given trailer in the player.
     *
     * @param {string} code
     * @param {boolean} plays
     */
    #load(code, plays) {
        const player = this.#manager?.player

        if (!player || !code) {
            return
        }

        this.#code = code
        this.#playsWhenReady = plays
        player.source = {
            type: 'video',
            sources: [{ src: code, provider: 'youtube' }],
        }

        if (plays) {
            player.play()
        }

        this.#markPlaying()
    }

    /**
     * Releases the player.
     */
    #teardown() {
        this.#setPinned(false)
        this.#manager?.destroy()
        this.#manager = null
        this.#frame = null
        this.#code = null
        this.#hasPlayed = false
        this.#playsWhenReady = false
    }

    /**
     * Routes a press to the control it landed on.
     *
     * @param {MouseEvent} event
     */
    #handleClick(event) {
        if (event.target.closest('[data-trailer-hero-close]')) {
            event.preventDefault()
            this.#isDismissed = true
            this.#setPinned(false)

            return
        }

        if (event.target.closest('[data-trailer-hero-return]')) {
            event.preventDefault()
            this.#returnToHero()

            return
        }

        const lockup = event.target.closest('[data-trailer-lockup]')

        // The press also reaches Livewire, which brings the hero's details along behind it.
        if (lockup && event.target.closest('[data-trailer-play]')) {
            this.#feature(lockup.dataset.code, lockup.dataset.poster)
        }
    }

    /**
     * Gives the player the given trailer, or turns the one it already holds.
     *
     * @param {string} code
     * @param {string} poster
     */
    #feature(code, poster) {
        const player = this.#manager?.player

        if (!player || !code) {
            return
        }

        if (code === this.#code) {
            if (player.playing) {
                player.pause()
            } else {
                player.play()
            }

            return
        }

        if (this.#root && poster) {
            this.#root.dataset.poster = poster
        }

        this.#load(code, true)
    }

    /**
     * Moves on to the trailer behind the one that just finished.
     */
    #advance() {
        const lockups = Array.from(document.querySelectorAll('[data-trailer-lockup]'))

        if (!lockups.length) {
            return
        }

        const index = lockups.findIndex((lockup) => lockup.dataset.code === this.#code)
        const next = lockups[index + 1] ?? lockups[0]

        next?.querySelector('[data-trailer-play]')?.click()
    }

    /**
     * Marks the lockup whose trailer the player is running.
     */
    #markPlaying() {
        const isPlaying = this.#manager?.player?.playing ?? false

        document.querySelectorAll('[data-trailer-lockup]').forEach((lockup) => {
            lockup.toggleAttribute('data-playing', isPlaying && lockup.dataset.code === this.#code)
        })
    }

    /**
     * Pins the player to the corner, or returns it to the hero, for the current scroll.
     */
    #updatePin() {
        if (!this.#slot || !this.#stage || !this.#hasPlayed) {
            this.#setPinned(false)

            return
        }

        const slotRect = this.#slot.getBoundingClientRect()
        const headerBottom = this.#headerBottom()

        if (headerBottom - slotRect.top < slotRect.height * TrailerHero.pinThreshold) {
            this.#isDismissed = false
            this.#setPinned(false)

            return
        }

        if (this.#isDismissed) {
            return
        }

        const top = headerBottom + TrailerHero.pinGap
        const left = slotRect.left
        const width = Math.min(TrailerHero.pinWidth, slotRect.width)

        // Writing on every scroll would make each following read reflow the page.
        if (this.#pinGeometry?.top !== top || this.#pinGeometry?.left !== left || this.#pinGeometry?.width !== width) {
            this.#pinGeometry = { top, left, width }
            this.#stage.style.setProperty('--trailer-pin-top', top + 'px')
            this.#stage.style.setProperty('--trailer-pin-left', left + 'px')
            this.#stage.style.setProperty('--trailer-pin-width', width + 'px')
        }

        this.#setPinned(true)
    }

    /**
     * Puts the player in the corner, or takes it back out.
     *
     * @param {boolean} isPinned
     */
    #setPinned(isPinned) {
        this.#isPinned = isPinned

        // A render that lands mid-scroll drops the attribute, so the DOM decides whether to write.
        if (!this.#stage || this.#stage.hasAttribute('data-pinned') === isPinned) {
            return
        }

        this.#stage.toggleAttribute('data-pinned', isPinned)
    }

    /**
     * The point the page's header leaves free.
     *
     * @returns {number}
     */
    #headerBottom() {
        const header = document.querySelector('[data-trailer-header]')

        return header ? header.getBoundingClientRect().bottom : 0
    }

    /**
     * Returns the reader to the hero.
     */
    #returnToHero() {
        if (!this.#slot) {
            return
        }

        const top = window.scrollY + this.#slot.getBoundingClientRect().top - this.#headerBottom() - TrailerHero.pinGap

        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' })
    }
}
