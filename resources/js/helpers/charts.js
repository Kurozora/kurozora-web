export default class Charts {
    /**
     * The charts root element.
     *
     * @type {?HTMLElement}
     */
    #root = null

    /**
     * The toggle that dims entries already in the user's library.
     *
     * @type {?HTMLElement}
     */
    #libraryToggle = null

    /**
     * Whether library entries are currently dimmed.
     *
     * @type {boolean}
     */
    #dimLibrary = false

    /**
     * Observes the page for content changes to re-apply the dim-library state.
     *
     * @type {?MutationObserver}
     */
    #mutationObserver = null

    /**
     * Mounts the charts page after every navigation, including the first load.
     */
    constructor() {
        document.addEventListener('livewire:navigated', () => this.#mount())
    }

    /**
     * Wires up the charts page, or stays inert when absent.
     */
    #mount() {
        this.#unmount()

        const root = document.querySelector('[data-charts]')

        if (!root) {
            return
        }

        this.#root = root
        this.#dimLibrary = localStorage.getItem('charts-dim-library') === 'true'
        this.#mutationObserver = new MutationObserver(() => this.#refresh())
        this.#mutationObserver.observe(root, { childList: true, subtree: true })
        this.#refresh()
    }

    /**
     * Tears down the observer and listeners from a previous page.
     */
    #unmount() {
        this.#mutationObserver?.disconnect()
        this.#libraryToggle?.removeEventListener('click', this.#onLibraryToggle)
        this.#root = null
        this.#libraryToggle = null
        this.#mutationObserver = null
        this.#dimLibrary = false
    }

    /**
     * Toggles dimming of entries already in the user's library.
     */
    #onLibraryToggle = () => {
        this.#dimLibrary = !this.#dimLibrary
        this.#refresh()

        localStorage.setItem('charts-dim-library', String(this.#dimLibrary))
    }

    /**
     * Re-applies the dim-library state to the toggle and the lockups.
     */
    #refresh() {
        if (!this.#root) {
            return
        }

        const libraryToggle = this.#root.querySelector('[data-charts-library-toggle]')

        if (libraryToggle !== this.#libraryToggle) {
            this.#libraryToggle?.removeEventListener('click', this.#onLibraryToggle)
            this.#libraryToggle = libraryToggle
            this.#libraryToggle?.addEventListener('click', this.#onLibraryToggle)
        }

        this.#reflectLibraryToggle()

        for (const lockup of this.#root.querySelectorAll('[data-in-library]')) {
            lockup.style.opacity = this.#dimLibrary ? '0.25' : ''
        }
    }

    /**
     * Reflects the dim-library state on the toggle button.
     */
    #reflectLibraryToggle() {
        if (!this.#libraryToggle) {
            return
        }

        this.#libraryToggle.setAttribute('aria-pressed', String(this.#dimLibrary))
        this.#libraryToggle.classList.toggle('bg-tint', this.#dimLibrary)
        this.#libraryToggle.classList.toggle('btn-text-tinted', this.#dimLibrary)
        this.#libraryToggle.classList.toggle('border-transparent', this.#dimLibrary)
        this.#libraryToggle.classList.toggle('bg-primary', !this.#dimLibrary)
        this.#libraryToggle.classList.toggle('text-tint', !this.#dimLibrary)
        this.#libraryToggle.classList.toggle('border-tint', !this.#dimLibrary)
        this.#libraryToggle.querySelector('[data-charts-library-icon-off]')?.classList.toggle('hidden', this.#dimLibrary)
        this.#libraryToggle.querySelector('[data-charts-library-icon-on]')?.classList.toggle('hidden', !this.#dimLibrary)
    }
}
