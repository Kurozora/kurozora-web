/**
 * Alpine data component for the Kotodama board.
 */
document.addEventListener('alpine:init', () => {
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
})
