// search.js

export default class SearchManager {
    #url = '/search/suggestions'
    #delay = 500
    #timers = new WeakMap()
    #controllers = new WeakMap()

    constructor() {
        document.addEventListener('input', (event) => this.#onInput(event))
    }

    #onInput(event) {
        const input = event.target.closest('[data-search-input]')

        if (!input) {
            return
        }

        clearTimeout(this.#timers.get(input))

        this.#timers.set(input, setTimeout(() => this.search(input), this.#delay))
    }

    #scope(element) {
        return element.closest('[data-search-scope]') ?? document
    }

    async search(input) {
        const scope = this.#scope(input)
        const results = scope.querySelector('[data-search-results]')
        const spinner = scope.querySelector('[data-search-spinner]')

        if (!results) {
            return
        }

        this.#controllers.get(input)?.abort()

        const controller = new AbortController()
        this.#controllers.set(input, controller)

        spinner?.classList.remove('hidden')

        try {
            const response = await fetch(this.#url + '?q=' + encodeURIComponent(input.value), {
                headers: { Accept: 'text/html' },
                signal: controller.signal,
            })

            if (!response.ok) {
                return
            }

            results.innerHTML = await response.text()
        } catch (error) {
            if (error.name !== 'AbortError') {
                throw error
            }
        } finally {
            if (!controller.signal.aborted) {
                spinner?.classList.add('hidden')
            }
        }
    }

    reset(element) {
        const scope = element?.closest('[data-search-scope]') ?? document
        const input = scope.querySelector('[data-search-input]')

        if (input) {
            input.value = ''
            this.search(input)
        }
    }
}
