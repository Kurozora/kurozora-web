import {decompressFrames, ParsedFrame, parseGIF} from 'gifuct-js'

/**
 * GIF Player class to handle loading and rendering GIFs on a canvas.
 */
export class GIF {
    /**
     * Canvas element where the GIF will be rendered.
     *
     * @type {HTMLCanvasElement}
     */
    canvas

    /**
     * Rendering context for the canvas.
     *
     * @type {RenderingContext | null}
     */
    ctx

    /**
     * Canvas used for temporary rendering of GIF frames.
     *
     * @type {HTMLCanvasElement}
     */
    tempCanvas

    /**
     * Rendering context for the temporary canvas.
     *
     * @type {RenderingContext | null}
     */
    tempCtx

    /**
     * Canvas used for full GIF rendering.
     *
     * @type {HTMLCanvasElement}
     */
    gifCanvas

    /**
     * Rendering context for the full GIF canvas.
     *
     * @type {RenderingContext | null}
     */
    gifCtx

    /**
     * ImageData object to hold the current frame's pixel data.
     *
     * @type {ImageData | null}
     */
    imgData

    /**
     * URL of the GIF to load.
     *
     * @type {string}
     */
    gifUrl

    /**
     * Playback speed multiplier for the GIF.
     *
     * @type {number}
     */
    speed

    /**
     * Indicates if the GIF is currently playing.
     *
     * @type {boolean}
     */
    isPlaying

    /**
     * Total number of frames in the GIF.
     *
     * @type {number}
     */
    frameCount

    /**
     * Total duration of the GIF in milliseconds.
     *
     * @type {number}
     */
    totalDuration

    /**
     * Elapsed time since the GIF started playing, in milliseconds.
     *
     * @type {number}
     */
    elapsedTime

    /**
     * Array of parsed frames from the GIF.
     *
     * @type {ParsedFrame[]}
     */
    frames

    /**
     * The current index of the frame being rendered.
     *
     * @type {number}
     */
    frameIndex

    /**
     * Timestamp of the last frame rendered, used for timing.
     *
     * @type {number}
     */
    lastRender

    /**
     * Determines if the GIF needs to be cleared before rendering the next frame.
     *
     * @type {boolean}
     */
    needsDisposal

    /**
     * Creates a new GIF player instance.
     *
     * @param canvas {HTMLCanvasElement} Canvas element to render the GIF on.
     * @param gifUrl {string} URL of the GIF to load.
     * @param speed {number} Playback speed multiplier (default is 1.0).
     */
    constructor({canvas, gifUrl, speed = 1.0}) {
        // User canvas
        this.canvas = canvas
        this.ctx = canvas.getContext('2d')
        // GIF patch canvas
        this.tempCanvas = document.createElement('canvas')
        this.tempCtx = this.tempCanvas.getContext('2d')
        // Full GIF canvas
        this.gifCanvas = document.createElement('canvas')
        this.gifCtx = this.gifCanvas.getContext('2d')

        this.gifUrl = gifUrl
        this.speed = speed

        this.frames = []
        this.frameCount = 0
        this.frameIndex = 0
        this.isPlaying = false
        this.lastRender = 0
        this.totalDuration = 0
        this.elapsedTime = 0
    }

    /**
     * Initializes the GIF player by loading the GIF and setting up the frames.
     *
     * @returns {Promise<void>}
     */
    async init() {
        await this.loadGIF()
        this.frameCount = this.frames.length
        this.totalDuration = this.frames.map(frame => frame.delay)
            .reduce(function (sum, delay) {
                return sum + delay
            }, 0)

        const isReduced = window.matchMedia(`(prefers-reduced-motion: reduce)`) === true || window.matchMedia(`(prefers-reduced-motion: reduce)`).matches === true;
        if (!isReduced) {
            this.play()
        }
    }

    /**
     * Loads the GIF from the specified URL and parses its frames.
     *
     * @returns {Promise<void>}
     */
    async loadGIF() {
        const response = await fetch(this.gifUrl)
        const buffer = await response.arrayBuffer()
        const parsedGif = parseGIF(buffer)
        this.frames = decompressFrames(parsedGif, true)

        this.canvas.width = this.frames[0].dims.width
        this.canvas.height = this.frames[0].dims.height

        this.gifCanvas.width = this.canvas.width
        this.gifCanvas.height = this.canvas.height
    }

