class Is {
    /**
     * List of available YouTube urls.
     *
     * @type {string[]}
     */
    static #youtubeUrls = [
        'youtube.com',
        'youtu.be',
        'youtube-nocookie.com',
    ]

    /**
     * Get the constructor of a value.
     *
     * @param e
     * @returns {Function|null}
     */
    static getConstructor = function (e) {
        return null != e ? e.constructor : null
    }
    /**
     * Check if value is an instance of given object.
     *
     * @param e
     * @param t
     * @returns {boolean}
     */
    static instanceOf = function (e, t) {
        return !!(e && t && e instanceof t)
    }
    /**
     * Check if value is null or undefined.
     * @param e
     * @returns {boolean}
     */
    static isNullOrUndefined = function (e) {
        return null == e
    }
    /**
     * Check if value is an object.
     *
     * @param e
     * @returns {boolean}
     */
    static isObject = function (e) {
        return Is.getConstructor(e) === Object
    }
    /**
     * Check if value is a number.
     *
     * @param e
     * @returns {boolean}
     */
    static isNumber = function (e) {
        return Is.getConstructor(e) === Number && !Number.isNaN(e)
    }
    /**
     * Check if value is a string.
     * @param e
     * @returns {boolean}
     */
    static isString = function (e) {
        return Is.getConstructor(e) === String
    }
    /**
     * Check if the value is a boolean.
     *
     * @param e
     * @returns {boolean}
     */
    static isBoolean = function (e) {
        return Is.getConstructor(e) === Boolean
    }
    /**
     * Check if value is a function.
     *
     * @param e
     * @returns {boolean}
     */
    static isFunction = function (e) {
        return Is.getConstructor(e) === Function
    }
    /**
     * Check if value is array.
     *
     * @param e
     * @returns {arg is any[]}
     */
    static isArray = function (e) {
        return Array.isArray(e)
    }
    /**
     * Check if value is a NodeList.
     *
     * @param e
     * @returns {*}
     */
    static isNodeList = function (e) {
        return Is.instanceOf(e, NodeList)
    }
    /**
     * Check if value is element.
     *
     * @param e
     * @returns {*}
     */
    static isElement = function (e) {
        return Is.instanceOf(e, Element)
    }
    /**
     * Check if value is event.
     *
     * @param e
     * @returns {*}
     */
    static isEvent = function (e) {
        return Is.instanceOf(e, Event)
    }
    /**
     * Check if value is empty.
     *
     * @param e
     * @returns {boolean|arg is any[]|*}
     */
    static isEmpty = function (e) {
        return Is.isNullOrUndefined(e) || (Is.isString(e) || Is.isArray(e) || Is.isNodeList(e)) && !e.length || Is.isObject(e) && !Object.keys(e).length
    }

    /**
     * Whether the video is Mp4.
     *
     * @param e
     * @returns {boolean}
     */
    static isMp4 = function (e) {
        return e.includes('.mp4')
    }

    /**
     * Whether the video is Hls.
     *
     * @param e
     * @returns {boolean}
     */
    static isHls = function (e) {
        return e.includes('.m3u8')
    }

    /**
     * Whether the video is YouTube.
     *
     * @param e
     * @returns {boolean}
     */
    static isYouTube = function (e) {
        return Is.#youtubeUrls.some(youtubeUrl => e.includes(youtubeUrl))
    }

    /**
     * Whether the video is iframe.
     *
     * @param e
     * @returns {boolean}
     */
    static isIframe = function (e) {
        return e.querySelector('iframe') != null
    }
}

export default {
    nullOrUndefined: Is.isNullOrUndefined,
    object: Is.isObject,
    number: Is.isNumber,
    string: Is.isString,
    boolean: Is.isBoolean,
    function: Is.isFunction,
    array: Is.isArray,
    nodeList: Is.isNodeList,
    element: Is.isElement,
    event: Is.isEvent,
    empty: Is.isEmpty,
    mp4: Is.isMp4,
    hls: Is.isHls,
    youTube: Is.isYouTube,
    iframe: Is.isIframe
}
