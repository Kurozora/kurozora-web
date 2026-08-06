export default class Position {
    /**
     * Whether the document is laid out right-to-left.
     *
     * @returns {boolean}
     */
    static isRTL() {
        return (document.documentElement.getAttribute('dir') || getComputedStyle(document.documentElement).direction) === 'rtl'
    }

    /**
     * Resolves which way a floating panel should open to stay within the viewport.
     *
     * @param {DOMRect} anchor - the triggering element's rectangle
     * @param {{width: number, height: number}} panel - the floating panel's size
     * @param {{rtl?: boolean}} [options]
     *
     * @returns {{x: 'left'|'right', y: 'up'|'down'}}
     */
    static resolveOpenDirection(anchor, panel, { rtl = false } = {}) {
        const viewportWidth = window.innerWidth
        const viewportHeight = window.innerHeight

        const y = (anchor.top + anchor.bottom) / 2 > viewportHeight / 2 ? 'up' : 'down'

        const spaceRight = viewportWidth - anchor.right
        const spaceLeft = anchor.left
        let x = rtl ? 'left' : 'right'

        if (x === 'right' && spaceRight < panel.width && spaceLeft > spaceRight) {
            x = 'left'
        } else if (x === 'left' && spaceLeft < panel.width && spaceRight > spaceLeft) {
            x = 'right'
        }

        return { x, y }
    }

    /**
     * Anchors a dropdown panel beside its trigger, kept within the viewport.
     *
     * @param {HTMLElement} panel - the dropdown panel
     * @param {HTMLElement} trigger - the element the panel is anchored to
     */
    static placeDropdown(panel, trigger) {
        if (!panel || !trigger) {
            return
        }

        panel.style.top = panel.style.bottom = panel.style.left = panel.style.right = panel.style.marginTop = panel.style.marginBottom = ''

        const anchor = trigger.getBoundingClientRect()
        const width = panel.getBoundingClientRect().width
        const margin = 8

        const openUp = (anchor.top + anchor.bottom) / 2 > window.innerHeight / 2
        if (openUp) {
            panel.style.top = 'auto'
            panel.style.bottom = '100%'
            panel.style.marginTop = '0'
            panel.style.marginBottom = '0.5rem'
        } else {
            panel.style.bottom = 'auto'
            panel.style.top = '100%'
            panel.style.marginBottom = '0'
            panel.style.marginTop = '0.5rem'
        }

        const fitsExtendingRight = anchor.left + width <= window.innerWidth - margin
        const fitsExtendingLeft = anchor.right - width >= margin
        let alignLeft = !Position.isRTL()

        if (alignLeft && !fitsExtendingRight && fitsExtendingLeft) {
            alignLeft = false
        } else if (!alignLeft && !fitsExtendingLeft && fitsExtendingRight) {
            alignLeft = true
        }

        if (alignLeft) {
            panel.style.left = '0'
            panel.style.right = 'auto'
        } else {
            panel.style.right = '0'
            panel.style.left = 'auto'
        }

        panel.style.transformOrigin = `${openUp ? 'bottom' : 'top'} ${alignLeft ? 'left' : 'right'}`
    }
}
