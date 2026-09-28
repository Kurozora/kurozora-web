// alert.js

export default class AlertManager {
    #id = 'alert'

    constructor() {
        document.addEventListener('livewire:init', () => {
            window.Livewire.on('present-alert', (payload) => this.present(payload))
        })

        window.addEventListener('present-alert', (event) => this.present(event.detail))
    }

    present(payload) {
        const { title, message } = Array.isArray(payload) ? payload[0] ?? {} : payload ?? {}

        const titleElement = document.querySelector('[data-alert-title]')
        const messageElement = document.querySelector('[data-alert-message]')

        if (!titleElement || !messageElement) {
            return
        }

        titleElement.textContent = title ?? ''
        messageElement.textContent = message ?? ''

        window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: this.#id } }))
    }
}
