import {GIF} from './helpers/gif'

window.gifPlayer = (url) => ({
    manager: null,
    speed: '1.00',
    isTouch: false,
    showControls: false,
    hideControlsTimeout: null,
    isCustomSpeed: null,
    playbackSpeeds: [0.5, 1.0, 1.50, 1.75, 2.00],
    minPlaybackSpeed: 0.25,
    maxPlaybackSpeed: 4.00,
    pointerDownTime: 0,
    isHovering: false,

    async init() {
        this.$watch('speed', (value) => this.changeSpeed(value))

        this.manager = new GIF({
            canvas: this.$refs.canvas,
            gifUrl: url,
            speed: this.speed
        })
        this.manager.init()
    },

    scrub(event) {
        this.manager.scrubTo(event.srcElement.value)
    },

    changeSpeed() {
        let speed = parseFloat(this.speed)

        if (isNaN(speed) || speed < this.minPlaybackSpeed) {
            speed = this.minPlaybackSpeed
        } else if (speed > this.maxPlaybackSpeed) {
            speed = this.maxPlaybackSpeed
        }

        this.speed = speed.toFixed(2).toString()
        this.isCustomSpeed = !this.playbackSpeeds.includes(speed)
        this.manager.setSpeed(speed)
    },

    showControlsTemporarily() {
        this.showControls = true

        clearTimeout(this.hideControlsTimeout)

        this.hideControlsTimeout = setTimeout(() => {
            if (!this.isTouch) this.showControls = false
        }, 2000)
    },

    toggleControls() {
        this.showControls = !this.showControls
    },

    onPointerDown(event) {
        if (
            event.target !== this.$refs.canvas
            && event.target !== this.$refs.controlOverlay
            && event.target !== event.currentTarget
        ) {
            return
        }

        this.isTouch = event.pointerType !== 'mouse'
        this.pointerDownTime = Date.now()
    },

    onPointerUp(event) {
        if (!this.isHovering) {
            const tapDuration = Date.now() - this.pointerDownTime

            if (tapDuration <= 200 && !this.isHovering) {
                this.toggleControls()
            }
        }
    },

    onPointerEnter(event) {
        this.isTouch = event.pointerType !== 'mouse'

        if (!this.isTouch) {
            this.showControls = true
            this.isHovering = true
        }
    },

    onPointerLeave(event) {
        if (event.target !== event.currentTarget) {
            return
        }

        if (!this.isTouch) {
            this.showControls = false
            this.isHovering = false
        }
    }
})
