import Position from './position'

export default class Submenu {
    /**
     * How long the pointer must linger on another trigger before it takes over.
     *
     * @type {number}
     */
    #lingerDelay = 260

    /**
     * The open hover flyout, or null.
     *
     * @type {{container: HTMLElement, trigger: HTMLElement, flyout: HTMLElement}|null}
     */
    #active = null

    /**
     * The open touch overlay, or null.
     *
     *  @type {{flyout: HTMLElement, chevron: ?HTMLElement, surface: HTMLElement, backdrop: HTMLElement, placeholder: Comment, shiftY: number, collapsedHeight: number}|null}
     */
    #overlay = null

    /**
     * The pointer position captured while over the active trigger.
     *
     * @type {?{x: number, y: number}}
     */
    #anchor = null

    /**
     * The latest pointer position.
     *
     * @type {{x: number, y: number}}
     */
    #lastPoint = { x: 0, y: 0 }

    /**
     * The previous pointer position, used to read movement direction.
     *
     * @type {{x: number, y: number}}
     */
    #prevPoint = { x: 0, y: 0 }

    /**
     * A deferred takeover by another trigger.
     *
     * @type {{trigger: HTMLElement, container: HTMLElement, timer: number}|null}
     */
    #pending = null

    /**
     * The in-flight overlay close animations.
     *
     * @type {Animation[]}
     */
    #closingAnimations = []

    /**
     * Finalizes a pending close, or null when none is pending.
     *
     * @type {(() => void)|null}
     */
    #finalizeClose = null

