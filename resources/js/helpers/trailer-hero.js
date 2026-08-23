import PlyrManager from './plyr'

export default class TrailerHero {
    /**
     * The width from which the queue is listed beside the trailer.
     *
     * @type {string}
     */
    static wideQuery = '(min-width: 1024px)'

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
     * The milliseconds the caption spends faded out while it changes.
     *
     * @type {number}
     */
    static captionFade = 150

    /**
     * The hero section.
     *
     * @type {?HTMLElement}
     */
    #root = null

    /**
     * The track the trailers are laid out on.
     *
     * @type {?HTMLElement}
     */
    #track = null

    /**
     * The queue beside the trailer.
     *
     * @type {?HTMLElement}
     */
    #queue = null

    /**
     * The queue entry the player is on.
     *
     * @type {?HTMLElement}
     */
    #playing = null

    /**
     * The identifier of the trailer in the player.
     *
     * @type {?string}
     */
    #code = null

    /**
     * The trailer the track settled on.
     *
     * @type {number}
     */
    #index = -1

    /**
     * Whether the queue is beside the trailer.
     *
     * @type {?boolean}
     */
    #wide = null

    /**
     * The player.
     *
     * @type {?PlyrManager}
     */
    #manager = null

    /**
     * The frame the player was built in.
     *
     * @type {?HTMLElement}
     */
    #frame = null

    /**
     * Whether the trailer the track settles on should start playing.
     *
     * @type {boolean}
     */
    #plays = false

    /**
     * Whether a sync is already scheduled for the next frame.
     *
     * @type {boolean}
     */
    #syncScheduled = false

