// subscription-sheet.js

export default class SubscriptionSheetManager {
    #id = 'subscription-sheet'

    constructor() {
        document.addEventListener('livewire:init', () => {
            window.Livewire.on('present-subscription-sheet', (payload) => this.present(payload))
        })

        window.addEventListener('present-subscription-sheet', (event) => this.present(event.detail))
    }

    present(payload) {
        const { title, message, tipJarEnabled } = Array.isArray(payload) ? payload[0] ?? {} : payload ?? {}

        const titleElement = document.querySelector('[data-subscription-title]')
        const messageElement = document.querySelector('[data-subscription-message]')
        const availabilityElement = document.querySelector('[data-subscription-availability]')
        const tipJarElement = document.querySelector('[data-subscription-tip-jar]')

        if (!titleElement || !messageElement || !availabilityElement) {
            return
        }

        const container = availabilityElement.parentElement

        titleElement.textContent = title ?? ''
        messageElement.textContent = message ?? ''
        availabilityElement.textContent = tipJarEnabled
            ? container.dataset.subscriptionAvailabilityWithTipJar
            : container.dataset.subscriptionAvailabilityWithoutTipJar

        tipJarElement?.classList.toggle('hidden', !tipJarEnabled)

        window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: this.#id } }))
    }
}
