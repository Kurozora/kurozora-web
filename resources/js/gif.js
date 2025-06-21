import {GIF} from './helpers/gif'

window.gifPlayer = (url) => ({
    manager: null,
    currentFrame: 0,
    speed: '1.00',
    isTouch: false,
    showControls: false,
    hideControlsTimeout: null,
    isCustomSpeed: null,
    playbackSpeeds: [0.5, 1.0, 1.50, 1.75, 2.00],
    minPlaybackSpeed: 0.25,
    maxPlaybackSpeed: 4.00,

    async init() {
        this.$watch('speed', (value) => this.changeSpeed(value))

        this.manager = new GIF({
            canvas: this.$refs.canvas,
            gifUrl: url,
            speed: this.speed
        })
        this.manager.init()
    },

    scrub() {
        this.manager.scrubTo(parseInt(this.currentFrame))
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
        this.isTouch = event.pointerType !== 'mouse';
        console.log('onPointerDown:', 'pointerType:', event.pointerType)
    },

    onPointerUp(event) {
        console.log('onPointerUp:', this.isTouch ? 'touch' : event.pointerType);
        if (this.isTouch) {
            this.toggleControls()
        }
    },

    onPointerEnter(event) {
        console.log('onPointerEnter:', this.isTouch ? 'touch' : event.pointerType);
        if (!this.isTouch) {
            this.showControls = true
        }
    },

    onPointerLeave(event) {
        console.log('onPointerLeave:', this.isTouch ? 'touch' : event.pointerType);
        if (!this.isTouch) {
            this.showControls = false
        }
    }
})
