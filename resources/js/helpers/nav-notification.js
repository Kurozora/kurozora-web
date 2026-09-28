// nav-notification.js

export default class NavNotificationManager {
    #events = ['.notification.created', '.notification.read', '.notification.deleted']

    constructor() {
        this.#subscribe()
    }

    #element() {
        return document.querySelector('[data-nav-notification]')
    }

    #subscribe() {
        const element = this.#element()

        if (!element || !window.Echo) {
            return
        }

        const channel = window.Echo.private('users.' + element.dataset.navNotification)

        for (const event of this.#events) {
            channel.listen(event, () => this.refresh())
        }
    }

    async refresh() {
        const badges = document.querySelectorAll('[data-nav-notification-badge]')

        if (!badges.length) {
            return
        }

        const response = await fetch('/notifications/unread', { headers: { Accept: 'application/json' } })

        if (!response.ok) {
            return
        }

        const { hasUnread } = await response.json()

        badges.forEach((badge) => badge.classList.toggle('hidden', !hasUnread))
    }
}
