// history.js

export default class HistoryManager {
    #stack = []
    #storageKey = 'customHistoryStack'
    #isPopState = false

    constructor() {
        this.#stack = this.#loadStack()
    }

    #loadStack() {
        try {
            return JSON.parse(sessionStorage.getItem(this.#storageKey)) || []
        } catch {
            return []
        }
    }

    #saveStack() {
        sessionStorage.setItem(this.#storageKey, JSON.stringify(this.#stack))
    }

    #getCurrentUrl() {
        return window.location.pathname + window.location.search
    }

    pushPage(title, url = this.#getCurrentUrl()) {
        if (this.#isPopState) {
            this.#isPopState = false
            return // Don't push during back/forward navigation
        }

        const last = this.#stack[this.#stack.length - 1]
        if (!last || last.url !== url) {
            this.#stack.push({ title, url })
            this.#saveStack()
        }
    }

    popPage() {
        this.#isPopState = true
        this.#stack.pop()
        this.#saveStack()
    }

    get stack() {
        return [...this.#stack]
    }

    get length() {
        return this.#stack.length
    }

    get previousPage() {
        return this.#stack.length >= 2 ? this.#stack[this.#stack.length - 2] : null
    }

    back(fallbackUrl = null) {
        window.history.back()

        if (!!fallbackUrl) {
            setTimeout(() => {
                window.location.href = fallbackUrl;
            }, 500);
        }
    }
}
