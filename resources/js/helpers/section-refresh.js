// section-refresh.js

export default class SectionRefreshManager {
    #triggerSelector = '[data-section-refresh]'
    #listenerSelector = '[data-section][data-section-refresh-on]'
    #subscribedEvents = new Set()

    constructor() {
        document.addEventListener('click', (event) => this.#onClick(event))
        document.addEventListener('DOMContentLoaded', () => this.#subscribe())
        document.addEventListener('livewire:navigated', () => this.#subscribe())
    }

    #onClick(event) {
        const trigger = event.target.closest(this.#triggerSelector)

        if (!trigger) {
            return
        }

        event.preventDefault()

        this.refresh(trigger.closest('[data-section]'), trigger.dataset.sectionRefresh)
    }

    #subscribe() {
        for (const section of document.querySelectorAll(this.#listenerSelector)) {
            for (const eventName of section.dataset.sectionRefreshOn.split(' ')) {
                if (this.#subscribedEvents.has(eventName)) {
                    continue
                }

                this.#subscribedEvents.add(eventName)
                window.addEventListener(eventName, () => this.#refreshSubscribers(eventName))
            }
        }
    }

    #refreshSubscribers(eventName) {
        for (const section of document.querySelectorAll(this.#listenerSelector)) {
            if (section.dataset.sectionRefreshOn.split(' ').includes(eventName)) {
                this.refresh(section, section.dataset.sectionUrl)
            }
        }
    }

    async refresh(section, url) {
        if (!section || !url || section.dataset.busy === 'true') {
            return
        }

        const spinner = section.querySelector('[data-section-spinner]')

        section.dataset.busy = 'true'
        spinner?.classList.remove('hidden')

        try {
            const response = await fetch(url, { headers: { Accept: 'text/html' } })

            if (!response.ok) {
                return
            }

            const markup = document.createRange().createContextualFragment(await response.text())

            section.replaceWith(markup)
        } finally {
            delete section.dataset.busy
            spinner?.classList.add('hidden')
        }
    }
}
