export default class Marquee {
    /**
     * Scroll speed in pixels per second.
     *
     * @type {number}
     */
    static scrollSpeed = 30

    /**
     * Milliseconds to wait before starting or restarting the scroll.
     *
     * @type {number}
     */
    static restartDelay = 3000

    /**
     * Gap between the end of the text and the start of the duplicate.
     *
     * @type {number}
     */
    static loopSpacing = 24

    /**
     * Fade width on the leading edge.
     *
     * @type {number}
     */
    static leadingFade = 8

    /**
     * Fade width on the trailing edge.
     *
     * @type {number}
     */
    static trailingFade = 24

    /**
     * Wraps the given element so its text can scroll.
     *
     * @param {HTMLElement} element - the clipping container
     */
    constructor(element) {
        this.element = element
        this.element.style.display = 'block'
        this.element.style.overflow = 'hidden'
        this.element.style.whiteSpace = 'nowrap'

        this.track = document.createElement('span')
        this.track.style.display = 'inline-block'
        this.track.style.willChange = 'transform'

        this.primary = document.createElement('span')
        this.clone = document.createElement('span')
        this.clone.setAttribute('aria-hidden', 'true')
        this.clone.style.display = 'none'
        this.clone.style.paddingLeft = `${Marquee.loopSpacing}px`

        this.track.append(this.primary, this.clone)
        this.element.appendChild(this.track)

        this.text = ''
        this.textWidth = 0
        this.overflowing = false
        this.animation = null
        this.timer = null

        this.resizeObserver = new ResizeObserver(() => this.measure())
        this.resizeObserver.observe(this.element)
    }

    /**
     * Sets the displayed text, remeasuring only when it changes.
     *
     * @param {string} text - the text to display
     */
    setText(text) {
        const value = text ?? ''
        if (value === this.text) {
            return
        }

        this.text = value
        this.primary.textContent = value
        this.clone.textContent = value
        this.measure()
    }

    /**
     * Measures the text and starts or stops scrolling accordingly.
     */
    measure() {
        this.stop()

        const containerWidth = this.element.clientWidth
        this.textWidth = Math.ceil(this.primary.getBoundingClientRect().width)
        this.overflowing = containerWidth > 0 && this.textWidth > containerWidth + 1

        if (this.overflowing) {
            const mask = `linear-gradient(to right, transparent 0, #000 ${Marquee.leadingFade}px, #000 calc(100% - ${Marquee.trailingFade}px), transparent 100%)`
            this.clone.style.display = 'inline-block'
            this.element.style.webkitMaskImage = mask
            this.element.style.maskImage = mask
            this.scheduleAnimation()
        } else {
            this.clone.style.display = 'none'
            this.element.style.webkitMaskImage = ''
            this.element.style.maskImage = ''
            this.track.style.transform = 'translateX(0)'
        }
    }

    /**
     * Schedules the next scroll after the restart delay.
     */
    scheduleAnimation() {
        this.timer = setTimeout(() => this.run(), Marquee.restartDelay)
    }

    /**
     * Runs one scroll round: linear then decelerating, ending at the start.
     */
    run() {
        const distance = this.textWidth + Marquee.loopSpacing
        const duration = (distance / (0.9 * Marquee.scrollSpeed)) * 1000

        this.animation = this.track.animate(
            [
                { transform: 'translateX(0)', easing: 'linear', offset: 0 },
                { transform: `translateX(${-distance * 8 / 9}px)`, easing: 'cubic-bezier(0.35, 0.7, 0.65, 1)', offset: 0.8 },
                { transform: `translateX(${-distance}px)`, offset: 1 }
            ],
            { duration }
        )

        this.animation.onfinish = () => {
            this.track.style.transform = 'translateX(0)'
            this.scheduleAnimation()
        }
    }

    /**
     * Cancels any in-flight scroll and pending restart.
     */
    stop() {
        clearTimeout(this.timer)
        this.timer = null
        this.animation?.cancel()
        this.animation = null
    }

    /**
     * Tears down the observer and animation.
     */
    destroy() {
        this.stop()
        this.resizeObserver.disconnect()
    }
}
