export default class LyricsManager {
    // MARK: - Constants
    /**
     * The distance a word lifts as it fills, in pixels.
     *
     * @type {number}
     */
    static #activeWordLift = 3

    /**
     * The blur added per line of distance from the active line, in pixels.
     *
     * @type {number}
     */
    static #blurRadiusPerLine = 1.5

    /**
     * The maximum blur radius, in pixels.
     *
     * @type {number}
     */
    static #maxBlurRadius = 7

    /**
     * The lead before the next line at which an interlude's closing breath begins, in milliseconds.
     *
     * @type {number}
     */
    static #interludeFinishLeadMs = 800

    /**
     * The gap length that maps to a single interlude breath, in milliseconds.
     *
     * @type {number}
     */
    static #interludeBreathBucketMs = 9000

    /**
     * The local storage key for the pronunciation preference.
     *
     * @type {string}
     */
    static #romajiKey = 'kurozora.lyrics.romaji'

    /**
     * The local storage key for the translation language preference.
     *
     * @type {string}
     */
    static #translationKey = 'kurozora.lyrics.translation'

    // MARK: - Properties
    /**
     * The lyrics overlay root, or null when closed.
     *
     * @type {HTMLElement|null}
     */
    #root = null

    /**
     * The scrolling list of lines and interludes, or null when closed.
     *
     * @type {HTMLElement|null}
     */
    #list = null

    /**
     * The lines and interludes in document order.
     *
     * @type {object[]}
     */
    #items = []

    /**
     * The opened song's Apple Music id.
     *
     * @type {string}
     */
    #amID = ''

    /**
     * The timing offset applied to every cue, in milliseconds.
     *
     * @type {number}
     */
    #offsetMs = 0

    /**
     * The singing agents keyed by id, each mapped to its type.
     *
     * @type {Object<string, string>}
     */
    #agents = {}

    /**
     * The active item's index, or -1 when none is active.
     *
     * @type {number}
     */
    #activeIndex = -1

    /**
     * The sync loop's animation frame handle.
     *
     * @type {number}
     */
    #rafID = 0

    /**
     * Whether playback was playing on the previous frame.
     *
     * @type {boolean}
     */
    #wasPlaying = false

    /**
     * Whether the overlay is animating closed.
     *
     * @type {boolean}
     */
    #closing = false

    /**
     * Whether pronunciation lines are shown.
     *
     * @type {boolean}
     */
    #showsRomaji = localStorage.getItem(LyricsManager.#romajiKey) !== '0'

    /**
     * The selected translation language, or empty when off.
     *
     * @type {string}
     */
    #translationLanguage = localStorage.getItem(LyricsManager.#translationKey) || ''

    /**
     * The localized labels for the options menu.
     *
     * @type {{translation: string, off: string, pronunciation: string}}
     */
    #labels = { translation: 'Translation', off: 'Off', pronunciation: 'Pronunciation' }

    /**
     * The bound Escape-key handler, or null when unbound.
     *
     * @type {((event: KeyboardEvent) => void)|null}
     */
    #onKeyDown = null

    /**
     * The reported playback time anchoring the position estimate, in seconds.
     *
     * @type {number}
     */
    #anchorReported = -1

    /**
     * The wall-clock time captured with the anchor, in milliseconds.
     *
     * @type {number}
     */
    #anchorWall = 0

    /**
     * The scroll animation's frame handle.
     *
     * @type {number}
     */
    #scrollRAF = 0

    /**
     * The pin animation's frame handle.
     *
     * @type {number}
     */
    #pinRAF = 0

    /**
     * The timeout id clearing the scrolling state after the user stops.
     *
     * @type {number}
     */
    #scrollEndTimer = 0

    /**
     * The wall-clock time until which user scrolling suppresses auto-scroll, in milliseconds.
     *
     * @type {number}
     */
    #userScrollUntil = 0

    /**
     * The wall-clock time of the last drift correction, in milliseconds.
     *
     * @type {number}
     */
    #lastDriftSync = 0

