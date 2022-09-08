import is from './is'
import object from './object'

/**
 * Set the given attributes on the specified HTML element.
 *
 * @param {HTMLElement} element - element
 * @param {{}} attributes - attributes
 */
function setAttributes(element, attributes) {
    if (!is.element(element) || is.empty(attributes)) {
        return
    }

    // Assume null and undefined attributes should be left out,
    // Setting them would otherwise convert them to "null" and "undefined"
    Object.entries(attributes)
        .filter(([, value]) => !is.nullOrUndefined(value))
        .forEach(([key, value]) => element.setAttribute(key, value))
}

/**
 * Create a new element with the specified type.
 *
 * @param {string} type - type
 * @param {{}} attributes - attributes
 * @param {string|null} text - text
 *
 * @returns {*}
 */
function create(type, attributes, text = null) {
    // Create a new <element>
    const element = document.createElement(type)

    // Set all passed attributes
    if (is.object(attributes)) {
        setAttributes(element, attributes)
    }

    // Add text node
    if (is.string(text)) {
        element.innerText = text
    }

    // Return built element
    return element
}

/**
 * Remove element(s).
 *
 * @param {Element|Node|NodeList} element - element
 */
function remove(element) {
    if (is.nodeList(element) || is.array(element)) {
        Array.from(element).forEach(remove)
        return
    }

    if (!is.element(element) || !is.element(element.parentNode)) {
        return
    }

    element.parentNode.removeChild(element)
}

/**
 * Get an attribute object from a string selector
 *
 * ```
 * For example:
 *     '.test' to { class: 'test' }
 *     '#test' to { id: 'test' }
 *     '[data-test="test"]' to { 'data-test': 'test' }
 *```
 *
 * @param {string} selector - selector
 * @param {{}} existingAttributes - existing attributes
 *
 * @returns {{}|{}|*}
 */
function getAttributesFromSelector(selector, existingAttributes = {}) {
    if (!is.string(selector) || is.empty(selector)) {
        return {}
    }

    const attributes = {}
    const existing = object.extend({}, existingAttributes)

    selector.split(',').forEach((s) => {
        // Remove whitespace
        const selector = s.trim()
        const className = selector.replace('.', '')
        const stripped = selector.replace(/[[\]]/g, '')
        // Get the parts and value
        const parts = stripped.split('=')
        const [key] = parts
        const value = parts.length > 1 ? parts[1].replace(/["']/g, '') : ''
        // Get the first character
        const start = selector.charAt(0)

        switch (start) {
            case '.':
                // Add to existing classname
                if (is.string(existing.class)) {
                    attributes.class = `${existing.class} ${className}`
                } else {
                    attributes.class = className
                }
                break
            case '#':
                // ID selector
                attributes.id = selector.replace('#', '')
                break
            case '[':
                // Attribute selector
                attributes[key] = value
                break
            default:
                break
        }
    })

    return object.extend(existing, attributes)
}

/**
 * Whether the given element matches the selector.
 *
 * @param {Element} element - element
 * @param {string} selector - selector
 *
 * @returns {boolean}
 */
function matches(element, selector) {
    const { prototype } = Element

    function match() {
        return Array.from(document.querySelectorAll(selector)).includes(this)
    }

    const method =
        prototype.matches ||
        prototype.webkitMatchesSelector ||
        prototype.mozMatchesSelector ||
        prototype.msMatchesSelector ||
        match

    return method.call(element, selector)
}

/**
 * Toggle hidden element.
 *
 * @param {Element} element - element
 * @param {boolean} hidden - hidden
 */
function toggleHidden(element, hidden) {
    if (!is.element(element)) {
        return
    }

    let hide = hidden

    if (!is.boolean(hide)) {
        hide = !element.hidden
    }

    element.hidden = hide
}

/**
 * Set focus and tab focus class.
 *
 * @param {Element} element - element
 * @param {boolean} tabFocus - tab focus
 */
function setFocus(element = null, tabFocus = false) {
    if (!is.element(element)) {
        return;
    }

    // Set regular focus
    element.focus({ preventScroll: true });

    // If we want to mimic keyboard focus via tab
    if (tabFocus) {
        toggleClass(element, this.config.classNames.tabFocus);
    }
}

// Mirror Element.classList.toggle, with IE compatibility for "force" argument
function toggleClass(element, className, force) {
    if (is.nodeList(element)) {
        return Array.from(element).map((e) => toggleClass(e, className, force));
    }

    if (is.element(element)) {
        let method = 'toggle';
        if (typeof force !== 'undefined') {
            method = force ? 'add' : 'remove';
        }

        element.classList[method](className);
        return element.classList.contains(className);
    }

    return false;
}

export default {
    create: create,
    remove: remove,
    setAttributes: setAttributes,
    getAttributesFromSelector: getAttributesFromSelector,
    matches: matches,
    toggleHidden: toggleHidden,
}
