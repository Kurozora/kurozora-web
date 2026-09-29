// pagination.js

import ProgressBar from './progress-bar'

export default class PaginationManager {
    #linkSelector = 'nav[aria-label="Pagination Navigation"] a[href]'
    #containerSelector = '[data-paginated]'
    #livewireMethods = ['gotoPage', 'nextPage', 'previousPage', 'setPage']
    #progressBar = new ProgressBar()

    constructor() {
        document.addEventListener('click', (event) => this.#onClick(event))
        document.addEventListener('livewire:init', () => this.#observeLivewire())
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
        this.#paginate(container, link.href, { push: true })
    }

    #onPopState() {
        for (const container of document.querySelectorAll(this.#containerSelector)) {
            const currentUrl = container.dataset.paginatedUrl ?? window.location.href

            if (currentUrl !== window.location.href) {
                this.#paginate(container, window.location.href, { push: false })
            }
        }
    }

    async #paginate(container, url, { push }) {
        if (container.dataset.busy === 'true') {
            return
        }

        container.dataset.busy = 'true'
        this.#progressBar.start()

        try {
            const response = await fetch(url, { headers: { Accept: 'text/html' } })

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
            window.Alpine.morph(container, replacement.outerHTML)

            if (push) {
                window.history.pushState({ ...(window.history.state ?? {}), paginated: container.dataset.paginated }, '', url)
            }
        } finally {
            this.#progressBar.finish()
            delete container.dataset.busy
        }
    }
}