    // MARK: - Initializers
    constructor() {
        document.addEventListener('livewire:init', () => {
            Livewire.on('lyrics-opened', () => requestAnimationFrame(() => this.#open()))
            Livewire.on('lyrics-closed', () => this.#teardown())
        })

        document.addEventListener('livewire:navigated', () => this.#teardown())
    }

    // MARK: - Accessors
    /**
     * The shared music manager, once configured.
     *
     * @returns {MusicManager|null}
     */
    get #manager() {
        return window.musicManager ?? null
    }

    /**
     * Whether playback can be time-synced to the lyrics.
     *
     * @returns {boolean}
     */
    get #canTimeSync() {
        return this.#manager?.isAuthorized ?? false
    }

    /**
     * Whether the opened song is the one loaded in the player.
     *
     * @returns {boolean}
     */
    get #isCurrentSong() {
        return !!this.#amID && this.#manager?.shared?.nowPlayingItem?.id === this.#amID
    }

    /**
     * The current playback position in milliseconds.
     *
     * @returns {number}
     */
    #positionMs() {
        if (!this.#isCurrentSong) {
            this.#anchorReported = -1
            return 0
        }

        const reported = this.#manager.shared.currentPlaybackTime ?? 0
        const now = performance.now()

        if (reported !== this.#anchorReported) {
            this.#anchorReported = reported
            this.#anchorWall = now
        }

        const seconds = this.#isPlaying() ? reported + (now - this.#anchorWall) / 1000 : reported
        return Math.round(seconds * 1000)
    }

