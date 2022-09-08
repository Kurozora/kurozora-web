import is from '../utilities/is'
import object from '../utilities/object'

export default class Storage {
    /**
     * @constructor
     *
     * @param {Plyr} player - player
     */
    constructor(player) {
        this.enabled = player.config.storage.enabled
        this.key = player.config.storage.key
    }

    /**
     * Get an item.
     *
     * @param key - key
     *
     * @returns {*|null}
     */
    get(key) {
        if (!Storage.#supported || !this.enabled) {
            return null
        }

        const store = window.localStorage.getItem(this.key)

        if (is.empty(store)) {
            return null
        }

        const json = JSON.parse(store)
        return is.string(key) && key.length ? json[key] : json
    }

    /**
     * Set an value.
     *
     * @param {*|null} value - value
     */
    set(value) {
        // Bail if we don't have localStorage support, or if it's disabled
        if (!Storage.#supported || !this.enabled) {
            return
        }

        // Can only store object
        if (!is.object(value)) {
            return
        }

        // Get current storage
        let storage = this.get(null)

        // Default to empty object
        if (is.empty(storage)) {
            storage = {}
        }

        // Update the working copy of the values
        object.extend(storage, value)

        // Update storage
        try {
            window.localStorage.setItem(this.key, JSON.stringify(storage))
        } catch (_) {
            // Do nothing
        }
    }

    /**
     * Check for actual support (see if we can use it)
     *
     * @returns {boolean}
     */
    static get #supported() {
        try {
            if (!('localStorage' in window)) {
                return false
            }

            const test = '___test'

            // Try to use it (it might be disabled, e.g. user is in private mode)
            window.localStorage.setItem(test, test)
            window.localStorage.removeItem(test)
            return true
        } catch (_) {
            return false
        }
    }
}
