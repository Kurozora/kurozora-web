import is from "./is";

/**
 * Extend an object with the given keys.
 *
 * @param {{}} target - target
 * @param {*} sources - sources
 *
 * @returns {{}|*}
 */
function extend(target = {}, ...sources) {
    if (!sources.length) {
        return target
    }

    const source = sources.shift()

    if (!is.object(source)) {
        return target
    }

    Object.keys(source).forEach(key => {
        if (is.object(source[key])) {
            if (!Object.keys(target).includes(key)) {
                Object.assign(target, {
                    [key]: {}
                })
            }

            extend(target[key], source[key])
        } else {
            Object.assign(target, {
                [key]: source[key]
            })
        }
    })
    return extend(target, ...sources)
}

export default {
    extend: extend
}