    constructor() {
        new MutationObserver(() => this.#scheduleSync())
            .observe(document.body, { childList: true, subtree: true })

        document.addEventListener('click', (event) => {
            const item = event.target.closest('[data-trailer-hero-item]')

            if (item) {
                this.#rotate(item)
            }
        })

        document.addEventListener('livewire:navigated', () => this.#scheduleSync())
        window.matchMedia(TrailerHero.wideQuery).addEventListener('change', () => this.#scheduleSync())

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
            return
        }

        this.#root = root
        this.#track = root.querySelector('[data-trailer-hero-slides]')
        this.#queue = root.querySelector('[data-trailer-hero-queue]')

        if (this.#frame && !document.contains(this.#frame)) {
            this.#teardown()
            this.#index = -1
        }

        if (!this.#track || !this.#slides().length) {
            return
        }

        const wide = window.matchMedia(TrailerHero.wideQuery).matches

        if (wide !== this.#wide) {
            this.#wide = wide
            this.#teardown()
            this.#index = -1
        }

        if (wide) {
            this.#syncQueue()

            return
        }

        this.#syncTrack()
    }

    /**
     * The trailers the track holds.
     *
     * @returns {HTMLElement[]}
     */
    #slides() {
        return Array.from(this.#track?.querySelectorAll('[data-trailer-hero-slide]') ?? [])
    }

    /**
     * The entries the queue holds.
     *
     * @returns {HTMLElement[]}
     */
    #entries() {
        return Array.from(this.#queue?.querySelectorAll('[data-trailer-hero-item]') ?? [])
    }

    /**
     * Holds the trailer still and lets the queue rotate through it.
     */
    #syncQueue() {
        const slides = this.#slides()
        const entries = this.#entries()

        if (!entries.length) {
            return
        }

        slides.forEach((slide, index) => slide.classList.toggle('hidden', index !== 0))
        this.#track.scrollLeft = 0

        const playing = entries.find((entry) => entry.dataset.code === this.#code) ?? entries[0]

        entries.forEach((entry) => entry.classList.toggle('hidden', entry === playing))
        this.#playing = playing
        this.#describe(playing)

        const frame = slides[0]?.querySelector('[data-trailer-hero-frame]')

        if (frame && frame !== this.#frame) {
            this.#build(frame, playing.dataset.code)
        } else if (this.#code !== playing.dataset.code) {
            this.#load(playing.dataset.code, false)
        }
    }

    /**
     * Lets the trailers be swiped through.
     */
    #syncTrack() {
        const slides = this.#slides()

        slides.forEach((slide) => slide.classList.remove('hidden'))

        if (!this.#track.dataset.bound) {
            this.#track.dataset.bound = 'true'
            this.#track.addEventListener('scroll', () => this.#scheduleSync(), { passive: true })
        }

        const index = Math.round(this.#track.scrollLeft / this.#track.clientWidth)

        if (index !== this.#index || !this.#frame) {
            this.#settle(index)
        }
    }

    /**
     * Gives the player to the trailer at the given position.
     *
     * @param {number} index
     */
    #settle(index) {
        const slide = this.#slides()[index]

        if (!slide) {
            return
        }

        this.#index = index
        this.#describe(slide)

        const frame = slide.querySelector('[data-trailer-hero-frame]')

        if (frame && frame !== this.#frame) {
            this.#build(frame, slide.dataset.code)
        }
    }

    /**
     * Builds the player on the given trailer.
     *
     * @param {HTMLElement} frame
     * @param {string} code
     */
    #build(frame, code) {
        this.#teardown()

        frame.setAttribute('player-src', 'https://www.youtube.com/watch?v=' + code)
        frame.replaceChildren(document.createElement('iframe'))

        this.#code = code
        this.#frame = frame
        this.#manager = new PlyrManager(frame, {
            ...TrailerHero.playerOptions,
            poster: frame.dataset.poster ?? '',
            youtube: {
                ...TrailerHero.playerOptions.youtube,
                origin: window.location.origin,
            },
        })

        this.#manager.player?.on('ended', () => this.#advance())

        if (this.#plays) {
            this.#manager.player?.play()
            this.#plays = false
        }
    }

    /**
     * Puts the given trailer in the player.
     *
     * @param {string} code
     * @param {boolean} plays
     */
    #load(code, plays) {
        const player = this.#manager?.player

        if (!player) {
            return
        }

        this.#code = code
        player.source = {
            type: 'video',
            sources: [{ src: code, provider: 'youtube' }],
        }

        if (plays) {
            player.play()
        }
    }

    /**
     * Releases the player.
     */
    #teardown() {
        this.#manager?.destroy()
        this.#manager = null
        this.#frame = null
    }

    /**
     * Puts the given entry in the player and sends the one it replaces to the back of the queue.
     *
     * @param {HTMLElement} entry
     */
    #rotate(entry) {
        if (!this.#manager || entry === this.#playing) {
            return
        }

        if (this.#playing) {
            this.#playing.classList.remove('hidden')
            this.#queue.appendChild(this.#playing)
        }

        this.#playing = entry
        entry.classList.add('hidden')

        this.#describe(entry)
        this.#load(entry.dataset.code, true)
    }

    /**
     * Moves on to the trailer behind the one that just finished.
     */
    #advance() {
        if (this.#wide) {
            const next = this.#queue?.querySelector('[data-trailer-hero-item]:not(.hidden)')

            if (next) {
                this.#rotate(next)
            }

            return
        }

        const slides = this.#slides()
        const next = slides[(this.#index + 1) % slides.length]

        if (next) {
            this.#plays = true
            this.#track.scrollTo({ left: next.offsetLeft - this.#track.offsetLeft, behavior: 'smooth' })
        }
    }

    /**
     * Fades the caption over to the given trailer.
     *
     * @param {HTMLElement} source
     */
    #describe(source) {
        const caption = this.#root.querySelector('[data-trailer-hero-caption]')
        const title = this.#root.querySelector('[data-trailer-hero-title]')
        const meta = this.#root.querySelector('[data-trailer-hero-meta]')
        const link = this.#root.querySelector('[data-trailer-hero-link]')

        if (!caption || title?.textContent === source.dataset.title) {
            return
        }

        caption.classList.add('opacity-0')

        window.setTimeout(() => {
            if (title) {
                title.textContent = source.dataset.title
            }

            if (meta) {
                meta.textContent = source.dataset.meta
            }

            if (link) {
                link.href = source.dataset.url
            }

            this.#reveal(source.dataset.code)

            caption.classList.remove('opacity-0')
        }, TrailerHero.captionFade)
    }
    /**
     * Shows the library button belonging to the given trailer, and hides the rest.
     *
     * @param {string} code
     */
    #reveal(code) {
        this.#root.querySelectorAll('[data-trailer-hero-action]').forEach((action) => {
            action.classList.toggle('hidden', action.dataset.code !== code)
        })
    }

}
