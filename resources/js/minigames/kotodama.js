/**
 * Alpine store for the Kotodama color-blind accessibility toggle.
 *
 * Mirrors the system "increase contrast" preference by default; an explicit
 * toggle persists an override to localStorage so it survives reloads even
 * when the media query is spoofed (e.g. Firefox resist-fingerprinting).
 */
const KOTODAMA_DIFFERENTIATE_STORAGE_KEY = 'kotodama-differentiate'

document.addEventListener('alpine:init', () => {
    window.Alpine.store('kotodamaAccessibility', {
        system: window.matchMedia('(prefers-contrast: more)').matches,
        override: localStorage.getItem(KOTODAMA_DIFFERENTIATE_STORAGE_KEY),

        get enabled() {
            if (this.override !== null) return this.override === '1'
            return this.system
        },

        toggle() {
            const nextEnabled = !this.enabled
            this.override = nextEnabled ? '1' : '0'
            localStorage.setItem(KOTODAMA_DIFFERENTIATE_STORAGE_KEY, this.override)
        },
    })

    window.matchMedia('(prefers-contrast: more)').addEventListener('change', (event) => {
        window.Alpine.store('kotodamaAccessibility').system = event.matches
    })

    window.Alpine.data('kotodamaBoard', ({ length = 5 } = {}) => ({
        length,
        currentGuess: '',

        pressKey(key) {
            if (this.currentGuess.length >= this.length) return
            if (!/^[a-z]$/.test(key)) return
            this.currentGuess += key
        },

        backspaceKey() {
            this.currentGuess = this.currentGuess.slice(0, -1)
        },

        submitGuess() {
            if (this.currentGuess.length !== this.length) return
            const guess = this.currentGuess
            this.currentGuess = ''
            this.$wire.submit(guess)
        },

        handleKey(event) {
            if (event.metaKey || event.ctrlKey || event.altKey) return

            if (event.key === 'Enter') {
                this.submitGuess()
                event.preventDefault()
                return
            }

            if (event.key === 'Backspace') {
                this.backspaceKey()
                event.preventDefault()
                return
            }

            const lower = event.key.toLowerCase()
            if (/^[a-z]$/.test(lower)) {
                this.pressKey(lower)
                event.preventDefault()
            }
        },
    }))

    window.Alpine.data('kotodamaCountdown', ({ unlockAt } = {}) => ({
        unlockAt,
        text: '',
        intervalId: null,

        start() {
            this.tick()
            this.intervalId = setInterval(() => this.tick(), 1000)
        },

        tick() {
            const remainingMs = this.unlockAt - Date.now()

            if (remainingMs <= 0) {
                clearInterval(this.intervalId)
                window.location.reload()
                return
            }

            this.text = this.$el.dataset.template.replace(':time', this.formatDuration(remainingMs))
        },

        formatDuration(remainingMs) {
            const totalSeconds = Math.floor(remainingMs / 1000)
            const hours = Math.floor(totalSeconds / 3600)
            const minutes = Math.floor((totalSeconds % 3600) / 60)
            const seconds = totalSeconds % 60

            return [hours, minutes, seconds]
                .map((unit) => String(unit).padStart(2, '0'))
                .join(':')
        },
    }))
})
