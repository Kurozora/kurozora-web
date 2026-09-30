// search-bar.js

export default class SearchBarManager {
    #formSelector = 'form[data-search-bar]'
    #typedSelector = 'input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]), textarea'
    #buttonSelector = '[data-search-choice], [data-search-reset]'
    #delay = 500
    #timers = new WeakMap()
    #paginationManager = null

    constructor(paginationManager) {
        this.#paginationManager = paginationManager

        document.addEventListener('input', (event) => this.#onInput(event))
        document.addEventListener('change', (event) => this.#onChange(event))
        document.addEventListener('submit', (event) => this.#onSubmit(event))
        document.addEventListener('click', (event) => this.#onClick(event))
    }

    #onInput(event) {
        const form = event.target.closest(this.#formSelector)

        if (!form || !event.target.matches(this.#typedSelector)) {
            return
        }

        clearTimeout(this.#timers.get(form))
        this.#timers.set(form, setTimeout(() => this.submit(form), this.#delay))
    }

    #onChange(event) {
        const form = event.target.closest(this.#formSelector)

        if (!form || event.target.matches(this.#typedSelector)) {
            return
        }

        this.submit(form)
    }

    #onSubmit(event) {
        if (!event.target.matches(this.#formSelector)) {
            return
        }

        event.preventDefault()
        this.submit(event.target)
    }

    #onClick(event) {
        const button = event.target.closest(this.#buttonSelector)
        const form = button?.form?.matches(this.#formSelector) ? button.form : button?.closest(this.#formSelector)

        if (!form) {
            return
        }

        event.preventDefault()

        if (button.dataset.searchChoice !== undefined) {
            form.elements[button.dataset.searchChoice].value = button.value
        } else {
            this.#reset(form, button.dataset.searchReset)
        }

        this.submit(form)
    }

    #reset(form, group) {
        for (const control of form.elements) {
            if (!control.name.startsWith(group + '[')) {
                continue
            }

            if (control.multiple) {
                for (const option of control.options) {
                    option.selected = false
                }
            } else {
                control.value = ''
            }
        }
    }

    submit(form) {
        clearTimeout(this.#timers.get(form))
        this.#paginationManager.load(form, this.#url(form), { history: 'replace' })
    }

    #url(form) {
        const url = new URL(form.action)
        const params = new URLSearchParams()

        for (const [name, value] of new FormData(form)) {
            if (value !== '') {
                params.append(name, value)
            }
        }

        url.search = params.toString()

        return url.href
    }
}
