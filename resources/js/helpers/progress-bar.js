// progress-bar.js

export default class ProgressBar {
    #element = null
    #delay = null
    #trickle = null
    #progress = 0

    start() {
        if (this.#element || this.#delay) {
            return
        }

        this.#delay = setTimeout(() => {
            this.#delay = null
            this.#render()
            this.#set(0.1)
            this.#trickle = setInterval(() => this.#set(this.#progress + (0.95 - this.#progress) * 0.1), 200)
        }, 150)
    }

    finish() {
        clearTimeout(this.#delay)
        clearInterval(this.#trickle)
        this.#delay = null
        this.#trickle = null

        if (!this.#element) {
            return
        }

        const element = this.#element

        this.#set(1)
        this.#element = null
        setTimeout(() => element.remove(), 250)
    }

    #render() {
        this.#element = document.createElement('div')
        this.#element.id = 'nprogress'
        this.#element.innerHTML = '<div class="bar" role="bar"><div class="peg"></div></div>'
        document.body.append(this.#element)
    }

    #set(progress) {
        const bar = this.#element.querySelector('.bar')

        this.#progress = Math.min(progress, 1)
        bar.style.transition = 'transform 200ms linear'
        bar.style.transform = `translate3d(${(this.#progress - 1) * 100}%, 0, 0)`
    }
}