    /**
     * Whether the opened song is currently playing.
     *
     * @returns {boolean}
     */
    #isPlaying() {
        return this.#isCurrentSong && this.#manager?.shared?.playbackState === MusicKit.PlaybackStates.playing
    }

    // MARK: - Open
    /**
     * Binds to the rendered overlay and begins the sync.
     */
    #open() {
        this.#root = document.querySelector('[data-lyrics]')
        this.#list = this.#root?.querySelector('[data-lyrics-list]')
        if (!this.#root || !this.#list) {
            return
        }

        this.#closing = false
        this.#amID = this.#root.dataset.amId || ''
        this.#offsetMs = parseInt(this.#root.dataset.offsetMs, 10) || 0
        this.#labels = {
            translation: this.#root.dataset.labelTranslation || 'Translation',
            off: this.#root.dataset.labelOff || 'Off',
            pronunciation: this.#root.dataset.labelPronunciation || 'Pronunciation'
        }
        try {
            this.#agents = Object.fromEntries((JSON.parse(this.#root.dataset.agents || '[]')).map((agent) => [agent.key, (agent.type || '').toLowerCase()]))
        } catch (error) {
            this.#agents = {}
        }

        this.#buildModel()
        this.#computeAlignments()
        this.#applyOptions()
        this.#buildOptionsMenu()
        this.#bindEvents()

        const hasLines = this.#items.some((item) => item?.type === 'line')

        if (this.#canTimeSync && hasLines) {
            this.#performInitialSync()
            this.#start()
        } else {
            this.#list.dataset.static = ''
        }

        requestAnimationFrame(() => this.#root?.setAttribute('data-open', ''))
    }

    /**
     * Reads the rendered lines and interludes into the item model.
     */
    #buildModel() {
        this.#items = []
        this.#activeIndex = -1

        this.#list.querySelectorAll('[data-line], [data-interlude]').forEach((element) => {
            const index = parseInt(element.dataset.index, 10)

            if (element.hasAttribute('data-interlude')) {
                this.#items[index] = {
                    type: 'interlude',
                    element,
                    rawStartMs: parseInt(element.dataset.startMs, 10) || 0,
                    endMs: parseInt(element.dataset.endMs, 10) || 0,
                    finishing: false,
                    animation: null
                }
                return
            }

            const beginMs = element.hasAttribute('data-begin') ? parseInt(element.dataset.begin, 10) : null
            const words = [...element.querySelectorAll('.lyrics-tile')].map((tile) => ({
                layers: [...tile.querySelectorAll('.lyrics-romaji, .lyrics-word')],
                beginMs: parseInt(tile.dataset.begin, 10) || 0,
                endMs: parseInt(tile.dataset.end, 10) || 0
            }))

            this.#items[index] = {
                type: 'line',
                element,
                rawStartMs: beginMs ?? 0,
                beginMs,
                hasWordTiming: element.hasAttribute('data-word-timing'),
                words,
                animations: null
            }
        })
    }

    /**
     * Resolves each line's alignment, alternating sides as the singing agent changes.
     */
    #computeAlignments() {
        let lastPersonAgent = null
        let lastPersonWasDuet = false

        for (const item of this.#items) {
            if (item.type !== 'line') {
                continue
            }

            const agent = item.element.dataset.agent || ''
            const type = this.#agents[agent]
            let isDuet

            if (type === 'group') {
                isDuet = false
            } else if (lastPersonAgent === null) {
                isDuet = type === 'other'
                lastPersonAgent = agent
                lastPersonWasDuet = isDuet
            } else if (lastPersonAgent === agent) {
                isDuet = lastPersonWasDuet
            } else {
                isDuet = !lastPersonWasDuet
                lastPersonAgent = agent
                lastPersonWasDuet = isDuet
            }

            if (isDuet) {
                item.element.dataset.align = 'right'
            }
        }
    }

    /**
     * Reflects the saved pronunciation and translation preferences in the DOM.
     */
    #applyOptions() {
        this.#list.toggleAttribute('data-show-romaji', this.#showsRomaji)

        this.#list.querySelectorAll('[data-translation]').forEach((translation) => {
            translation.hidden = translation.dataset.language !== this.#translationLanguage
        })
    }

    /**
     * Builds the floating options menu from the available pronunciations and translations.
     */
    #buildOptionsMenu() {
        const menu = this.#root.querySelector('[data-lyrics-options-menu]')
        const button = this.#root.querySelector('[data-lyrics-options]')
        if (!menu || !button) {
            return
        }

        const hasRomaji = !!this.#list.querySelector('[data-romaji]')
        const languages = [...new Set([...this.#list.querySelectorAll('[data-translation]')].map((translation) => translation.dataset.language))]
        const itemClass = 'flex items-center gap-2 w-full pl-2 pr-2 pt-1.5 pb-1.5 text-xs text-left rounded-lg hover:bg-tint hover:btn-text-tinted'

        const check = (active) => `<span class="w-3 shrink-0">${active ? '✓' : ''}</span>`
        const rows = []

        if (languages.length) {
            const display = (code) => {
                try {
                    return new Intl.DisplayNames([navigator.language], { type: 'language' }).of(code) || code
                } catch (error) {
                    return code
                }
            }
            rows.push(`<p class="pl-2 pr-2 pt-1.5 pb-1 text-[11px] uppercase tracking-wide text-secondary">${this.#labels.translation}</p>`)
            rows.push(`<button type="button" class="${itemClass}" data-set-translation="">${check(!this.#translationLanguage)}<span>${this.#labels.off}</span></button>`)
            for (const language of languages) {
                rows.push(`<button type="button" class="${itemClass}" data-set-translation="${language}">${check(this.#translationLanguage === language)}<span>${display(language)}</span></button>`)
            }
        }

        if (hasRomaji) {
            if (rows.length) {
                rows.push('<hr class="my-1 border-primary">')
            }
            rows.push(`<button type="button" class="${itemClass}" data-toggle-romaji>${check(this.#showsRomaji)}<span>${this.#labels.pronunciation}</span></button>`)
        }

        menu.innerHTML = rows.join('')
        button.classList.toggle('hidden', rows.length === 0)
    }

    /**
     * Re-applies the options and keeps the active line anchored after a height change.
     */
    #afterOptionsChange() {
        const item = this.#rowToCenter()
        const anchorTop = item ? this.#lineViewportTop(item) : null

        this.#applyOptions()
        this.#buildOptionsMenu()

        if (item) {
            this.#pinActiveLine(item, anchorTop, 400)
        }
    }

    /**
     * The line's top, relative to the list viewport.
     *
     * @param {object} item - the line item
     *
     * @returns {number}
     */
    #lineViewportTop(item) {
        return item.element.getBoundingClientRect().top - this.#list.getBoundingClientRect().top
    }

    /**
     * Holds a line at a fixed viewport position while surrounding heights change.
     *
     * @param {object} item - the line item to hold
     * @param {number} targetTop - the viewport top to hold it at
     * @param {number} durationMs - how long to keep holding
     */
    #pinActiveLine(item, targetTop, durationMs) {
        cancelAnimationFrame(this.#pinRAF)
        cancelAnimationFrame(this.#scrollRAF)
        const start = performance.now()

        const step = (now) => {
            if (this.#rowToCenter() !== item) {
                return
            }
            this.#list.scrollTop += this.#lineViewportTop(item) - targetTop
            if (now - start < durationMs) {
                this.#pinRAF = requestAnimationFrame(step)
            }
        }
        this.#pinRAF = requestAnimationFrame(step)
    }

    /**
     * Wires the overlay's controls.
     */
    #bindEvents() {
        this.#root.querySelector('[data-lyrics-close]')?.addEventListener('click', () => this.#close())

        const optionsButton = this.#root.querySelector('[data-lyrics-options]')
        const menu = this.#root.querySelector('[data-lyrics-options-menu]')
        optionsButton?.addEventListener('click', () => menu?.toggleAttribute('hidden'))

        menu?.addEventListener('click', (event) => {
            const translation = event.target.closest('[data-set-translation]')
            if (translation) {
                this.#translationLanguage = translation.dataset.setTranslation
                localStorage.setItem(LyricsManager.#translationKey, this.#translationLanguage)
                this.#afterOptionsChange()
                return
            }

            if (event.target.closest('[data-toggle-romaji]')) {
                this.#showsRomaji = !this.#showsRomaji
                localStorage.setItem(LyricsManager.#romajiKey, this.#showsRomaji ? '1' : '0')
                this.#afterOptionsChange()
            }
        })

        this.#list.addEventListener('click', (event) => {
            const lineElement = event.target.closest('[data-line]')
            if (lineElement) {
                this.#seekToLine(this.#items[parseInt(lineElement.dataset.index, 10)])
            }
        })

        const markUserScroll = () => {
            this.#userScrollUntil = performance.now() + 4000
            this.#list.setAttribute('data-scrolling', '')
            clearTimeout(this.#scrollEndTimer)
            this.#scrollEndTimer = setTimeout(() => this.#list?.removeAttribute('data-scrolling'), 600)
        }
        this.#list.addEventListener('wheel', markUserScroll, { passive: true })
        this.#list.addEventListener('touchmove', markUserScroll, { passive: true })

        this.#items.forEach((item) => {
            if (item.type !== 'line') {
                return
            }
            item.element.addEventListener('mouseenter', () => this.#onHover(item, true))
            item.element.addEventListener('mouseleave', () => this.#onHover(item, false))
        })

        if (this.#onKeyDown) {
            document.removeEventListener('keydown', this.#onKeyDown)
        }
        this.#onKeyDown = (event) => event.key === 'Escape' && this.#close()
        document.addEventListener('keydown', this.#onKeyDown)
    }

    /**
     * Seeks playback to a line's start.
     *
     * @param {object} item - the line item
     */
    #seekToLine(item) {
        if (!this.#canTimeSync || !item || item.type !== 'line' || item.beginMs === null || !this.#isCurrentSong) {
            return
        }

        this.#manager.seekTo((item.beginMs + this.#offsetMs) / 1000)
        this.#lastDriftSync = 0
        if (!this.#isPlaying()) {
            this.#manager.togglePlayPause()
        }
    }

    /**
     * Reveals or dims an inactive line on hover.
     *
     * @param {object} item - the line item
     * @param {boolean} isHovered - whether the pointer is over the line
     */
    #onHover(item, isHovered) {
        if (!this.#canTimeSync || item === this.#items[this.#activeIndex]) {
            return
        }

        item.element.style.filter = isHovered ? 'none' : this.#lineFilter(this.#itemIndex(item))
    }

    // MARK: - Sync
    /**
     * Starts the per-frame sync loop.
     */
    #start() {
        this.#stop()
        const tick = () => {
            this.#tick()
            this.#rafID = requestAnimationFrame(tick)
        }
        this.#rafID = requestAnimationFrame(tick)
    }

    /**
     * Stops the sync loop.
     */
    #stop() {
        cancelAnimationFrame(this.#rafID)
        this.#rafID = 0
    }

    /**
     * Advances the active item, scroll, and fills to the current playback position.
     */
    #tick() {
        if (!this.#root?.isConnected) {
            this.#teardown()
            return
        }

        const positionMs = this.#positionMs()

        const newIndex = this.#activeItemIndex(positionMs)
        if (newIndex !== this.#activeIndex) {
            this.#setActive(newIndex)
        }

        const playing = this.#isPlaying()
        if (playing !== this.#wasPlaying) {
            this.#wasPlaying = playing
            this.#syncActiveLine()
            this.#refreshBlur()
        } else if (playing && performance.now() - this.#lastDriftSync > 500) {
            this.#correctDrift()
            this.#lastDriftSync = performance.now()
        }

        const active = this.#items[this.#activeIndex]
        if (active?.type === 'interlude') {
            this.#updateInterlude(active, (active.endMs + this.#offsetMs) - positionMs, active.endMs - active.rawStartMs)
        }
    }

    /**
     * The index of the last item that has started by the given position.
     *
     * @param {number} positionMs - the playback position in milliseconds
     *
     * @returns {number}
     */
    #activeItemIndex(positionMs) {
        if (!this.#isCurrentSong) {
            return -1
        }

        let index = -1
        for (let itemIndex = 0; itemIndex < this.#items.length; itemIndex++) {
            if ((this.#items[itemIndex].rawStartMs + this.#offsetMs) <= positionMs) {
                index = itemIndex
            } else {
                break
            }
        }
        return index
    }

    /**
     * Computes the active item once at open, styles every line and jumps to it.
     */
    #performInitialSync() {
        this.#activeIndex = this.#activeItemIndex(this.#positionMs())

        const active = this.#items[this.#activeIndex]
        if (active?.type === 'line') {
            this.#activate(active)
        } else if (active?.type === 'interlude') {
            active.element.setAttribute('data-active', '')
        }

        this.#refreshBlur()
        if (this.#activeIndex >= 0) {
            this.#scrollToActive(false)
        }
    }

    /**
     * Moves the active styling to a new item.
     *
     * @param {number} newIndex - the new active item index
     */
    #setActive(newIndex) {
        const previous = this.#items[this.#activeIndex]
        if (previous?.type === 'line') {
            this.#deactivate(previous)
        } else if (previous?.type === 'interlude') {
            previous.element.removeAttribute('data-active')
            this.#resetInterlude(previous)
        }

        this.#activeIndex = newIndex
        const current = this.#items[newIndex]

        if (current?.type === 'line') {
            this.#activate(current)
        } else if (current?.type === 'interlude') {
            current.finishing = false
            current.element.setAttribute('data-active', '')
        }

        this.#refreshBlur()
        this.#scrollToActive(true)
    }

    /**
     * Activates a line, attaching a browser-driven fill animation to each syllable.
     *
     * @param {object} item - the line item
     */
    #activate(item) {
        item.element.setAttribute('data-active', '')
        this.#cancelAnimations(item)

        if (!item.hasWordTiming) {
            item.words.forEach((word) => word.layers.forEach((layer) => layer.style.setProperty('--fill', '120%')))
            return
        }

        item.animations = []

        for (const word of item.words) {
            const duration = Math.max(1, word.endMs - word.beginMs)
            const delay = Math.max(0, word.beginMs - item.rawStartMs)

            for (const layer of word.layers) {
                const animation = layer.animate(
                    [
                        { '--fill': '0%', transform: 'translateY(0)' },
                        { '--fill': '120%', transform: `translateY(-${LyricsManager.#activeWordLift}px)` }
                    ],
                    { duration, delay, fill: 'both', easing: 'linear' }
                )
                animation.pause()
                item.animations.push(animation)
            }
        }

        this.#syncActiveLine()
    }

    /**
     * Deactivates a line, cancelling its fill animations.
     *
     * @param {object} item - the line item
     */
    #deactivate(item) {
        item.element.removeAttribute('data-active')
        this.#cancelAnimations(item)
        item.words.forEach((word) => word.layers.forEach((layer) => layer.style.removeProperty('--fill')))
    }

    /**
     * Cancels any fill animations attached to a line.
     *
     * @param {object} item - the line item
     */
    #cancelAnimations(item) {
        if (item.animations) {
            item.animations.forEach((animation) => animation.cancel())
            item.animations = null
        }
    }

    /**
     * Reseeks the active line's fill animations to the playback position and plays or pauses them.
     */
    #syncActiveLine() {
        const item = this.#items[this.#activeIndex]
        if (item?.type !== 'line' || !item.animations) {
            return
        }

        const elapsed = Math.max(0, this.#positionMs() - (item.rawStartMs + this.#offsetMs))
        const playing = this.#isPlaying()
        this.#lastDriftSync = performance.now()

        for (const animation of item.animations) {
            animation.currentTime = elapsed
            if (playing) {
                if (animation.playState !== 'finished') {
                    animation.play()
                }
            } else {
                animation.pause()
            }
        }
    }

    /**
     * Corrects slow clock drift between the browser timeline and playback, without restarting.
     */
    #correctDrift() {
        const item = this.#items[this.#activeIndex]
        if (item?.type !== 'line' || !item.animations || !this.#isPlaying()) {
            return
        }

        const elapsed = Math.max(0, this.#positionMs() - (item.rawStartMs + this.#offsetMs))
        for (const animation of item.animations) {
            if (animation.playState !== 'finished') {
                animation.currentTime = elapsed
            }
        }
    }

    // MARK: - Blur
    /**
     * Re-applies the distance blur to every line.
     */
    #refreshBlur() {
        this.#items.forEach((item) => {
            if (item.type === 'line') {
                item.element.style.filter = this.#lineFilter(this.#itemIndex(item))
            }
        })
    }

    /**
     * The blur filter for a line at the given item index.
     *
     * @param {number} index - the line's item index
     *
     * @returns {string}
     */
    #lineFilter(index) {
        if (!this.#canTimeSync || !this.#isPlaying() || this.#activeIndex < 0 || index === this.#activeIndex) {
            return 'none'
        }

        const radius = Math.min(LyricsManager.#maxBlurRadius, Math.abs(index - this.#activeIndex) * LyricsManager.#blurRadiusPerLine)
        return radius > 0 ? `blur(${radius}px)` : 'none'
    }

    /**
     * The item index of a given item.
     *
     * @param {object} item - the item
     *
     * @returns {number}
     */
    #itemIndex(item) {
        return this.#items.indexOf(item)
    }

    // MARK: - Scroll
    /**
     * Scrolls the active line to the focal point.
     *
     * @param {boolean} animated - whether to animate the scroll
     */
    #scrollToActive(animated) {
        if (performance.now() < this.#userScrollUntil) {
            return
        }

        const item = this.#rowToCenter()
        if (!item) {
            return
        }

        const listRect = this.#list.getBoundingClientRect()
        const elementRect = item.element.getBoundingClientRect()
        const target = Math.max(0, this.#list.scrollTop + (elementRect.top - listRect.top) - this.#list.clientHeight * 0.35)

        if (!animated) {
            cancelAnimationFrame(this.#scrollRAF)
            this.#list.scrollTop = target
            return
        }

        this.#animateScroll(target)
    }

    /**
     * Eases the list to a target scroll position, replacing any in-flight scroll.
     *
     * @param {number} target - the destination scrollTop
     */
    #animateScroll(target) {
        cancelAnimationFrame(this.#scrollRAF)

        const start = this.#list.scrollTop
        const distance = target - start
        if (Math.abs(distance) < 1) {
            return
        }

        const duration = 600
        const startTime = performance.now()
        const ease = (progress) => 1 - Math.pow(1 - progress, 3)

        const step = (now) => {
            const progress = Math.min(1, (now - startTime) / duration)
            this.#list.scrollTop = start + distance * ease(progress)
            if (progress < 1) {
                this.#scrollRAF = requestAnimationFrame(step)
            }
        }
        this.#scrollRAF = requestAnimationFrame(step)
    }

    /**
     * The item to anchor at the focal point, stepping back from an interlude to its line.
     *
     * @returns {object|null}
     */
    #rowToCenter() {
        const active = this.#items[this.#activeIndex]
        if (!active) {
            return null
        }
        if (active.type === 'line') {
            return active
        }

        for (let index = this.#activeIndex - 1; index >= 0; index--) {
            if (this.#items[index].type === 'line') {
                return this.#items[index]
            }
        }
        return null
    }

    // MARK: - Interlude
    /**
     * Advances an interlude's breathing and dot fill.
     *
     * @param {object} item - the interlude item
     * @param {number} remainingMs - the time left before the next line
     * @param {number} totalMs - the full gap duration
     */
    #updateInterlude(item, remainingMs, totalMs) {
        const dots = item.element.querySelector('[data-interlude-dots]')
        if (!dots) {
            return
        }

        if (!item.finishing && remainingMs <= LyricsManager.#interludeFinishLeadMs) {
            this.#finishInterlude(item, dots)
            return
        }
        if (item.finishing) {
            return
        }

        const breathSpan = Math.max(1, totalMs - LyricsManager.#interludeFinishLeadMs)
        const elapsed = Math.max(0, totalMs - remainingMs)
        const progress = Math.min(1, elapsed / breathSpan)

        ;[...dots.children].forEach((dot, index) => {
            const local = Math.max(0, Math.min(1, (progress - index / 3) * 3))
            dot.style.opacity = String(0.25 + 0.75 * local)
        })

        const breathCount = Math.max(1, Math.ceil(totalMs / LyricsManager.#interludeBreathBucketMs))
        const withinBreath = (progress * breathCount) % 1
        const scale = this.#breathScale(withinBreath)
        dots.style.transform = `scale(${scale})`
    }

    /**
     * The dot scale at a point within a single breath.
     *
     * @param {number} progress - the progress through the breath
     *
     * @returns {number}
     */
    #breathScale(progress) {
        const amplitude = 0.16
        const smoothstep = (value) => {
            const clamped = Math.max(0, Math.min(1, value))
            return clamped * clamped * (3 - 2 * clamped)
        }

        let level
        if (progress < 0.55) {
            level = smoothstep(progress / 0.55)
        } else if (progress < 0.63) {
            level = 1
        } else if (progress < 0.78) {
            level = 1 - smoothstep((progress - 0.63) / 0.15)
        } else {
            level = 0
        }

        return 1 + amplitude * level
    }

    /**
     * Plays an interlude's closing breath, then collapses it.
     *
     * @param {object} item - the interlude item
     * @param {HTMLElement} dots - the dots container
     */
    #finishInterlude(item, dots) {
        item.finishing = true
        item.animation = dots.animate(
            [
                { transform: 'scale(1)', opacity: 1, offset: 0 },
                { transform: 'scale(1.4)', opacity: 1, offset: 0.45 },
                { transform: 'scale(1.4)', opacity: 1, offset: 0.6 },
                { transform: 'scale(0)', opacity: 0, offset: 1 }
            ],
            { duration: 700, easing: 'ease-out', fill: 'forwards' }
        )
    }

    /**
     * Resets an interlude's dots for reuse.
     *
     * @param {object} item - the interlude item
     */
    #resetInterlude(item) {
        item.finishing = false
        item.animation?.cancel()
        item.animation = null

        const dots = item.element.querySelector('[data-interlude-dots]')
        if (dots) {
            dots.style.transform = ''
            ;[...dots.children].forEach((dot) => (dot.style.opacity = ''))
        }
    }

    // MARK: - Teardown
    /**
     * Animates the overlay out, then asks the component to clear it.
     */
    #close() {
        if (this.#closing) {
            return
        }
        this.#closing = true

        this.#stop()
        this.#root?.removeAttribute('data-open')
        setTimeout(() => window.Livewire?.dispatch('close-lyrics'), 300)
    }

    /**
     * Releases the loop and listeners once the overlay is gone.
     */
    #teardown() {
        this.#stop()
        cancelAnimationFrame(this.#scrollRAF)
        cancelAnimationFrame(this.#pinRAF)
        clearTimeout(this.#scrollEndTimer)
        if (this.#onKeyDown) {
            document.removeEventListener('keydown', this.#onKeyDown)
            this.#onKeyDown = null
        }
        this.#root = null
        this.#list = null
        this.#items = []
        this.#activeIndex = -1
        this.#closing = false
    }
}
