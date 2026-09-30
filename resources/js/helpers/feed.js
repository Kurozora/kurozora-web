// feed.js

export default class FeedManager {
    #root = null
    #timer = null
    #pending = 0

    constructor() {
        document.addEventListener('livewire:navigated', () => this.#setup())
        window.addEventListener('postSubmitted', () => this.#showNewer())
        window.addEventListener('focus', () => this.#startPolling())
        window.addEventListener('blur', () => this.#stopPolling())
    }

    #setup() {
        this.#stopPolling()
        this.#root = document.querySelector('[data-feed-list]')

        if (!this.#root || this.#root.dataset.feedReady === 'true') {
            return
        }

        this.#root.dataset.feedReady = 'true'
        this.#root.querySelector('[data-feed-show-newer]')?.addEventListener('click', () => this.#showNewer())
        this.#observeMore()

        if (document.hasFocus()) {
            this.#startPolling()
        }
    }

    #observeMore() {
        const sentinel = this.#root.querySelector('[data-feed-more]')

        if (!sentinel) {
            return
        }

        const observer = new IntersectionObserver((entries) => {
            if (!entries.some((entry) => entry.isIntersecting)) {
                return
            }

            observer.disconnect()
            this.#loadMore(sentinel)
        }, { rootMargin: '50% 0px' })

        observer.observe(sentinel)
    }

    async #loadMore(sentinel) {
        const spinner = this.#root.querySelector('[data-feed-loading]')

        spinner?.classList.remove('hidden')

        try {
            const page = await this.#fetchPage({ cursor: sentinel.dataset.feedCursor })

            if (!page) {
                return
            }

            this.#root.querySelector('[data-feed-messages]').append(...page.children)

            if (page.dataset.feedNext) {
                sentinel.dataset.feedCursor = page.dataset.feedNext
                this.#observeMore()
            } else {
                sentinel.remove()
            }
        } finally {
            spinner?.classList.add('hidden')
        }
    }

    #startPolling() {
        if (!this.#root || this.#timer !== null) {
            return
        }

        this.#timer = window.setInterval(() => this.#poll(), 30000)
    }

    #stopPolling() {
        if (this.#timer === null) {
            return
        }

        window.clearInterval(this.#timer)
        this.#timer = null
    }

    async #poll() {
        const response = await fetch(this.#url({ after: this.#root.dataset.feedLatest }), { headers: { Accept: 'application/json' } })

        if (!response.ok) {
            return
        }

        const { count } = await response.json()

        this.#pending = count
        this.#renderNewerButton()
    }

    #renderNewerButton() {
        const container = this.#root.querySelector('[data-feed-newer]')
        const button = this.#root.querySelector('[data-feed-show-newer]')

        if (!container || !button) {
            return
        }

        container.classList.toggle('hidden', this.#pending === 0)

        const template = this.#pending === 1 ? this.#root.dataset.feedLabelOne : this.#root.dataset.feedLabelMany

        button.textContent = template.replace(':x', this.#pending)
    }

    async #showNewer() {
        if (!this.#root) {
            return
        }

        const page = await this.#fetchPage({ after: this.#root.dataset.feedLatest })

        if (!page) {
            return
        }

        this.#root.querySelector('[data-feed-messages]').prepend(...page.children)

        if (page.dataset.feedLatest) {
            this.#root.dataset.feedLatest = page.dataset.feedLatest
        }

        this.#pending = 0
        this.#renderNewerButton()
    }

    async #fetchPage(params) {
        const response = await fetch(this.#url(params), { headers: { Accept: 'text/html' } })

        if (!response.ok) {
            return null
        }

        const markup = document.createRange().createContextualFragment(await response.text())

        return markup.querySelector('[data-feed-page]')
    }

    #url(params) {
        const url = new URL(this.#root.dataset.feedUrl, window.location.origin)

        Object.entries(params).forEach(([key, value]) => {
            if (value) {
                url.searchParams.set(key, value)
            }
        })

        return url
    }
}
