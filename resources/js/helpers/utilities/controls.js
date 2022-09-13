import is from './is'
import element from './element'

// Bind keyboard shortcuts for a menu item
// We have to bind to keyup otherwise Firefox triggers a click when a keydown event handler shifts focus
// https://bugzilla.mozilla.org/show_bug.cgi?id=1220143
function bindMenuItemShortcuts(menuItem, type) {
    // Navigate through menus via arrow keys and space
    menuItem.addEventListener('keydown keyup', (event) => {
        // We only care about space and ⬆️ ⬇️️ ➡️
        if (!['Space', 'ArrowUp', 'ArrowDown', 'ArrowRight'].includes(event.key)) {
            return
        }

        // Prevent play / seek
        event.preventDefault()
        event.stopPropagation()

        // We're just here to prevent the keydown bubbling
        if (event.type === 'keydown') {
            return
        }

        const isRadioButton = element.matches(menuItem, '[role="menuitemradio"]')

        // Show the respective menu
        if (!isRadioButton && ['Space', 'ArrowRight'].includes(event.key)) {
            showMenuPanel(type, true)
        } else {
            let target

            if (event.key !== 'Space') {
                if (event.key === 'ArrowDown' || (isRadioButton && event.key === 'ArrowRight')) {
                    target = menuItem.nextElementSibling

                    if (!is.element(target)) {
                        target = menuItem.parentNode.firstElementChild
                    }
                } else {
                    target = menuItem.previousElementSibling

                    if (!is.element(target)) {
                        target = menuItem.parentNode.lastElementChild
                    }
                }

                element.setFocus(target, true)
            }
        }
    })

    // Enter will fire a `click` event, but we still need to manage focus
    // So we bind to keyup which fires after and set focus here
    menuItem.addEventListener('keyup', (event) => {
        if (event.key !== 'Return') return

        controls.focusFirstMenuItem.call(this, null, true)
    })
}

/**
 * Show a panel in the menu.
 *
 * @param {string} settingsMenu - settingsMenu
 * @param {boolean} tabFocus - tab focused
 */
function showMenuPanel(settingsMenu = '', tabFocus = false) {
    const target = document.querySelector(settingsMenu)

    // Nothing to show, bail
    if (!is.element(target)) {
        return
    }

    // Hide all other panels
    const container = target.parentNode
    const current = Array.from(container.children).find((node) => !node.hidden)

    // Set the current width as a base
    container.style.width = `${current.scrollWidth}px`
    container.style.height = `${current.scrollHeight}px`

    // Get potential sizes
    const size = getMenuSize(target)

    // Restore auto height/width
    const restore = (event) => {
        // We're only bothered about height and width on the container
        if (event.target !== container || !['width', 'height'].includes(event.propertyName)) {
            return
        }

        // Revert to auto
        container.style.width = ''
        container.style.height = ''

        // Only listen once
        container.removeEventListener(transitionEndEvent(), restore)
    }

    // Listen for the transition finishing and restore auto height/width
    container.addEventListener(transitionEndEvent(), restore)

    // Set dimensions to target
    container.style.width = `${size.width}px`
    container.style.height = `${size.height}px`

    // Set attributes on current tab
    element.toggleHidden(current, true)

    // Set attributes on target
    element.toggleHidden(target, false)

    // Focus the first item
    // controls.focusFirstMenuItem.call(this, target, tabFocus)
}

/**
 * Get event of transition end.
 *
 * @returns {string|boolean}
 */
function transitionEndEvent() {
    const element = document.createElement('span')
    const events = {
        WebkitTransition: 'webkitTransitionEnd',
        MozTransition: 'transitionend',
        OTransition: 'oTransitionEnd otransitionend',
        transition: 'transitionend',
    }
    const type = Object.keys(events).find((event) => element.style[event] !== undefined)

    return is.string(type) ? events[type] : false
}

/**
 * Get the natural size of a menu panel
 *
 * @param {Element} tab
 * @returns {{width: (*|number), height: (*|number)}}
 */
function getMenuSize(tab) {
    const clone = tab.cloneNode(true)
    clone.style.position = 'absolute'
    clone.style.opacity = 0
    clone.removeAttribute('hidden')

    // Append to parent, so we get the "real" size
    tab.parentNode.appendChild(clone)

    // Get the sizes before we remove
    const width = clone.scrollWidth
    const height = clone.scrollHeight

    // Remove from the DOM
    element.remove(clone)

    return {
        width,
        height,
    }
}

export default  {
    showMenuPanel
}
