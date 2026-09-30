// pagination.js

import ProgressBar from './progress-bar'

export default class PaginationManager {
    #linkSelector = 'nav[aria-label="Pagination Navigation"] a[href]'
    #containerSelector = '[data-paginated]'
    #listenerSelector = '[data-paginated][data-paginated-refresh-on]'
    #loadingSelector = '[data-loading]'
    #livewireMethods = ['gotoPage', 'nextPage', 'previousPage', 'setPage']
    #progressBar = new ProgressBar()
    #requests = new WeakMap()
    #subscribedEvents = new Set()

    constructor() {
        document.addEventListener('click', (event) => this.#onClick(event))
        document.addEventListener('livewire:init', () => this.#observeLivewire())
        document.addEventListener('livewire:navigated', () => this.#subscribe())
        window.addEventListener('popstate', () => this.#onPopState())
    }

    #observeLivewire() {
        window.Livewire.hook('commit', ({ commit, succeed, fail }) => {
            if (!commit.calls?.some((call) => this.#livewireMethods.includes(call.method))) {
                return
            }

            this.#progressBar.start()
            succeed(() => this.#progressBar.finish())
            fail(() => this.#progressBar.finish())
        })

        this.#subscribe()
    }

    #subscribe() {
        for (const container of document.querySelectorAll(this.#listenerSelector)) {
            for (const eventName of container.dataset.paginatedRefreshOn.split(' ')) {
                if (this.#subscribedEvents.has(eventName)) {
                    continue
                }

                this.#subscribedEvents.add(eventName)
                window.Livewire.on(eventName, () => this.#refreshSubscribers(eventName))
            }
        }
    }

    #refreshSubscribers(eventName) {
        for (const container of document.querySelectorAll(this.#listenerSelector)) {
            if (container.dataset.paginatedRefreshOn.split(' ').includes(eventName)) {
                this.load(container, container.dataset.paginatedUrl ?? window.location.href)
            }
        }
    }

    #onClick(event) {
        const link = event.target.closest(this.#linkSelector)

        if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return
        }

        const container = link.closest(this.#containerSelector)

        event.preventDefault()

        if (!container) {
            window.Livewire.navigate(link.href)
            return
        }

        (link.closest('body') || document.querySelector('body')).scrollIntoView()
        this.load(container, link.href, { history: 'push', progress: true })
    }

    #onPopState() {
        for (const container of document.querySelectorAll(this.#containerSelector)) {
            const currentUrl = new URL(container.dataset.paginatedUrl ?? window.location.href, window.location.origin)

            if (currentUrl.pathname === window.location.pathname && currentUrl.href !== window.location.href) {
                this.load(container, window.location.href, { progress: true })
            }
        }
    }

    async load(element, url, { history = null, progress = false } = {}) {
        const container = element.closest(this.#containerSelector)
        const request = new AbortController()

        this.#requests.get(container)?.abort()
        this.#requests.set(container, request)
        this.#setLoading(container, true)

        if (progress) {
            this.#progressBar.start()
        }

        try {
            const response = await fetch(url, { headers: { Accept: 'text/html' }, signal: request.signal })

            if (!response.ok) {
                window.Livewire.navigate(url)
                return
            }

            const page = new DOMParser().parseFromString(await response.text(), 'text/html')
            const replacement = page.querySelector(`[data-paginated="${CSS.escape(container.dataset.paginated)}"]`)

            if (!replacement) {
                window.Livewire.navigate(url)
                return
            }

            replacement.dataset.paginatedUrl = url
            this.#morph(container, replacement.outerHTML)

            if (history !== null) {
                const state = { ...(window.history.state ?? {}), paginated: container.dataset.paginated }

                history === 'push'
                    ? window.history.pushState(state, '', url)
                    : window.history.replaceState(state, '', url)
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                throw error
            }
        } finally {
            if (this.#requests.get(container) === request) {
                this.#requests.delete(container)
                this.#setLoading(container, false)
                this.#progressBar.finish()
            }
        }
    }

    #morph(container, html) {
        const stale = []

        window.Alpine.morph(container, html, {
            key: (element) => element.getAttribute('wire:key') ?? element.getAttribute('key') ?? element.id,
            updating: (from, to, childrenOnly, skip) => {
                if (from.nodeType !== 1) {
                    return
                }

                if (from.hasAttribute('wire:ignore')) {
                    skip()
                    return
                }

                if (from.hasAttribute('wire:ignore.self')) {
                    childrenOnly()
                }

                if (from.hasAttribute('x-data') && from.getAttribute('x-data') !== to.getAttribute('x-data')) {
                    stale.push(from)
                }
            },
        })

        for (const element of stale) {
            window.Alpine.destroyTree(element)
            window.Alpine.initTree(element)
        }
    }

    #setLoading(container, loading) {
        for (const indicator of container.querySelectorAll(this.#loadingSelector)) {
            if (indicator.dataset.loading === 'hide') {
                indicator.classList.toggle('invisible', loading)
                continue
            }

            indicator.classList.toggle('hidden', !loading)
        }
    }
}