    constructor() {
        document.addEventListener('pointermove', (event) => {
            if (this.#isTouch()) {
                return
            }

            this.#prevPoint = this.#lastPoint
            this.#lastPoint = { x: event.clientX, y: event.clientY }

            if (!this.#active) {
                return
            }

            const overTrigger = this.#isInside(this.#lastPoint, this.#rectOf(this.#active.trigger))
            const overFlyout = this.#isInside(this.#lastPoint, this.#rectOf(this.#active.flyout))

            if (overTrigger) {
                this.#anchor = { ...this.#lastPoint }
            }
            if (overFlyout) {
                this.#clearPending()
            }

            this.#setHighlight(this.#active.trigger, overTrigger ? 'accent' : 'open')
        }, { passive: true })

        document.addEventListener('pointerover', (event) => {
            if (this.#isTouch()) {
                return
            }

            const trigger = event.target.closest('[data-submenu-trigger]')
            if (trigger) {
                const container = trigger.closest('[data-submenu]')
                if (this.#active?.container === container) {
                    this.#clearPending()
                } else if (this.#active && this.#isAiming()) {
                    this.#defer(container, trigger)
                } else {
                    this.#open(container)
                }
                return
            }

            if (this.#active && event.target.closest('[data-submenu-flyout]') === this.#active.flyout) {
                this.#clearPending()
                return
            }

            const item = event.target.closest('[data-menu-item]')
            if (this.#active && item && !item.closest('[data-submenu-flyout]') && !this.#isAiming()) {
                this.#close()
            }
        })

        document.addEventListener('pointerout', (event) => {
            if (this.#pending && event.target.closest('[data-submenu-trigger]') === this.#pending.trigger && !this.#pending.trigger.contains(event.relatedTarget)) {
                this.#clearPending()
            }
        })

        document.addEventListener('focusin', (event) => {
            if (this.#isTouch()) {
                return
            }

            const container = event.target.closest('[data-submenu]')
            if (container) {
                this.#open(container)
            }
        })

        document.addEventListener('click', (event) => {
            if (!this.#isTouch()) {
                return
            }

            if (!this.#overlay) {
                const trigger = event.target.closest('[data-submenu-trigger]')
                if (trigger) {
                    event.preventDefault()
                    this.#openOverlay(trigger.closest('[data-submenu]'))
                }
                return
            }

            if (event.target.closest('[data-submenu-header]') || event.target.closest('[data-submenu-backdrop]')) {
                event.preventDefault()
                event.stopPropagation()
                this.#closeOverlay()
            }
        })

        document.addEventListener('livewire:navigated', () => {
            this.#clearPending()
            this.#active = null
            this.#anchor = null
            this.#overlay = null
        })

        document.addEventListener('pointerdown', (event) => {
            if (!this.#isTouch() && this.#active && !event.target.closest('[data-submenu]')) {
                this.#close()
            }
        })

        document.addEventListener('click', (event) => {
            const item = event.target.closest('[data-menu-item]')
            if (!item) {
                return
            }

            this.#close()
            this.#closeOverlay()
            item.dispatchEvent(new CustomEvent('close', { bubbles: true }))
        })

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.#close()
                this.#closeOverlay()
            }
        })
    }

    /**
     * Whether the device lacks hover, i.e. is touch-driven.
     *
     * @returns {boolean}
     */
    #isTouch() {
        return window.matchMedia('(hover: none)').matches
    }

    #rectOf(element) {
        return element.getBoundingClientRect()
    }

    #isInside(point, rect) {
        return point.x >= rect.left && point.x <= rect.right && point.y >= rect.top && point.y <= rect.bottom
    }

    #slope(from, to) {
        return (to.y - from.y) / (to.x - from.x)
    }

    /**
     * Whether the pointer is moving toward the open flyout's safe cone.
     *
     * @returns {boolean}
     */
    #isAiming() {
        if (!this.#active || !this.#anchor) {
            return false
        }

        const rect = this.#rectOf(this.#active.flyout)
        const openedRight = this.#anchor.x <= rect.left
        const edge = openedRight ? rect.left : rect.right
        const upper = { x: edge, y: rect.top }
        const lower = { x: edge, y: rect.bottom }

        const aimingRight = this.#slope(this.#lastPoint, upper) < this.#slope(this.#prevPoint, upper) && this.#slope(this.#lastPoint, lower) > this.#slope(this.#prevPoint, lower)
        const aimingLeft = this.#slope(this.#lastPoint, upper) > this.#slope(this.#prevPoint, upper) && this.#slope(this.#lastPoint, lower) < this.#slope(this.#prevPoint, lower)

        return openedRight ? aimingRight : aimingLeft
    }

    /**
     * Highlights a trigger as accented while hovered, open while its submenu is open, or none.
     *
     * @param {HTMLElement} trigger
     * @param {'accent'|'open'|'none'} mode
     */
    #setHighlight(trigger, mode) {
        trigger.classList.toggle('bg-tint', mode === 'accent')
        trigger.classList.toggle('btn-text-tinted', mode === 'accent')
        trigger.classList.toggle('bg-tertiary', mode === 'open')
    }

    #clearPending() {
        if (this.#pending) {
            clearTimeout(this.#pending.timer)
            this.#pending = null
        }
    }

    /**
     * Places the flyout beside its trigger, choosing the side and vertical anchor that keep it within the viewport.
     *
     * @param {HTMLElement} trigger
     * @param {HTMLElement} flyout
     */
    #place(trigger, flyout) {
        flyout.style.left = flyout.style.right = flyout.style.top = flyout.style.bottom = flyout.style.marginLeft = flyout.style.marginRight = ''

        const surface = trigger.closest('[data-dropdown-surface]')
        if (surface) {
            flyout.style.maxWidth = 'none'
            flyout.style.width = `${surface.getBoundingClientRect().width}px`
        }

        const direction = Position.resolveOpenDirection(this.#rectOf(trigger), this.#rectOf(flyout), { rtl: Position.isRTL() })

        if (direction.x === 'right') {
            flyout.style.left = '100%'
            flyout.style.marginLeft = '-0.25rem'
        } else {
            flyout.style.right = '100%'
            flyout.style.marginRight = '-0.25rem'
        }

        flyout.style[direction.y === 'up' ? 'bottom' : 'top'] = '0'
    }

    /**
     * Opens the container's submenu as a hover flyout.
     *
     * @param {HTMLElement} container
     */
    #open(container) {
        if (this.#active?.container === container) {
            return
        }

        this.#close()

        const trigger = container.querySelector('[data-submenu-trigger]')
        const flyout = container.querySelector('[data-submenu-flyout]')
        if (!flyout) {
            return
        }

        this.#place(trigger, flyout)
        flyout.classList.remove('invisible', 'opacity-0')
        this.#active = { container, trigger, flyout }
        this.#anchor = { ...this.#lastPoint }
        this.#setHighlight(trigger, this.#isInside(this.#lastPoint, this.#rectOf(trigger)) ? 'accent' : 'open')
    }

    /**
     * Closes the open hover flyout.
     */
    #close() {
        this.#clearPending()

        if (!this.#active) {
            return
        }

        this.#active.flyout.classList.add('invisible', 'opacity-0')
        this.#setHighlight(this.#active.trigger, 'none')
        this.#active = null
        this.#anchor = null
    }

    /**
     * Defers a takeover until the pointer lingers on the trigger.
     *
     * @param {HTMLElement} container
     * @param {HTMLElement} trigger
     */
    #defer(container, trigger) {
        if (this.#pending?.trigger === trigger) {
            return
        }

        this.#clearPending()
        this.#pending = {
            trigger,
            container,
            timer: setTimeout(() => {
                this.#pending = null
                if (this.#isInside(this.#lastPoint, this.#rectOf(trigger))) {
                    this.#open(container)
                }
            }, this.#lingerDelay),
        }
    }

    /**
     * Opens a submenu as an in-place overlay, collapsing and dimming the parent behind it.
     *
     * @param {HTMLElement} container
     */
    #openOverlay(container) {
        this.#finishClose()
        this.#closeOverlay()

        const trigger = container.querySelector('[data-submenu-trigger]')
        const flyout = container.querySelector('[data-submenu-flyout]')
        const header = flyout?.querySelector('[data-submenu-header]')
        const chevron = flyout?.querySelector('[data-submenu-chevron]')
        const surface = container.closest('[data-dropdown-surface]')
        if (!flyout || !surface?.parentElement) {
            return
        }

        const panel = surface.parentElement

        const placeholder = document.createComment('submenu-flyout')
        flyout.parentNode.insertBefore(placeholder, flyout)

        const backdrop = document.createElement('div')
        backdrop.setAttribute('data-submenu-backdrop', '')
        backdrop.className = 'fixed inset-0 z-0'
        panel.appendChild(backdrop)
        panel.appendChild(flyout)

        flyout.classList.add('is-overlay')
        flyout.classList.remove('invisible', 'opacity-0')
        flyout.style.position = 'absolute'
        flyout.style.zIndex = '10'
        flyout.style.maxWidth = 'none'

        const surfaceRect = surface.getBoundingClientRect()
        const panelRect = panel.getBoundingClientRect()
        const triggerRect = trigger.getBoundingClientRect()

        flyout.style.left = `${surfaceRect.left - panelRect.left}px`
        flyout.style.width = `${surfaceRect.width}px`

        const fullHeight = flyout.getBoundingClientRect().height
        const collapsedHeight = header ? header.getBoundingClientRect().height + 8 : fullHeight

        const margin = 8
        let finalTop = triggerRect.top
        if (finalTop + fullHeight > window.innerHeight - margin) {
            finalTop = window.innerHeight - margin - fullHeight
        }
        if (finalTop < margin) {
            finalTop = margin
        }
        flyout.style.top = `${finalTop - panelRect.top}px`

        const shiftY = triggerRect.top - finalTop

        surface.classList.add('is-dimmed')
        flyout.style.overflow = 'hidden'

        const expand = flyout.animate(
            [
                { maxHeight: `${collapsedHeight}px`, transform: `translateY(${shiftY}px)` },
                { maxHeight: `${fullHeight}px`, transform: 'translateY(0px)' },
            ],
            { duration: 240, easing: 'cubic-bezier(0.2, 0, 0, 1)' },
        )
        expand.onfinish = () => {
            flyout.style.overflow = ''
            flyout.style.maxHeight = ''
        }

        chevron?.animate(
            [{ transform: 'rotate(0deg)' }, { transform: 'rotate(90deg)' }],
            { duration: 240, easing: 'cubic-bezier(0.2, 0, 0, 1)' },
        )

        this.#overlay = { flyout, chevron, surface, backdrop, placeholder, shiftY, collapsedHeight }
    }

    /**
     * Collapses the open touch overlay back into its trigger.
     */
    #closeOverlay() {
        if (!this.#overlay) {
            return
        }

        const { flyout, chevron, surface, backdrop, placeholder, shiftY, collapsedHeight } = this.#overlay
        this.#overlay = null

        surface.classList.remove('is-dimmed')

        const fullHeight = flyout.getBoundingClientRect().height
        flyout.style.overflow = 'hidden'

        let done = false
        const finalize = () => {
            if (done) {
                return
            }
            done = true
            this.#finalizeClose = null
            this.#closingAnimations = []

            flyout.classList.add('invisible', 'opacity-0')
            flyout.classList.remove('is-overlay')
            flyout.style.position = flyout.style.zIndex = flyout.style.maxWidth = flyout.style.left = flyout.style.width = flyout.style.top = flyout.style.overflow = flyout.style.maxHeight = flyout.style.transform = ''

            if (placeholder.parentNode) {
                placeholder.parentNode.insertBefore(flyout, placeholder)
                placeholder.remove()
            }
            backdrop.remove()
        }
        this.#finalizeClose = finalize

        const collapse = flyout.animate(
            [
                { maxHeight: `${fullHeight}px`, transform: 'translateY(0px)' },
                { maxHeight: `${collapsedHeight}px`, transform: `translateY(${shiftY}px)` },
            ],
            { duration: 200, easing: 'cubic-bezier(0.4, 0, 1, 1)' },
        )
        collapse.onfinish = finalize
        collapse.oncancel = finalize
        this.#closingAnimations = [collapse]

        if (chevron) {
            this.#closingAnimations.push(chevron.animate(
                [{ transform: 'rotate(90deg)' }, { transform: 'rotate(0deg)' }],
                { duration: 200, easing: 'cubic-bezier(0.4, 0, 1, 1)' },
            ))
        }
    }

    /**
     * Finishes a pending close immediately so a fast reopening starts clean.
     */
    #finishClose() {
        if (!this.#finalizeClose) {
            return
        }

        this.#closingAnimations.forEach((animation) => animation.cancel())
        this.#finalizeClose()
    }
}
