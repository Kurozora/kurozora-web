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
     * Rendering context for the canvas.
     *
     * @type {RenderingContext | null}
     */
    ctx

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
     * Creates a new GIF player instance.
     *
     * @param canvas {HTMLCanvasElement} Canvas element to render the GIF on.
     * @param gifUrl {string} URL of the GIF to load.
     * @param speed {number} Playback speed multiplier (default is 1.0).
     */
    constructor({canvas, gifUrl, speed = 1.0}) {
        this.canvas = canvas
        this.ctx = canvas.getContext('2d')
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
            const delay = (frame.delay || 10) / this.speed // delay is in hundredths of sec

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
     * Renders a specific frame on the canvas.
     *
     * @param frame {ParsedFrame} The frame to render.
     */
    renderFrame(frame) {
        const imgData = new ImageData(new Uint8ClampedArray(frame.patch), frame.dims.width, frame.dims.height)
        this.canvas.width = frame.dims.width
        this.canvas.height = frame.dims.height
        this.ctx.putImageData(imgData, frame.dims.left, frame.dims.top)

        this.elapsedTime = this.frames
            .slice(0, this.frameIndex)
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
     * @param frameIndex
     */
    scrubTo(frameIndex) {
        this.pause()
        this.frameIndex = frameIndex
        this.renderFrame(this.frames[frameIndex])
    }

    /**
     * Sets the playback speed of the GIF.
     *
     * @param speed
     */
    setSpeed(speed) {
        this.speed = speed
    }

    formatTime(ms) {
        const totalSeconds = Math.floor(ms / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }

    get currentTimeFormatted() {
        return this.formatTime(this.elapsedTime);
    }

    get totalTimeFormatted() {
        return this.formatTime(this.totalDuration);
    }
}
