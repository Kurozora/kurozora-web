export default class Prompt {
    /**
     * Reveals a permission prompt with an iOS-style scale-and-fade transition.
     *
     * @param {string} id - the prompt's element id
     */
    static show(id) {
        const overlay = document.getElementById(id)
        if (!overlay) {
            return
        }

        const card = overlay.querySelector('[data-prompt-card]')
        overlay.classList.remove('hidden')

        requestAnimationFrame(() => requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0')
            card?.classList.remove('opacity-0', 'scale-95')
        }))
    }

    /**
     * Dismisses a permission prompt, hiding it once the transition completes.
     *
     * @param {string} id - the prompt's element id
     */
    static hide(id) {
        const overlay = document.getElementById(id)
        if (!overlay) {
            return
        }

        const card = overlay.querySelector('[data-prompt-card]')
        overlay.classList.add('opacity-0')
        card?.classList.add('opacity-0', 'scale-95')

        setTimeout(() => overlay.classList.add('hidden'), 200)
    }
}
