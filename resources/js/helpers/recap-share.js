export default class RecapShare {
    /**
     * The width of a share card.
     *
     * @type {number}
     */
    static width = 1080

    /**
     * The height of a share card.
     *
     * @type {number}
     */
    static height = 1920

    /**
     * The glow field of each card layout.
     *
     * @type {Object<string, {url: string, splits: number[]}>}
     */
    static glows = {
        genres: { url: '/images/static/recap/share-glow-genres.png', splits: [] },
        comparison: { url: '/images/static/recap/share-glow-comparison.png', splits: [1096] },
        summary: { url: '/images/static/recap/share-glow-summary.png', splits: [] },
    }

    /**
     * How far a pixel read back from a canvas may drift before the canvas counts as blocked.
     *
     * @type {number}
     */
    static canvasTolerance = 8

    /**
     * The lowest point the summary's list may reach.
     *
     * @type {number}
     */
    static summaryBottom = 1800

    /**
     * The share of the shorter artwork that stacked summary artworks overlap by.
     *
     * @type {number}
     */
    static summaryArtworkOverlap = 0.3

    /**
     * The paths of the Kurozora logo in its 44-point box.
     *
     * @type {string[]}
     */
    static logoPaths = [
        'M4.26356589,25.6953027 C4.25865611,30.1220323 6.11477477,34.3424354 9.36940612,37.3048315 C6.04486859,33.8984502 5.89762765,28.4720384 9.03244888,24.886623 C12.1672701,21.3012076 17.5136377,20.7811787 21.2674782,23.6965497 C25.0213187,26.6119208 25.9007262,31.9671163 23.2799805,35.9517818 C20.6592348,39.9364472 15.4332991,41.1898517 11.3205565,38.8201669 C13.8256676,40.4665942 16.7506698,41.3407107 19.7388593,41.3359375 C28.2881704,41.3359375 35.2170543,34.3335972 35.2170543,25.6953027 C35.2170543,17.0570082 28.2881704,10.0546875 19.7388593,10.0546875 C11.1924595,10.0546875 4.26356589,17.0575967 4.26356589,25.6953027 Z',
        'M1.02325581,22.9450189 C1.02325581,24.4181799 1.17548672,25.8873356 1.47831164,27.3285372 C1.40322831,26.6431228 1.36549315,25.9540748 1.36527611,25.2644961 C1.36527611,14.9125812 9.69261577,6.5201841 19.9642832,6.5201841 C30.2359506,6.5201841 38.5632903,14.9143428 38.5632903,25.2644961 C38.5632903,35.2634982 30.7941364,43.4333429 21.0084053,43.9788605 C21.3090565,43.9917791 21.610873,44 21.9150202,44 C33.4516369,44 42.8062016,34.5735271 42.8062016,22.9450189 C42.8062016,11.3165107 33.4528022,1.890625 21.9150202,1.890625 C10.3784035,1.890625 1.02325581,11.3170979 1.02325581,22.9450189 Z',
        'M0.937984496,12.890625 C3.00055919,6.51588805 8.78568735,1.96729358 15.0930233,0 C7.6776734,0 0.937984496,4.79560892 0.937984496,12.890625 Z',
    ]

    /**
     * The worker that paints the glows.
     *
     * @type {?Worker}
     */
    #worker = null

    /**
     * The glow requests awaiting the worker keyed by ID.
     *
     * @type {Map<number, {resolve: Function, reject: Function}>}
     */
    #glowRequests = new Map()

    /**
     * The ID of the next glow request.
     *
     * @type {number}
     */
    #nextGlowRequestID = 0

    /**
     * The rendered cards keyed by the button that shares them.
     *
     * @type {Map<string, {signature: string, file: File}>}
     */
    #files = new Map()

    /**
     * The renders in progress keyed by what they render.
     *
     * @type {Map<string, Promise<File>>}
     */
    #renders = new Map()

    /**
     * The card waiting on canvas access.
     *
     * @type {?string}
     */
    #pendingKey = null

    /**
     * Whether the share sheet is open.
     *
     * @type {boolean}
     */
    #isSharing = false

    /**
     * The colors and font of the current theme.
     *
     * @type {?Object}
     */
    #theme = null

    constructor() {
        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-recap-share]')

            if (button) {
                event.preventDefault()
                this.#share(button)
            } else if (event.target.closest('[data-recap-share-retry]')) {
                event.preventDefault()
                this.#retry()
            }
        })
    }

    /**
     * Returns the page's share cards.
     *
     * @returns {?Object}
     */
    #payload() {
        const source = document.querySelector('[data-recap-share-cards]')

        return source ? JSON.parse(source.dataset.recapShareCards) : null
    }

    /**
     * Shares the card of the given button.
     *
     * @param {HTMLElement} button
     */
    async #share(button) {
        const payload = this.#payload()
        const key = button.dataset.recapShare

        if (!payload?.cards[key] || this.#isSharing || button.getAttribute('aria-busy') === 'true') {
            return
        }

        // Reading the canvas during the tap lets a browser that blocks it ask for access.
        if (!RecapShare.#canReadCanvas()) {
            this.#pendingKey = key
            this.#setCanvasPromptShown(true)
            return
        }

        button.setAttribute('aria-busy', 'true')

        let file
        try {
            file = await this.#file(key, payload)
        } catch (error) {
            console.error(error)
            return
        } finally {
            button.removeAttribute('aria-busy')
        }

        // The share promise settles only once the sheet closes.
        this.#isSharing = true
        try {
            await this.#deliver(file)
        } catch (error) {
            if (error.name !== 'AbortError' && error.name !== 'NotAllowedError') {
                console.error(error)
            }
        } finally {
            this.#isSharing = false
        }
    }

    /**
     * Shares the card that waited on canvas access once the browser allows it.
     */
    #retry() {
        if (!RecapShare.#canReadCanvas()) {
            return
        }

        this.#setCanvasPromptShown(false)

        const button = this.#pendingKey ? document.querySelector(`[data-recap-share="${CSS.escape(this.#pendingKey)}"]`) : null
        if (button) {
            this.#share(button)
        }
    }

    /**
     * Shows or hides the prompt to allow canvas access.
     *
     * @param {boolean} isShown
     */
    #setCanvasPromptShown(isShown) {
        window.dispatchEvent(new CustomEvent(isShown ? 'open-modal' : 'close-modal', { detail: { id: 'recap-canvas-access' } }))
    }

    /**
     * Returns whether the page can read back what it draws on a canvas.
     *
     * @returns {boolean}
     */
    static #canReadCanvas() {
        const canvas = document.createElement('canvas')
        canvas.width = 4
        canvas.height = 4
        const context = canvas.getContext('2d', { willReadFrequently: true })
        const expected = [40, 160, 220]
        context.fillStyle = `rgb(${expected.join(', ')})`
        context.fillRect(0, 0, 4, 4)

        try {
            const data = context.getImageData(0, 0, 4, 4).data
            for (let index = 0; index < data.length; index += 4) {
                if (data[index + 3] !== 255 || expected.some((value, channel) => Math.abs(data[index + channel] - value) > RecapShare.canvasTolerance)) {
                    return false
                }
            }
            return true
        } catch {
            return false
        }
    }

    /**
     * Returns the rendered image of a card, rendering it when needed.
     *
     * @param {string} key
     * @param {Object} payload
     * @returns {Promise<File>}
     */
    #file(key, payload) {
        const card = payload.cards[key]
        const theme = this.#readTheme()
        const signature = JSON.stringify([card, payload.brand, theme])
        const cached = this.#files.get(key)

        if (cached?.signature === signature) {
            return Promise.resolve(cached.file)
        }

        if (!this.#renders.has(signature)) {
            this.#renders.set(signature, this.render(card, payload.brand, theme)
                .then((blob) => {
                    const file = new File([blob], `${payload.fileName}-${key}.png`, { type: 'image/png' })
                    this.#files.set(key, { signature, file })
                    return file
                })
                .finally(() => this.#renders.delete(signature)))
        }

        return this.#renders.get(signature)
    }

    /**
     * Opens the share sheet with the given file, or saves it where there is none.
     *
     * @param {File} file
     */
    async #deliver(file) {
        if (navigator.canShare?.({ files: [file] })) {
            await navigator.share({ files: [file] })
        } else {
            this.#download(file)
        }
    }

    /**
     * Saves the given file through a temporary link.
     *
     * @param {File} file
     */
    #download(file) {
        const url = URL.createObjectURL(file)
        const link = document.createElement('a')
        link.href = url
        link.download = file.name
        link.click()
        setTimeout(() => URL.revokeObjectURL(url), 1000)
    }

    /**
     * Renders the given card to a PNG.
     *
     * @param {Object} card
     * @param {Object} brand
     * @param {Object} theme
     * @returns {Promise<Blob>}
     */
    async render(card, brand, theme = this.#readTheme()) {
        this.#theme = theme
        await document.fonts.ready
        await Promise.all([400, 500, 600].map((weight) => document.fonts.load(`${weight} 48px ${this.#theme.fontFamily}`)))

        const canvas = document.createElement('canvas')
        canvas.width = RecapShare.width
        canvas.height = RecapShare.height
        const context = canvas.getContext('2d')

        await this.#drawGlow(context, card.layout, brand.colors)
        this.#drawHeader(context, brand)

        switch (card.layout) {
            case 'genres':
                this.#drawGenres(context, card)
                break
            case 'comparison':
                await this.#drawComparison(context, card)
                break
            default:
                await this.#drawSummary(context, card)
        }

        return new Promise((resolve, reject) => canvas.toBlob((blob) => (blob ? resolve(blob) : reject(new Error('The card could not be encoded.'))), 'image/png'))
    }

    /**
     * Returns the colors and font of the current theme.
     *
     * @returns {Object}
     */
    #readTheme() {
        const style = getComputedStyle(document.body)
        const color = (name, fallback) => style.getPropertyValue(name).trim() || fallback

        return {
            background: color('--bg-primary-color', '#353A50'),
            secondaryBackground: color('--bg-secondary-color', '#454F63'),
            primaryText: color('--primary-text-color', '#EEEEEE'),
            secondaryText: color('--secondary-text-color', '#AFAFAF'),
            fontFamily: style.fontFamily,
        }
    }

    // MARK: - Glow

    /**
     * Paints the glow of the given layout in the recap colors.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {string} layout
     * @param {string[]} colors
     */
    async #drawGlow(context, layout, colors) {
        const pixels = await this.#paintGlow(layout, colors)
        context.putImageData(new ImageData(pixels, RecapShare.width, RecapShare.height), 0, 0)
    }

    /**
     * Returns the glow of the given layout painted by the worker.
     *
     * @param {string} layout
     * @param {string[]} colors
     * @returns {Promise<Uint8ClampedArray>}
     */
    #paintGlow(layout, colors) {
        this.#worker ??= this.#makeWorker()
        const id = this.#nextGlowRequestID++

        return new Promise((resolve, reject) => {
            this.#glowRequests.set(id, { resolve, reject })
            this.#worker.postMessage({
                id,
                url: new URL(RecapShare.glows[layout].url, window.location.href).href,
                splits: RecapShare.glows[layout].splits,
                width: RecapShare.width,
                height: RecapShare.height,
                first: colors[0],
                second: colors[1],
                background: this.#theme.background,
            })
        })
    }

    /**
     * Returns the worker that paints the glows.
     *
     * @returns {Worker}
     */
    #makeWorker() {
        const worker = new Worker(new URL('./recap-share-glow.worker.js', import.meta.url), { type: 'module' })

        worker.addEventListener('message', (event) => {
            const request = this.#glowRequests.get(event.data.id)

            if (request) {
                this.#glowRequests.delete(event.data.id)
                event.data.error ? request.reject(new Error(event.data.error)) : request.resolve(event.data.pixels)
            }
        })

        return worker
    }

    // MARK: - Text

    /**
     * Sets the context's font.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {number} weight
     * @param {number} size
     */
    #setFont(context, weight, size) {
        context.font = `${weight} ${size}px ${this.#theme.fontFamily}`

        if ('letterSpacing' in context) {
            context.letterSpacing = `${-0.02 * size}px`
        }
    }

    /**
     * Returns the baseline of a line box with the given top and height in the context's font.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {number} top
     * @param {number} lineHeight
     * @returns {number}
     */
    #baseline(context, top, lineHeight) {
        const metrics = context.measureText('Hg')
        const ascent = metrics.fontBoundingBoxAscent ?? metrics.actualBoundingBoxAscent
        const descent = metrics.fontBoundingBoxDescent ?? metrics.actualBoundingBoxDescent

        return top + (lineHeight - (ascent + descent)) / 2 + ascent
    }

    /**
     * Returns the text broken into lines that fit the width.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {string} text
     * @param {number} maxWidth
     * @param {number} maxLines
     * @returns {string[]}
     */
    #wrap(context, text, maxWidth, maxLines) {
        const fits = (line) => context.measureText(line).width <= maxWidth
        const lines = []
        let line = ''

        for (const word of text.split(/\s+/).filter(Boolean)) {
            const candidate = line ? `${line} ${word}` : word

            if (fits(candidate)) {
                line = candidate
                continue
            }

            if (line) {
                lines.push(line)
            }

            // A word wider than the line breaks where it runs out of room.
            line = word
            while (!fits(line) && line.length > 1) {
                let end = line.length - 1
                while (end > 1 && !fits(line.slice(0, end))) end--
                lines.push(line.slice(0, end))
                line = line.slice(end)
            }
        }

        if (line) {
            lines.push(line)
        }

        if (lines.length <= maxLines) {
            return lines
        }

        const kept = lines.slice(0, maxLines)
        let last = `${kept[maxLines - 1]}…`
        while (!fits(last) && last.length > 1) {
            last = `${last.slice(0, -2).trimEnd()}…`
        }
        kept[maxLines - 1] = last

        return kept
    }

    /**
     * Draws lines of text in consecutive line boxes from the given top.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {string[]} lines
     * @param {number} x
     * @param {number} top
     * @param {number} lineHeight
     */
    #drawLines(context, lines, x, top, lineHeight) {
        lines.forEach((line, index) => context.fillText(line, x, this.#baseline(context, top + index * lineHeight, lineHeight)))
    }

    // MARK: - Header

    /**
     * Draws the Re:CAP wordmark and the Kurozora brand along the top.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {Object} brand
     */
    #drawHeader(context, brand) {
        const center = 206
        context.fillStyle = this.#theme.primaryText
        context.textAlign = 'left'

        this.#setFont(context, 600, 55)
        context.fillText(brand.wordmark, 54, center + context.measureText('H').actualBoundingBoxAscent / 2)

        this.#setFont(context, 600, 46)
        const nameWidth = context.measureText(brand.name).width
        const nameX = RecapShare.width - 54 - nameWidth
        context.fillText(brand.name, nameX, center + context.measureText('H').actualBoundingBoxAscent / 2)

        const logoSize = 42
        context.save()
        context.translate(nameX - 10 - logoSize, center - logoSize / 2)
        context.scale(logoSize / 44, logoSize / 44)
        context.translate(0.5, 0)
        RecapShare.logoPaths.forEach((path) => context.fill(new Path2D(path)))
        context.restore()
    }

    /**
     * Draws a card's title and its secondary subtitle beneath.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {string} title
     * @param {string} subtitle
     */
    #drawTitleBlock(context, title, subtitle) {
        const lineHeight = 56 * 1.12
        this.#setFont(context, 600, 56)
        context.fillStyle = this.#theme.primaryText
        context.fillText(this.#wrap(context, title, 972, 1)[0], 54, this.#baseline(context, 345, lineHeight))
        context.fillStyle = this.#theme.secondaryText
        context.fillText(this.#wrap(context, subtitle, 972, 1)[0], 54, this.#baseline(context, 345 + lineHeight, lineHeight))
    }

    // MARK: - Cards

    /**
     * Draws the ranked genres or themes with the minutes spent on each.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {Object} card
     */
    #drawGenres(context, card) {
        this.#drawTitleBlock(context, card.title, card.subtitle)

        const lineHeight = 56 * 1.215
        const pitch = lineHeight * 2 + 81
        this.#setFont(context, 600, 56)

        card.items.forEach((item, index) => {
            const top = 591 + index * pitch
            context.fillStyle = this.#theme.primaryText
            context.fillText(String(index + 1), 52, this.#baseline(context, top, lineHeight))
            context.fillText(this.#wrap(context, item.name, 880, 1)[0], 148, this.#baseline(context, top, lineHeight))

            if (item.detail) {
                context.fillStyle = this.#theme.secondaryText
                context.fillText(this.#wrap(context, item.detail, 880, 1)[0], 148, this.#baseline(context, top + lineHeight, lineHeight))
            }
        })
    }

    /**
     * Draws this year's top title above last year's.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {Object} card
     */
    async #drawComparison(context, card) {
        this.#drawTitleBlock(context, card.title, card.subtitle)

        const artworks = await Promise.all(card.entries.map((entry) => this.#loadImage(entry.artwork.url)))
        const layouts = [
            { imageTop: 580, imageX: 704, textX: 56 },
            { imageTop: 1200, imageX: 56, textX: 424 },
        ]

        card.entries.forEach((entry, index) => {
            const { imageTop, imageX, textX } = layouts[index]
            const size = RecapShare.#artworkSize(entry.artwork.shape, 320, 480)
            const artworkTop = imageTop + (480 - size.height) / 2
            this.#drawArtwork(context, artworks[index], entry.artwork, imageX + (320 - size.width) / 2, artworkTop, size.width, size.height, false)

            // The year's top lines up with the artwork's top.
            this.#setFont(context, 600, 44)
            context.fillStyle = this.#theme.secondaryText
            context.fillText(entry.year, textX, artworkTop + context.measureText(entry.year).actualBoundingBoxAscent)

            // Long names step down in size until they fit three lines.
            let nameSize = 80
            this.#setFont(context, 500, nameSize)
            while (nameSize > 56 && this.#wrap(context, entry.name, 600, Infinity).length > 3) {
                nameSize -= 4
                this.#setFont(context, 500, nameSize)
            }
            context.fillStyle = this.#theme.primaryText
            this.#drawLines(context, this.#wrap(context, entry.name, 600, 3), textX, imageTop + 89, nameSize * 1.19)

            if (entry.detail) {
                this.#setFont(context, 600, 42)
                context.fillStyle = this.#theme.secondaryText
                context.fillText(this.#wrap(context, entry.detail, 600, 1)[0], textX, this.#baseline(context, imageTop + 408, 42 * 1.2))
            }
        })
    }

    /**
     * Draws the period's total and top titles beside a stack of each section's top artwork.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {Object} card
     */
    async #drawSummary(context, card) {
        context.textAlign = 'center'
        this.#setFont(context, 600, 83)
        context.fillStyle = this.#theme.primaryText
        context.fillText(card.period, RecapShare.width / 2, this.#baseline(context, 286, 83 * 1.2))

        if (card.total) {
            this.#setFont(context, 600, 41)
            context.fillStyle = this.#theme.secondaryText
            context.fillText(card.total, RecapShare.width / 2, this.#baseline(context, 286 + 83 * 1.2 + 12, 41 * 1.2))
        }
        context.textAlign = 'left'

        const layout = this.#layoutSummary(context, card.sections)
        this.#drawSummaryList(context, card.sections, layout)

        const artworks = await Promise.all(card.sections.map((section) => this.#loadImage(section.artwork.url)))
        let top = 580
        let previousHeight = null
        card.sections.slice(0, 4).forEach((section, index) => {
            const size = RecapShare.#artworkSize(section.artwork.shape, 300, 428)

            // Each artwork overlaps the one above by the same share of the shorter of the two.
            if (previousHeight !== null) {
                top += previousHeight - RecapShare.summaryArtworkOverlap * Math.min(previousHeight, size.height)
            }
            previousHeight = size.height

            this.#drawArtwork(context, artworks[index], section.artwork, index % 2 === 0 ? 700 : 760, top, size.width, size.height, true)
        })
    }

    /**
     * Returns how many titles each summary section lists and how large the list is drawn.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {Object[]} sections
     * @returns {{counts: number[], scale: number, spacing: number}}
     */
    #layoutSummary(context, sections) {
        const limit = { 1: 10, 2: 5, 3: 4 }[sections.length] ?? 3
        const layout = { counts: sections.map((section) => Math.min(3, section.items.length)), scale: 1, spacing: 1 }
        const fits = () => this.#summaryListBottom(context, sections, layout) <= RecapShare.summaryBottom

        // Fewer sections list more titles, one round at a time while the list still fits.
        let grew = true
        while (grew) {
            grew = false
            sections.forEach((section, index) => {
                if (layout.counts[index] >= Math.min(limit, section.items.length)) {
                    return
                }

                layout.counts[index]++
                if (fits()) {
                    grew = true
                } else {
                    layout.counts[index]--
                }
            })
        }

        // Long titles tighten the gaps before shrinking the list.
        while (!fits() && layout.spacing > 0.5) {
            layout.spacing -= 0.05
        }
        while (!fits() && layout.scale > 0.72) {
            layout.scale -= 0.02
        }

        return layout
    }

    /**
     * Returns the summary list's rows at the given layout.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {Object[]} sections
     * @param {Object} layout
     * @returns {{bottom: number, rows: Object[]}}
     */
    #summaryRows(context, sections, layout) {
        const { scale, spacing, counts } = layout
        const indent = 67 * scale
        const itemLineHeight = 34 * scale * 1.2
        const rows = []
        let top = 580

        sections.forEach((section, sectionIndex) => {
            if (sectionIndex > 0) {
                top += 118 * scale * spacing
            }

            rows.push({ kind: 'heading', text: section.title, x: 63 + indent, top })
            top += 48 * scale * 1.2 + 35 * scale

            this.#setFont(context, 500, 34 * scale)
            section.items.slice(0, counts[sectionIndex]).forEach((item, itemIndex) => {
                if (itemIndex > 0) {
                    top += 22 * scale
                }

                const lines = this.#wrap(context, item, 600 - indent, 3)
                rows.push({ kind: 'item', rank: String(itemIndex + 1), lines, top })
                top += lines.length * itemLineHeight
            })
        })

        return { bottom: top, rows }
    }

    /**
     * Returns where the summary list ends at the given layout.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {Object[]} sections
     * @param {Object} layout
     * @returns {number}
     */
    #summaryListBottom(context, sections, layout) {
        return this.#summaryRows(context, sections, layout).bottom
    }

    /**
     * Draws the summary list at the given layout.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {Object[]} sections
     * @param {Object} layout
     */
    #drawSummaryList(context, sections, layout) {
        const { scale } = layout
        const itemLineHeight = 34 * scale * 1.2

        for (const row of this.#summaryRows(context, sections, layout).rows) {
            if (row.kind === 'heading') {
                this.#setFont(context, 600, 48 * scale)
                context.fillStyle = this.#theme.primaryText
                context.fillText(row.text, row.x, this.#baseline(context, row.top, 48 * scale * 1.2))
                continue
            }

            this.#setFont(context, 500, 34 * scale)
            context.fillStyle = this.#theme.secondaryText
            context.fillText(row.rank, 63, this.#baseline(context, row.top, itemLineHeight))
            context.fillStyle = this.#theme.primaryText
            this.#drawLines(context, row.lines, 63 + 67 * scale, row.top, itemLineHeight)
        }
    }

    // MARK: - Artwork

    /**
     * Returns the size of an artwork shape at the given width and poster height.
     *
     * @param {string} shape
     * @param {number} width
     * @param {number} posterHeight
     * @returns {{width: number, height: number}}
     */
    static #artworkSize(shape, width, posterHeight) {
        switch (shape) {
            case 'square':
            case 'circle':
                return { width, height: width }
            case 'book':
                return { width, height: Math.min(width * (160 / 112), posterHeight) }
            default:
                return { width, height: posterHeight }
        }
    }

    /**
     * Returns the outline of an artwork shape.
     *
     * @param {string} shape
     * @param {number} x
     * @param {number} y
     * @param {number} width
     * @param {number} height
     * @returns {Path2D}
     */
    static #artworkPath(shape, x, y, width, height) {
        const path = new Path2D()

        if (shape === 'circle') {
            path.arc(x + width / 2, y + height / 2, width / 2, 0, Math.PI * 2)
            return path
        }

        const radius = { square: 0.2133, book: 4 / 112, poster: 0.07 }[shape] * width
        path.roundRect(x, y, width, height, radius)
        return path
    }

    /**
     * Loads an image that can be drawn into an exportable canvas.
     *
     * @param {?string} url
     * @returns {Promise<?HTMLImageElement>}
     */
    #loadImage(url) {
        if (!url) {
            return Promise.resolve(null)
        }

        return new Promise((resolve) => {
            const image = new Image()
            image.crossOrigin = 'anonymous'
            image.onload = () => resolve(image)
            image.onerror = () => resolve(null)
            image.src = url
        })
    }

    /**
     * Draws an artwork in its shape.
     *
     * @param {CanvasRenderingContext2D} context
     * @param {?HTMLImageElement} image
     * @param {Object} artwork
     * @param {number} x
     * @param {number} y
     * @param {number} width
     * @param {number} height
     * @param {boolean} isStacked
     */
    #drawArtwork(context, image, artwork, x, y, width, height, isStacked) {
        const path = RecapShare.#artworkPath(artwork.shape, x, y, width, height)
        const fill = artwork.color || this.#theme.secondaryBackground

        context.save()
        if (isStacked) {
            context.shadowColor = 'rgba(0, 0, 0, 0.45)'
            context.shadowBlur = 60
            context.shadowOffsetY = 24
        }
        context.fillStyle = fill
        context.fill(path)
        context.restore()

        if (artwork.shape !== 'book') {
            context.save()
            context.lineWidth = 4
            context.strokeStyle = isStacked ? 'rgba(0, 0, 0, 0.2)' : 'rgba(255, 255, 255, 0.14)'
            context.stroke(path)
            context.restore()
        }

        context.save()
        context.clip(path)
        if (image) {
            const coverScale = Math.max(width / image.naturalWidth, height / image.naturalHeight)
            const sourceWidth = width / coverScale
            const sourceHeight = height / coverScale
            context.drawImage(image, (image.naturalWidth - sourceWidth) / 2, (image.naturalHeight - sourceHeight) / 2, sourceWidth, sourceHeight, x, y, width, height)
        }

        if (artwork.shape === 'book') {
            // The spine and sheen of the site's book covers.
            const spine = context.createLinearGradient(x + width, y, x + width * 0.0036, y)
            spine.addColorStop(0, 'rgba(223, 218, 218, 0.318)')
            spine.addColorStop(0.952, 'rgba(227, 227, 227, 0.12)')
            spine.addColorStop(0.956, 'rgb(21, 21, 20)')
            spine.addColorStop(1, 'rgb(255, 255, 255)')
            context.globalAlpha = 0.4
            context.globalCompositeOperation = 'lighten'
            context.fillStyle = spine
            context.fillRect(x, y, width, height)
        }
        context.restore()
    }
}
