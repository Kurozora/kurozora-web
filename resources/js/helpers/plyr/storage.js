export default class Storage {
    /**
     * The Plyr instance.
     *
     * @param {Plyr} player - player
     */
    player

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
     * Get an object.
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

        if (this.#is.empty(store)) {
            return null
        }

        const json = JSON.parse(store)
        return this.#is.string(key) && key.length ? json[key] : json
    }

    /**
     * Set an object.
     *
     * @param {*|null} object - object
     */
    set(object) {
        // Bail if we don't have localStorage support, or if it's disabled
        if (!Storage.#supported || !this.enabled) {
            return
        }

        // Can only store object
        if (!this.#is.object(object)) {
            return
        }

        // Get current storage
        let storage = this.get(null) // Default to empty object

        if (this.#is.empty(storage)) {
            storage = {}
        }

        // Update the working copy of the values
        this.#extend(storage, object) // Update storage

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

            const test = '___test' // Try to use it (it might be disabled, e.g. user is in private mode)

            window.localStorage.setItem(test, test)
            window.localStorage.removeItem(test)
            return true
        } catch (_) {
            return false
        }
    }
    /**
     * Get the constructor of a value.
     *
     * @param e
     * @returns {Function|null}
     */
    static #getConstructor = function (e) {
        return null != e ? e.constructor : null
    }
    /**
     * Check if value is an instance of given object.
     *
     * @param e
     * @param t
     * @returns {boolean}
     */
    static #instanceOf = function (e, t) {
        return !!(e && t && e instanceof t)
    }
    /**
     * Check if value is null or undefined.
     * @param e
     * @returns {boolean}
     */
    static #isNullOrUndefined = function (e) {
        return null == e
    }
    /**
     * Check if value is an object.
     *
     * @param e
     * @returns {boolean}
     */
    static #isObject = function (e) {
        return Storage.#getConstructor(e) === Object
    }
    /**
     * Check if value is a number.
     *
     * @param e
     * @returns {boolean}
     */
    static #isNumber = function (e) {
        return Storage.#getConstructor(e) === Number && !Number.isNaN(e)
    }
    /**
     * Check if value is a string.
     * @param e
     * @returns {boolean}
     */
    static #isString = function (e) {
        return Storage.#getConstructor(e) === String
    }
    /**
     * Check if the value is a boolean.
     *
     * @param e
     * @returns {boolean}
     */
    static #isBoolean = function (e) {
        return Storage.#getConstructor(e) === Boolean
    }
    /**
     * Check if value is a function.
     *
     * @param e
     * @returns {boolean}
     */
    static #isFunction = function (e) {
        return Storage.#getConstructor(e) === Function
    }
    /**
     * Check if value is array.
     *
     * @param e
     * @returns {arg is any[]}
     */
    static #isArray = function (e) {
        return Array.isArray(e)
    }
    /**
     * Check if value is a NodeList.
     *
     * @param e
     * @returns {*}
     */
    static #isNodeList = function (e) {
        return Storage.#instanceOf(e, NodeList)
    }
    /**
     * Check if value is element.
     *
     * @param e
     * @returns {*}
     */
    static #isElement = function (e) {
        return Storage.#instanceOf(e, Element)
    }
    /**
     * Check if value is event.
     *
     * @param e
     * @returns {*}
     */
    static #isEvent = function (e) {
        return Storage.#instanceOf(e, Event)
    }
    /**
     * Check if value is empty.
     *
     * @param e
     * @returns {boolean|arg is any[]|*}
     */
    static #isEmpty = function (e) {
        return Storage.#isNullOrUndefined(e) || (Storage.#isString(e) || Storage.#isArray(e) || Storage.#isNodeList(e)) && !e.length || Storage.#isObject(e) && !Object.keys(e).length
    }

    /**
     * @type {{nullOrUndefined: *, number: *, boolean: *, string: *, array: *, function: *, event: *, nodeList: *, object: *, element: *, empty: *}}
     */
    #is = {
        nullOrUndefined: Storage.#isNullOrUndefined,
        object: Storage.#isObject,
        number: Storage.#isNumber,
        string: Storage.#isString,
        boolean: Storage.#isBoolean,
        function: Storage.#isFunction,
        array: Storage.#isArray,
        nodeList: Storage.#isNodeList,
        element: Storage.#isElement,
        event: Storage.#isEvent,
        empty: Storage.#isEmpty
    }

    /**
     * Extend an object with the given keys.
     *
     * @param {{}} target - target
     * @param {*} sources - sources
     *
     * @returns {{}|*}
     */
    #extend(target = {}, ...sources) {
        if (!sources.length) {
            return target
        }

        const source = sources.shift()

        if (!this.#is.object(source)) {
            return target
        }

        Object.keys(source).forEach(key => {
            if (this.#is.object(source[key])) {
                if (!Object.keys(target).includes(key)) {
                    Object.assign(target, {
                        [key]: {}
                    })
                }

                this.#extend(target[key], source[key])
            } else {
                Object.assign(target, {
                    [key]: source[key]
                })
            }
        })
        return this.#extend(target, ...sources)
    }
}
