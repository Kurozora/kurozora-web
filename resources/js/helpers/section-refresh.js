// section-refresh.js

export default class SectionRefreshManager {
    #selector = '[data-section-refresh]'

    constructor() {
        document.addEventListener('click', (event) => this.#onClick(event))
    }

    #onClick(event) {
        const trigger = event.target.closest(this.#selector)

        if (!trigger) {
            return
        }

        event.preventDefault()

        this.refresh(trigger)
    }

    async refresh(trigger) {
        const section = trigger.closest('[data-section]')
        const url = trigger.dataset.sectionRefresh

        if (!section || !url || trigger.dataset.busy === 'true') {
            return
        }

        const spinner = section.querySelector('[data-section-spinner]')

        trigger.dataset.busy = 'true'
        spinner?.classList.remove('hidden')

        try {
            const response = await fetch(url, { headers: { Accept: 'text/html' } })

            if (!response.ok) {
                return
            }

            const markup = document.createRange().createContextualFragment(await response.text())

            section.replaceWith(markup)
        } finally {
            delete trigger.dataset.busy
            spinner?.classList.add('hidden')
        }
    }
}
