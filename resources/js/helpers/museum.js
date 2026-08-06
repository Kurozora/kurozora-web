export default class Museum {
    /**
     * Poster width in pixels, matching the `w-28` utility.
     *
     * @type {number}
     */
    static #itemWidth = 112

    /**
     * Gap between posters in pixels, matching the `gap-2` utility.
     *
     * @type {number}
     */
    static #gap = 8

    /**
     * The collection-scale display modes.
     *
     * @type {{Total: number, Year: number}}
     */
    static #ScaleMode = Object.freeze({
        Total: 0,
        Year: 1
    })

    /**
     * The museum root element carrying the title and deeplink templates.
     *
     * @type {?HTMLElement}
     */
    #root = null

    /**
     * The base URL a year's posters are fetched from.
     *
     * @type {string}
     */
    #endpoint = ''

    /**
     * Poster height in pixels for the current kind.
     *
     * @type {number}
     */
    #itemHeight = 160

    /**
     * The template whose poster shell is cloned for each entry.
     *
     * @type {?HTMLElement}
     */
    #templateNode = null

    /**
     * The horizontally scrolling hall that holds every year.
     *
     * @type {?HTMLElement}
     */
    #scroller = null

    /**
     * The discreet year picker beside the title.
     *
     * @type {?HTMLSelectElement}
     */
    #select = null

    /**
     * The button that returns the hall to the current year.
     *
     * @type {?HTMLElement}
     */
    #recenter = null

    /**
     * The density timeline track holding the year ticks.
     *
     * @type {?HTMLElement}
     */
    #track = null

    /**
     * The floating chip shown while hovering or scrubbing the timeline.
     *
     * @type {?HTMLElement}
     */
    #chip = null

    /**
     * The collection-scale line beside the title.
     *
     * @type {?HTMLElement}
     */
    #scale = null

    /**
     * The full collection-scale text shown while idle.
     *
     * @type {string}
     */
    #scaleFull = ''

    /**
     * The timer that restores the full scale after scrolling stops.
     *
     * @type {number}
     */
    #scaleTimer = 0

    /**
     * The current scale display mode.
     *
     * @type {number}
     */
    #scaleMode = Museum.#ScaleMode.Total

    /**
     * The pending scale cross-fade timer.
     *
     * @type {number}
     */
    #scaleFadeTimer = 0

    /**
     * The year columns in display order.
     *
     * @type {HTMLElement[]}
     */
    #columns = []

    /**
     * Each column's content-space left edge in pixels, indexed with the columns.
     *
     * @type {number[]}
     */
    #lefts = []

    /**
     * Each column's content-space right edge in pixels, indexed with the columns.
     *
     * @type {number[]}
     */
    #rights = []

    /**
     * The column index for a given year.
     *
     * @type {Map<string, number>}
     */
    #indexByYear = new Map()

    /**
     * The scroller's visible content width in pixels.
     *
     * @type {number}
     */
    #viewportWidth = 0

    /**
     * The scroller's left padding in pixels.
     *
     * @type {number}
     */
    #paddingLeft = 0

    /**
     * The scroller's right padding in pixels.
     *
     * @type {number}
     */
    #paddingRight = 0

    /**
     * The timeline ticks in display order.
     *
     * @type {HTMLElement[]}
     */
    #ticks = []

    /**
     * The index of the currently highlighted tick.
     *
     * @type {number}
     */
    #activeTickIndex = -1

    /**
     * The decade labels beneath the timeline.
     *
     * @type {HTMLElement[]}
     */
    #decadeLabels = []

    /**
     * Years whose posters are currently mounted in the DOM.
     *
     * @type {Set<string>}
     */
    #loaded = new Set()

    /**
     * Years whose posters are currently being fetched.
     *
     * @type {Set<string>}
     */
    #inflight = new Set()

    /**
     * Debounce timers for years awaiting load, keyed by year.
     *
     * @type {Map<string, number>}
     */
    #pendingLoads = new Map()

    /**
     * Fetched posters keyed by year, kept across mounts to avoid refetching.
     *
     * @type {Map<string, object[]>}
     */
    #cache = new Map()

    /**
     * The number of posters that fit in a single column at the current height.
     *
     * @type {number}
     */
    #itemsPerColumn = 1

    /**
     * The year currently at the leading edge of the hall.
     *
     * @type {?string}
     */
    #activeYear = null

    /**
     * The hall's configured height before it is snapped to whole poster rows.
     *
     * @type {string}
     */
    #baseHeight = ''

    /**
     * Whether the viewer prefers reduced motion.
     *
     * @type {boolean}
     */
    #reducedMotion = false

    /**
     * The reduced-motion media query being observed.
     *
     * @type {?MediaQueryList}
     */
    #reducedMotionQuery = null

    /**
     * Whether the timeline is currently being scrubbed.
     *
     * @type {boolean}
     */
    #timelineScrubbing = false

    /**
     * Whether the museum has settled after mount and may react to scrolling.
     *
     * @type {boolean}
     */
    #ready = false

    /**
     * The timer that marks the museum ready after mount.
     *
     * @type {number}
     */
    #readyTimer = 0

    /**
     * The toggle that dims entries already in the user's library.
     *
     * @type {?HTMLInputElement}
     */
    #libraryToggle = null

    /**
     * Whether library entries are currently dimmed.
     *
     * @type {boolean}
     */
    #dimLibrary = false

    /**
     * Observes columns nearing the viewport to trigger their fetch.
     *
     * @type {?IntersectionObserver}
     */
    #intersectionObserver = null

    /**
     * The pending scroll-sync animation frame.
     *
     * @type {number}
     */
    #scrollFrame = 0

    /**
     * The pending relayout timer.
     *
     * @type {number}
     */
    #resizeTimer = 0

    /**
     * Mounts the museum after every navigation, including the first load.
     */
    constructor() {
        document.addEventListener('livewire:navigated', () => this.#mount())
    }

    /**
     * Wires up the museum on the current page, or stays inert when absent.
     */
    #mount() {
        this.#unmount()

        const root = document.querySelector('[data-museum]')
        this.#root = root
        this.#endpoint = root?.dataset.museumEndpoint ?? ''
        this.#itemHeight = Number(root?.dataset.museumItemHeight) || 160
        this.#scroller = root?.querySelector('[data-museum-scroller]') ?? null

        if (!this.#scroller) {
            return
        }

        this.#templateNode = root.querySelector('[data-museum-poster]')?.content.firstElementChild ?? null
        this.#select = root.querySelector('[data-museum-select]')
        this.#recenter = root.querySelector('[data-museum-recenter]')
        this.#track = root.querySelector('[data-museum-track]')
        this.#chip = root.querySelector('[data-museum-chip]')
        this.#scale = root.querySelector('[data-museum-scale]')
        this.#scaleFull = this.#scale?.textContent ?? ''
        this.#libraryToggle = root.querySelector('[data-museum-library-toggle]')
        this.#dimLibrary = localStorage.getItem('museum-dim-library') === 'true'
        this.#reflectLibraryToggle()
        this.#columns = Array.from(this.#scroller.querySelectorAll('[data-museum-year]'))
        this.#ticks = this.#track ? Array.from(this.#track.querySelectorAll('[data-museum-tick]')) : []
        this.#decadeLabels = Array.from(root.querySelector('[data-museum-decades]')?.children ?? [])
        this.#baseHeight = this.#scroller.style.height

        if (!this.#columns.length) {
            return
        }

        this.#reducedMotionQuery = matchMedia('(prefers-reduced-motion: reduce)')
        this.#reducedMotion = this.#reducedMotionQuery.matches

        this.#layout()
        this.#observe()
        this.#bind()

        this.#jumpToYear(this.#validYear(location.hash.slice(1)) ?? this.#scroller.dataset.museumCurrentYear, false)
        this.#syncActiveYear()

        this.#readyTimer = setTimeout(() => {
            this.#ready = true
        }, 300)
    }

    /**
     * Tears down observers and listeners from a previous page.
     */
    #unmount() {
        this.#intersectionObserver?.disconnect()
        window.removeEventListener('resize', this.#onResize)

        if (this.#scroller) {
            this.#scroller.removeEventListener('scroll', this.#onScroll)
            this.#scroller.removeEventListener('keydown', this.#onKeydown)
        }

        this.#select?.removeEventListener('change', this.#onSelect)
        this.#recenter?.removeEventListener('click', this.#onRecenter)
        this.#libraryToggle?.removeEventListener('click', this.#onLibraryToggle)

        if (this.#track) {
            this.#track.removeEventListener('pointerdown', this.#onTimelinePointerDown)
            this.#track.removeEventListener('pointermove', this.#onTimelinePointerMove)
            this.#track.removeEventListener('pointerup', this.#onTimelinePointerUp)
            this.#track.removeEventListener('pointercancel', this.#onTimelinePointerUp)
            this.#track.removeEventListener('pointerleave', this.#onTimelinePointerLeave)
        }

        this.#reducedMotionQuery?.removeEventListener('change', this.#onReducedMotionChange)

        cancelAnimationFrame(this.#scrollFrame)
        clearTimeout(this.#resizeTimer)
        clearTimeout(this.#scaleTimer)
        clearTimeout(this.#scaleFadeTimer)
        clearTimeout(this.#readyTimer)
        this.#scaleMode = Museum.#ScaleMode.Total
        this.#ready = false
        this.#dimLibrary = false
        this.#libraryToggle = null

        for (const timer of this.#pendingLoads.values()) {
            clearTimeout(timer)
        }

        this.#root = null
        this.#endpoint = ''
        this.#templateNode = null
        this.#scroller = null
        this.#select = null
        this.#track = null
        this.#scale = null
        this.#columns = []
        this.#lefts = []
        this.#rights = []
        this.#indexByYear = new Map()
        this.#ticks = []
        this.#activeTickIndex = -1
        this.#decadeLabels = []
        this.#loaded = new Set()
        this.#inflight = new Set()
        this.#pendingLoads = new Map()
        this.#activeYear = null
        this.#timelineScrubbing = false
    }

    /**
     * Measures column capacity, snaps the hall to whole poster rows, and reserves widths.
     */
    #layout() {
        this.#scroller.style.height = this.#baseHeight

        const styles = getComputedStyle(this.#scroller)
        this.#paddingLeft = parseFloat(styles.paddingLeft) || 0
        this.#paddingRight = parseFloat(styles.paddingRight) || 0
        const paddingTop = parseFloat(styles.paddingTop) || 0
        const paddingBottom = parseFloat(styles.paddingBottom) || 0

        const available = this.#scroller.clientHeight - paddingTop - paddingBottom
        this.#itemsPerColumn = Math.max(1, Math.floor((available + Museum.#gap) / (this.#itemHeight + Museum.#gap)))

        const rowsHeight = this.#itemsPerColumn * this.#itemHeight + (this.#itemsPerColumn - 1) * Museum.#gap
        const overflow = available - rowsHeight

        if (overflow > 0) {
            this.#scroller.style.height = `${this.#scroller.clientHeight - overflow}px`
        }

        for (const column of this.#columns) {
            const count = this.#loaded.has(column.dataset.museumYear)
                ? column.childElementCount
                : Number(column.dataset.museumCount) || 0
            const width = this.#columnWidth(count)

            column.style.height = `${rowsHeight}px`
            column.style.gridTemplateRows = `repeat(${this.#itemsPerColumn}, ${this.#itemHeight}px)`
            column.style.gridAutoColumns = `${Museum.#itemWidth}px`
            column.style.minWidth = `${width}px`
            column.style.containIntrinsicSize = `${width}px ${rowsHeight}px`
            column.style.contentVisibility = 'auto'
        }

        this.#measure()
        this.#layoutDecades()
    }

    /**
     * Caches each column's content-space bounds so scrolling never forces a layout.
     */
    #measure() {
        const scrollerRect = this.#scroller.getBoundingClientRect()
        const contentEdge = scrollerRect.left + this.#paddingLeft
        const scrollLeft = this.#scroller.scrollLeft
        this.#viewportWidth = this.#scroller.clientWidth - this.#paddingLeft - this.#paddingRight

        this.#lefts = new Array(this.#columns.length)
        this.#rights = new Array(this.#columns.length)
        this.#indexByYear = new Map()

        this.#columns.forEach((column, index) => {
            const rect = column.getBoundingClientRect()
            const left = scrollLeft + rect.left - contentEdge

            this.#lefts[index] = left
            this.#rights[index] = left + rect.width
            this.#indexByYear.set(column.dataset.museumYear, index)
        })
    }

    /**
     * Shows each decade label only where its slot is wide enough to fit it.
     */
    #layoutDecades() {
        for (const label of this.#decadeLabels) {
            label.textContent = label.dataset.label
            if (label.scrollWidth > label.clientWidth) {
                label.textContent = ''
            }
        }
    }

    /**
     * Returns the pixel width needed to hold the given number of posters.
     *
     * @param {number} count - the poster count
     *
     * @returns {number}
     */
    #columnWidth(count) {
        const columns = Math.max(1, Math.ceil(count / this.#itemsPerColumn))
        return columns * Museum.#itemWidth + (columns - 1) * Museum.#gap
    }

    /**
     * Fetches a year's posters once its column nears the viewport.
     */
    #observe() {
        this.#intersectionObserver = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    const year = entry.target.dataset.museumYear

                    if (entry.isIntersecting) {
                        if (this.#loaded.has(year) || this.#pendingLoads.has(year)) {
                            continue
                        }

                        this.#pendingLoads.set(year, setTimeout(() => {
                            this.#pendingLoads.delete(year)
                            this.#loadYear(entry.target)
                        }, 150))
                    } else {
                        clearTimeout(this.#pendingLoads.get(year))
                        this.#pendingLoads.delete(year)
                    }
                }
            },
            { root: this.#scroller, rootMargin: '0px 150% 0px 150%', threshold: 0 }
        )

        for (const column of this.#columns) {
            this.#intersectionObserver.observe(column)
        }
    }

    /**
     * Loads a year's posters from cache or the network, then renders them.
     *
     * @param {HTMLElement} column - the year column to fill
     */
    async #loadYear(column) {
        const year = column.dataset.museumYear

        if (this.#loaded.has(year) || this.#inflight.has(year)) {
            return
        }

        if (this.#cache.has(year)) {
            this.#renderYear(column, this.#cache.get(year))
            return
        }

        this.#inflight.add(year)

        try {
            const response = await fetch(`${this.#endpoint}/${year}`, { headers: { Accept: 'application/json' } })

            if (!response.ok) {
                throw new Error(`Museum failed to load ${year}: ${response.status}`)
            }

            const items = await response.json()
            this.#cache.set(year, items)
            this.#renderYear(column, items)
        } catch (error) {
            console.error(error)
        } finally {
            this.#inflight.delete(year)
        }
    }

    /**
     * Renders a year's posters into its column.
     *
     * @param {HTMLElement} column - the year column to fill
     * @param {object[]} items - the poster payloads
     */
    #renderYear(column, items) {
        if (!this.#templateNode) {
            return
        }

        const fragment = document.createDocumentFragment()

        for (const item of items) {
            fragment.append(this.#poster(item))
        }

        column.replaceChildren(fragment)
        column.style.minWidth = `${this.#columnWidth(items.length)}px`
        this.#loaded.add(column.dataset.museumYear)
    }

    /**
     * Clones the poster shell and fills it with an entry's image, color, and link.
     *
     * @param {{title: string, url: string, poster: string, backgroundColor: ?string, inLibrary: ?boolean}} item - the poster payload
     *
     * @returns {HTMLElement}
     */
    #poster(item) {
        const node = this.#templateNode.cloneNode(true)

        const image = node.querySelector('img')
        image.dataset.src = item.poster
        image.alt = `${item.title} Poster`

        for (const element of node.querySelectorAll('[data-museum-poster-bg]')) {
            element.style.backgroundColor = item.backgroundColor
            element.style.fill = item.backgroundColor
        }

        const link = node.querySelector('[data-museum-poster-link]')
        link.href = item.url
        link.setAttribute('aria-label', item.title)

        if (item.inLibrary) {
            node.dataset.museumInLibrary = 'true'

            if (this.#dimLibrary) {
                node.style.opacity = '0.25'
            }
        }

        return node
    }

    /**
     * Returns the given value when a matching year column exists.
     *
     * @param {string} value - the candidate year
     *
     * @returns {?string}
     */
    #validYear(value) {
        return this.#indexByYear.has(String(value)) ? String(value) : null
    }

    /**
     * Returns the viewport x of the hall's content edge, clear of the sidebar.
     *
     * @returns {number}
     */
    #contentLeft() {
        return this.#scroller.getBoundingClientRect().left + this.#scroller.clientLeft + this.#paddingLeft
    }

    /**
     * Returns the column index whose content edge leads at the given scroll offset.
     *
     * @param {number} scrollLeft - the scroll offset
     *
     * @returns {number}
     */
    #indexAt(scrollLeft) {
        const lefts = this.#lefts

        if (!lefts.length) {
            return 0
        }

        const target = scrollLeft + 1
        let low = 0
        let high = lefts.length - 1
        let index = 0

        while (low <= high) {
            const mid = (low + high) >> 1

            if (lefts[mid] <= target) {
                index = mid
                low = mid + 1
            } else {
                high = mid - 1
            }
        }

        return index
    }

    /**
     * Scrolls the hall so the given year sits at the leading edge.
     *
     * @param {string|number} year - the year to reveal
     * @param {boolean} smooth - whether to animate the scroll
     */
    #jumpToYear(year, smooth = true) {
        const index = this.#indexByYear.get(String(year))

        if (index === undefined) {
            return
        }

        this.#scroller.scrollTo({
            left: this.#lefts[index],
            behavior: smooth && !this.#reducedMotion ? 'smooth' : 'auto',
        })
    }

    /**
     * Jumps a number of years relative to the active year.
     *
     * @param {number} delta - the number of years to move
     */
    #stepYear(delta) {
        const index = this.#indexByYear.get(this.#activeYear) ?? 0
        const next = this.#columns[Math.min(this.#columns.length - 1, Math.max(0, index + delta))]

        if (next) {
            this.#jumpToYear(next.dataset.museumYear, true)
        }
    }

    /**
     * Binds the picker, recenter, timeline, scroll-sync, and keyboard handlers.
     */
    #bind() {
        this.#scroller.addEventListener('scroll', this.#onScroll, { passive: true })
        this.#scroller.addEventListener('keydown', this.#onKeydown)

        this.#select?.addEventListener('change', this.#onSelect)
        this.#recenter?.addEventListener('click', this.#onRecenter)
        this.#libraryToggle?.addEventListener('click', this.#onLibraryToggle)

        if (this.#track) {
            this.#track.addEventListener('pointerdown', this.#onTimelinePointerDown)
            this.#track.addEventListener('pointermove', this.#onTimelinePointerMove)
            this.#track.addEventListener('pointerup', this.#onTimelinePointerUp)
            this.#track.addEventListener('pointercancel', this.#onTimelinePointerUp)
            this.#track.addEventListener('pointerleave', this.#onTimelinePointerLeave)
        }

        this.#reducedMotionQuery.addEventListener('change', this.#onReducedMotionChange)
        window.addEventListener('resize', this.#onResize)
    }

    /**
     * Re-flows the columns after the viewport resizes.
     */
    #onResize = () => {
        clearTimeout(this.#resizeTimer)
        this.#resizeTimer = setTimeout(() => this.#layout(), 150)
    }

    /**
     * Syncs the picker, timeline, and hash while reclaiming far years.
     */
    #onScroll = () => {
        cancelAnimationFrame(this.#scrollFrame)
        this.#scrollFrame = requestAnimationFrame(() => {
            this.#syncActiveYear()
            this.#reclaimFarYears()

            if (this.#ready) {
                this.#showScrollingScale()
            }
        })
    }

    /**
     * Toggles dimming of entries already in the user's library.
     */
    #onLibraryToggle = () => {
        this.#dimLibrary = !this.#dimLibrary
        this.#reflectLibraryToggle()

        for (const poster of this.#scroller.querySelectorAll('[data-museum-in-library]')) {
            poster.style.opacity = this.#dimLibrary ? '0.25' : ''
        }

        localStorage.setItem('museum-dim-library', String(this.#dimLibrary))
    }

    /**
     * Reflects the dim-library state on the toggle button.
     */
    #reflectLibraryToggle() {
        if (!this.#libraryToggle) {
            return
        }

        this.#libraryToggle.setAttribute('aria-pressed', String(this.#dimLibrary))
        this.#libraryToggle.classList.toggle('bg-tint', this.#dimLibrary)
        this.#libraryToggle.classList.toggle('btn-text-tinted', this.#dimLibrary)
        this.#libraryToggle.classList.toggle('border-transparent', this.#dimLibrary)
        this.#libraryToggle.classList.toggle('bg-primary', !this.#dimLibrary)
        this.#libraryToggle.classList.toggle('text-tint', !this.#dimLibrary)
        this.#libraryToggle.classList.toggle('border-tint', !this.#dimLibrary)
        this.#libraryToggle.querySelector('[data-museum-library-icon-off]')?.classList.toggle('hidden', this.#dimLibrary)
        this.#libraryToggle.querySelector('[data-museum-library-icon-on]')?.classList.toggle('hidden', !this.#dimLibrary)
    }

    /**
     * Shows the current year's scale while scrolling, restoring the full scale on idle.
     */
    #showScrollingScale() {
        if (!this.#scale) {
            return
        }

        const count = Number(this.#countForYear(this.#activeYear)).toLocaleString()
        const text = this.#scale.dataset.yearLabel
            .replace(':count', count)
            .replace(':year', this.#activeYear)

        this.#setScale(text, Museum.#ScaleMode.Year)

        clearTimeout(this.#scaleTimer)
        this.#scaleTimer = setTimeout(() => this.#setScale(this.#scaleFull, Museum.#ScaleMode.Total), 500)
    }

    /**
     * Sets the scale text, cross-fading only when switching between total and year modes.
     *
     * @param {string} text - the text to show
     * @param {number} mode - the target scale mode
     */
    #setScale(text, mode) {
        if (mode === this.#scaleMode) {
            this.#scale.textContent = text

            return
        }

        this.#scaleMode = mode
        this.#scale.style.opacity = '0'

        clearTimeout(this.#scaleFadeTimer)
        this.#scaleFadeTimer = setTimeout(() => {
            this.#scale.textContent = text
            this.#scale.style.opacity = '1'
        }, 150)
    }

    /**
     * Reflects the leading visible year across the picker, timeline, and hash.
     */
    #syncActiveYear() {
        const index = this.#indexAt(this.#scroller.scrollLeft)
        const year = this.#columns[index]?.dataset.museumYear

        if (!year || year === this.#activeYear) {
            return
        }

        this.#activeYear = year

        if (this.#select && this.#select.value !== year) {
            this.#select.value = year
        }

        if (this.#ticks.length) {
            this.#ticks[this.#activeTickIndex]?.classList.remove('bg-tint')
            this.#ticks[this.#activeTickIndex]?.classList.add('bg-secondary')
            this.#ticks[index]?.classList.add('bg-tint')
            this.#ticks[index]?.classList.remove('bg-secondary')
            this.#activeTickIndex = index
        }

        const hash = `#${year}`

        if (location.hash !== hash) {
            history.replaceState(null, '', hash)
        }

        this.#updateMeta(year)
    }

    /**
     * Reflects the active year in the document title and app deeplink meta tags.
     *
     * @param {string} year - the active year
     */
    #updateMeta(year) {
        if (this.#root?.dataset.museumTitle) {
            document.title = this.#root.dataset.museumTitle.replace(':year', year)
        }

        const itunes = document.querySelector('meta[name="apple-itunes-app"]')

        if (itunes && this.#root?.dataset.museumItunes) {
            itunes.setAttribute('content', this.#root.dataset.museumItunes.replace(':year', year))
        }

        const deeplink = document.querySelector('meta[property="al:ios:url"]')

        if (deeplink && this.#root?.dataset.museumDeeplink) {
            deeplink.setAttribute('content', this.#root.dataset.museumDeeplink.replace(':year', year))
        }
    }

    /**
     * Drops the posters of years scrolled well beyond the viewport to cap memory.
     */
    #reclaimFarYears() {
        if (!this.#loaded.size) {
            return
        }

        const scrollLeft = this.#scroller.scrollLeft
        const margin = this.#viewportWidth * 2
        const min = scrollLeft - margin
        const max = scrollLeft + this.#viewportWidth + margin

        for (const year of [...this.#loaded]) {
            const index = this.#indexByYear.get(year)

            if (index === undefined) {
                continue
            }

            if (this.#rights[index] < min || this.#lefts[index] > max) {
                const column = this.#columns[index]
                column.replaceChildren()
                column.style.minWidth = `${this.#columnWidth(Number(column.dataset.museumCount) || 0)}px`
                this.#loaded.delete(year)
            }
        }
    }

    /**
     * Scrolls the hall to the year chosen in the picker.
     */
    #onSelect = () => {
        if (this.#select.value !== this.#activeYear) {
            this.#jumpToYear(this.#select.value, true)
        }
    }

    /**
     * Returns the hall to the current year.
     */
    #onRecenter = () => this.#jumpToYear(this.#scroller.dataset.museumCurrentYear, true)

    /**
     * Walks the hall with the arrow, page, and home/end keys.
     *
     * @param {KeyboardEvent} event - the key event
     */
    #onKeydown = (event) => {
        const step = (Museum.#itemWidth + Museum.#gap) * 3

        switch (event.key) {
            case 'ArrowRight':
                this.#scroller.scrollBy({ left: step, behavior: this.#reducedMotion ? 'auto' : 'smooth' })
                break
            case 'ArrowLeft':
                this.#scroller.scrollBy({ left: -step, behavior: this.#reducedMotion ? 'auto' : 'smooth' })
                break
            case 'PageDown':
                this.#stepYear(1)
                break
            case 'PageUp':
                this.#stepYear(-1)
                break
            case 'Home':
                this.#jumpToYear(this.#columns[0].dataset.museumYear, true)
                break
            case 'End':
                this.#jumpToYear(this.#columns[this.#columns.length - 1].dataset.museumYear, true)
                break
            default:
                return
        }

        event.preventDefault()
    }

    /**
     * Starts scrubbing the timeline.
     *
     * @param {PointerEvent} event - the pointer event
     */
    #onTimelinePointerDown = (event) => {
        const tick = event.target.closest('[data-museum-tick]')

        if (!tick) {
            return
        }

        this.#timelineScrubbing = true
        this.#track.setPointerCapture(event.pointerId)
        this.#scrubTo(tick, event.clientX)
    }

    /**
     * Scrubs or previews the year under the pointer.
     *
     * @param {PointerEvent} event - the pointer event
     */
    #onTimelinePointerMove = (event) => {
        if (this.#timelineScrubbing) {
            const tick = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-museum-tick]')

            if (tick) {
                this.#scrubTo(tick, event.clientX)
            }

            return
        }

        if (event.pointerType !== 'mouse') {
            return
        }

        const tick = event.target.closest('[data-museum-tick]')

        if (tick) {
            this.#showChip(tick, event.clientX)
        } else {
            this.#hideChip()
        }
    }

    /**
     * Ends scrubbing the timeline.
     *
     * @param {PointerEvent} event - the pointer event
     */
    #onTimelinePointerUp = (event) => {
        this.#timelineScrubbing = false

        if (this.#track.hasPointerCapture?.(event.pointerId)) {
            this.#track.releasePointerCapture(event.pointerId)
        }

        if (event.pointerType !== 'mouse') {
            this.#hideChip()
        }
    }

    /**
     * Hides the chip when the pointer leaves the timeline.
     */
    #onTimelinePointerLeave = () => {
        if (!this.#timelineScrubbing) {
            this.#hideChip()
        }
    }

    /**
     * Jumps to a tick's year and previews it.
     *
     * @param {HTMLElement} tick - the timeline tick
     * @param {number} clientX - the pointer's viewport x
     */
    #scrubTo(tick, clientX) {
        this.#jumpToYear(tick.dataset.museumYear, false)
        this.#showChip(tick, clientX)
    }

    /**
     * Shows the chip for a tick's year and count at the pointer.
     *
     * @param {HTMLElement} tick - the timeline tick
     * @param {number} clientX - the pointer's viewport x
     */
    #showChip(tick, clientX) {
        if (!this.#chip) {
            return
        }

        const year = tick.dataset.museumYear
        this.#chip.textContent = this.#chip.dataset.label
            .replace(':year', year)
            .replace(':count', this.#countForYear(year))
        this.#chip.classList.remove('hidden')

        const half = this.#chip.offsetWidth / 2
        const minCenter = this.#contentLeft() + half + 4
        const maxCenter = window.innerWidth - half - 4
        const center = Math.max(minCenter, Math.min(clientX, maxCenter))
        this.#chip.style.left = `${center - this.#track.getBoundingClientRect().left}px`
    }

    /**
     * Hides the chip.
     */
    #hideChip() {
        this.#chip?.classList.add('hidden')
    }

    /**
     * Returns the entry count for a year.
     *
     * @param {string} year - the year
     *
     * @returns {string}
     */
    #countForYear(year) {
        const index = this.#indexByYear.get(year)

        return index === undefined ? '0' : (this.#columns[index].dataset.museumCount ?? '0')
    }

    /**
     * Tracks the viewer's reduced-motion preference.
     *
     * @param {MediaQueryListEvent} event - the media query event
     */
    #onReducedMotionChange = (event) => {
        this.#reducedMotion = event.matches
    }
}
