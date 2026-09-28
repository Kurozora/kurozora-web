// explore-section.js

export default class ExploreSectionManager {
    #selector = '[data-explore-section-refresh]'

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
        const section = trigger.closest('[data-explore-section]')
        const url = trigger.dataset.exploreSectionRefresh

        if (!section || !url || trigger.dataset.busy === 'true') {
            return
        }

        trigger.dataset.busy = 'true'

        try {
            const response = await fetch(url, { headers: { Accept: 'text/html' } })

            if (!response.ok) {
                return
            }

            const markup = document.createRange().createContextualFragment(await response.text())

            section.replaceWith(markup)
        } finally {
            delete trigger.dataset.busy
        }
    }
}