    /**
     * Starts the rendering loop for the GIF.
     */
    startRenderLoop() {
        const render = (timestamp) => {
            if (!this.isPlaying) {
                return
            }

            const frame = this.frames[this.frameIndex]
            const delay = (frame.delay || 10) / this.speed

            if (timestamp - this.lastRender > delay) {
                this.frameIndex = (this.frameIndex + 1) % this.frames.length
                this.renderFrame(frame)
                this.lastRender = timestamp
            }

            requestAnimationFrame(render)
        }
        requestAnimationFrame(render)
    }

    /**
     * Renders a specific patch of the GIF frame onto the temporary canvas.
     *
     * @param frame {ParsedFrame} The frame to render.
     */
    renderPatch(frame) {
        if (
            !this.imgData ||
            frame.dims.width !== this.imgData.width ||
            frame.dims.height !== this.imgData.height
        ) {
            this.tempCanvas.width = frame.dims.width
            this.tempCanvas.height = frame.dims.height
            this.imgData = this.tempCtx.createImageData(frame.dims.width, frame.dims.height)
        }

        // Set the patch data as an override
        this.imgData.data.set(frame.patch)

        // Draw the patch back over the canvas
        this.tempCtx.putImageData(this.imgData, 0,0)
        this.gifCtx.drawImage(this.tempCanvas, 0,0)
    }

    /**
     * Manipulates the GIF canvas to apply the current frame's pixel data.
     */
    manipulate() {
        let imgData = this.gifCtx.getImageData(0,0, this.gifCanvas.width, this.gifCanvas.height)
        this.ctx.putImageData(imgData, 0,0)
    }

    /**
     * Renders a specific frame on the canvas.
     *
     * @param frame {ParsedFrame} The frame to render.
     */
    renderFrame(frame) {
        if (this.needsDisposal) {
            this.gifCtx.clearRect(0,0, this.canvas.width, this.canvas.height)
            this.needsDisposal = false
        }

        this.renderPatch(frame)
        this.manipulate()

        if (frame.disposalType === 2) {
            this.needsDisposal = true
        }

        this.elapsedTime = this.frames
            .slice(0, this.frameIndex + (this.frameIndex === 0 ? 0 : 1))
            .map(frame => frame.delay)
            .reduce((sum, delay) => sum + delay, 0)
    }

    /**
     * Starts playing the GIF.
     */
    play() {
        this.isPlaying = true
        this.startRenderLoop()
    }

    /**
     * Pauses the GIF playback.
     */
    pause() {
        this.isPlaying = false
    }

    /**
     * Toggles the playback state of the GIF.
     */
    toggle() {
        if(this.isPlaying) {
            this.pause()
        } else {
            this.play()
        }
    }

    /**
     * Scrubs to a specific frame in the GIF.
     *
     * @param time {number} The time in milliseconds to scrub to.
     */
    scrubTo(time) {
        this.pause()

        let delay = 0
        this.frameIndex = this.frames.findIndex(frame => {
            delay += frame.delay
            return delay >= time
        })

        if (this.frameIndex === -1) {
            this.frameIndex = this.frames.length - 1
        }

        let frame = this.frames[this.frameIndex]

        if (frame.disposalType === 2) {
            this.needsDisposal = true
        }

        this.renderFrame(frame)
    }

    /**
     * Sets the playback speed of the GIF.
     *
     * @param speed
     */
    setSpeed(speed) {
        this.speed = speed
    }

    /**
     * Formats a time in milliseconds into a human-readable string.
     *
     * @param ms {number} Time in milliseconds to format.
     *
     * @returns {string}
     */
    formatTime(ms) {
        const totalSeconds = Math.floor(ms / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }

    /**
     * Formats the current time of the GIF into a human-readable string.
     *
     * @returns {string}
     */
    get currentTimeFormatted() {
        return this.formatTime(this.elapsedTime);
    }

    /**
     * Formats the total duration of the GIF into a human-readable string.
     *
     * @returns {string}
     */
    get totalTimeFormatted() {
        return this.formatTime(this.totalDuration);
    }
}
