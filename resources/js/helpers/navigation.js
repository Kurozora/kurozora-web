// navigation.js

export default class NavigationManager {
    #url = '/me/navigation'
    #selectors = ['[data-navigation-sidebar]', '[data-navigation-dropdown]']

    constructor() {
        document.addEventListener('livewire:init', () => {
            window.Livewire.on('refresh-navigation-dropdown', () => this.refresh())
        })

        window.addEventListener('refresh-navigation-dropdown', () => this.refresh())
    }

    async refresh() {
        if (!document.querySelector(this.#selectors[1])) {
            return
        }

        const response = await fetch(this.#url, { headers: { Accept: 'text/html' } })

        if (!response.ok) {
            return
        }

        const markup = document.createRange().createContextualFragment(await response.text())

        for (const selector of this.#selectors) {
            const current = document.querySelector(selector)
            const replacement = markup.querySelector(selector)

            if (current && replacement) {
                current.replaceWith(replacement)
            }
        }
    }
}
