/**
 * Plyr is a simple, lightweight, accessible and customizable HTML5, YouTube
 * and Vimeo media player that supports modern browsers.
 */
import Plyr from 'plyr'
import Screenshot from './plyr/screenshot'
import Storage from './plyr/storage'
import Amplify from './plyr/amplify'
import Audio from './plyr/audio'
import is from './utilities/is'
import element from './utilities/element'
/**
 * HLS.js is a JavaScript library that implements an HTTP Live Streaming client.
 * It relies on HTML5 video and MediaSource Extensions for playback.
 */
import Hls from 'hls.js'

export default class PlyrManager {
    // MARK: - Properties
    /**
     * The video element.
     *
     * @param {Element|HTMLElement} videoElement - videoElement
     */
    #videoElement

    /**
     * The Plyr instance.
     *
     * @param {Plyr} player - player
     */
    #player

    /**
     * The player's storage.
     *
     * @param {Storage} storage - storage
     */
    #storage

    /**
     * A configured instance of Hls.
     *
     * @param {Hls} hls - hls
     */
    #hls

    /**
     * The url of the video.
     *
     * @param {string} url - url
     */
    #url

    /**
     * Whether the video was checked for errors.
     *
     * @param {boolean} videoCheckedForError - video checked for error
     */
    #videoCheckedForError = false

    /**
     * Whether the video has expired.
     *
     * @param {boolean} videoHasExpired - video has expired
     */
    #videoHasExpired = false

    /**
     * The number of times the request was retried.
     *
     * @param {number} retryCount - retry count
     */
    #retryCount = 0

    /**
     * Plyr options.
     *
     * @param {Plyr.Options} playerOptions - default options
     */
    playerOptions = {
        controls: [
            'play-large', // The large play button in the center
            'restart', // Restart playback
            'rewind', // Rewind by the seek time (default 10 seconds)
            'play', // Play/pause playback
            'fast-forward', // Fast-forward by the seek time (default 10 seconds)
            'progress', // The progress bar and scrubber for playback and buffering
            'current-time', // The current time of playback
            'duration', // The full duration of the media
            'mute', // Toggle mute
            'volume', // Volume control
            'download', // Show a download button with a link to either the current source or a custom URL you specify in your options
            'screenshot', // Show a screenshot button that downloads the current frame as image
            'captions', // Toggle captions
            'settings', // Settings menu
            'pip', // Picture-in-picture (currently Safari only)
            'airplay', // Airplay (currently Safari only)
            'fullscreen' // Toggle fullscreen
        ],
        blankVideo: 'https://cdn.plyr.io/static/blank.mp4',
        caption: {
            active: true,
            language: 'en',
            update: true
        },
        fullscreen: {
            enabled: true,
            fallback: true,
            iosNative: true,
        },
        loadSprite: true,
        iconUrl: 'https://cdn.plyr.io/3.7.2/plyr.svg',
        i18n: {
            enterFullscreen: 'Enter fullscreen (F)',
            exitFullscreen: 'Exit fullscreen (F)',
            enableCaptions: 'Enable captions (C)',
            disableCaptions: 'Disable captions (C)',
            mute: 'Mute (M)',
            loop: 'Loop (L)',
            play: 'Play (K)',
            pause: 'Pause (K)',
            qualityLabel: {
                0: 'Auto',
            },
            restart: 'Restart (R)',
            screenshot: 'Screenshot (S)',
            unmute: 'Unmute (M)',
            audio: 'Audio',
            amplify: 'Amplify',
        },
        invertTime: true,
        mediaMetadata: {
            title: 'Anime on Kurozora',
            artist: 'Kurozora',
            album: 'Anime',
            artwork: [
                { src: 'https://dummyimage.com/96x96',   sizes: '96x96',   type: 'image/png' },
                { src: 'https://dummyimage.com/128x128', sizes: '128x128', type: 'image/png' },
                { src: 'https://dummyimage.com/192x192', sizes: '192x192', type: 'image/png' },
                { src: 'https://dummyimage.com/256x256', sizes: '256x256', type: 'image/png' },
                { src: 'https://dummyimage.com/384x384', sizes: '384x384', type: 'image/png' },
                { src: 'https://dummyimage.com/512x512', sizes: '512x512', type: 'image/png' },
            ]
        },
        toggleInvert: true,
        keyboard: {
            focused: true,
            global: true
        },
        ratio: '16:9',
        storage: {
            enabled: true,
            key: '_x_plyr'
        },
        thumbnail: {
            enabled: true,
            src: ''
        },
        tooltips: {
            controls: true,
            seek: true
        },
        youtube: {
            noCookie: true,
            autoplay: 1,
            iv_load_policy: 3,
            rel: 0,
            showinfo: 0,
            start: 0,
            modestbranding: 1,
            playsinline: 1,
            origin: ''
        },
        currentTime: 0
    }

    // MARK: - Initializers
    /**
     * Create a new instance of Kurozora Player.
     *
     * @constructor
     *
     * @param {HTMLElement|string} targets - targets
     * @param {Plyr.Options} options - options
     */
    constructor(targets, options) {
        // Get video element.
        let videoElement = typeof targets === 'string' ? document.querySelector(targets) : targets
        this.#videoElement = videoElement

        // Get url and cleanup.
        this.#url = videoElement.getAttribute('player-src').repeat(1)
        videoElement.removeAttribute('player-src')

        // Merge options.
        this.playerOptions = {
            ...this.playerOptions,
            ...options,
        }

        // For more options see: https://github.com/sampotts/plyr/#options
        if (is.youTube(this.#url) || is.iframe(this.#videoElement)) {
            this.#videoElement.querySelector('iframe').setAttribute('src', this.#url)

            if (is.youTube(this.#url)) {
                this.playerOptions.controls = this.playerOptions.controls.filter(control => {
                    return control !== 'screenshot'
                })
            }

            this.#player = new Plyr(videoElement, this.playerOptions)
            this.#setupPlayer()
        } else if (is.hls(this.#url)) {
            if (this.#videoElement.canPlayType('application/vnd.apple.mpegurl')) {
                this.#videoElement.src = this.#url

                // Default options with no quality update in case Hls is not supported.
                this.#player = new Plyr(videoElement, this.playerOptions)
                this.#setupPlayer()
                // this.#setupHTMLVideo()
            } else if (Hls.isSupported) {
                this.#hls = new Hls()
                this.#hls.loadSource(this.#url)

                // From the m3u8 playlist, hls parses the manifest and returns
                // all available video qualities. This is important, in this approach,
                // we will have one source on the Plyr player.
                this.#hls.on(Hls.Events.MANIFEST_PARSED, this.#handleHlsManifestParsed.bind(this))

                // Handle switching video quality.
                this.#hls.on(Hls.Events.LEVEL_SWITCHED, this.#handleHlsLevelSwitched.bind(this))

                // // Handle errors.
                // this.#hls.on(Hls.Events.ERROR, this.#handleHlsError.bind(this))

                // Save hls.
                this.#hls.attachMedia(this.#videoElement)
            }
        } else if (is.mp4(this.#url)) {
            this.#videoElement.setAttribute('src', this.#url)

            // Default options with no quality update in case Hls is not supported.
            this.#player = new Plyr(videoElement, this.playerOptions)
            this.#setupPlayer()
        } else {
            // Default options with no quality update in case Hls is not supported.
            this.#player = new Plyr(videoElement, this.playerOptions)
            this.#setupPlayer()
        }
    }

    // MARK: - Functions
    /**
     * Set up the player with the preconfigured options.
     */
    #setupPlayer() {
        this.#player.on('ready', (event) => {
            this.#storage = new Storage(this.#player)

            // Add audio settings
            if (is.hls(this.#url)) {
                this.#player.audio = new Audio(this.#player, this.#hls, this.#storage)
            }

            // Add amplification settings
            this.#player.amplify = new Amplify(this.#player, this.#storage)

            // Add screenshot capability
            this.#player.screenshot = new Screenshot(this.#player)

            window.addEventListener('keydown', (event) => {
                const {
                    key,
                    type,
                    altKey,
                    ctrlKey,
                    metaKey,
                    shiftKey
                } = event
                const pressed = type === 'keydown'

                if (altKey || ctrlKey || metaKey || shiftKey) {
                    return
                }

                if (!key) {
                    return
                }

                if (pressed) {
                    // Check focused element
                    // and if the focused element is not editable (e.g. text input)
                    // and any that accept key input http://webaim.org/techniques/keyboard/
                    const focused = document.activeElement

                    if (is.element(focused)) {
                        const { editable } = this.#player.config.selectors

                        if (element.matches(focused, editable)) {
                            return
                        }

                        if (event.key === 'Space' && element.matches(focused, 'button, [role^="menuitem"]')) {
                            return
                        }
                    }
                }

                if (event.key.toLowerCase() === 'r') {
                    event.preventDefault()
                    event.stopPropagation()
                    this.#player.restart()
                }
            })
        })

        // Handle can play event
        this.#player.once('canplay', (event) => {
            // Set current time
            let progress = this.playerOptions.currentTime === 0
                ? (localStorage.getItem('_x_progress' + window.location.pathname.replaceAll('/', '_')) ?? 0)
                : this.playerOptions.currentTime
            this.#player.currentTime = parseFloat(progress)
        })

        // Handle changing captions
        this.#player.on('languagechange', () => {
            if (is.hls(this.#url)) {
                setTimeout(() => this.#hls.subtitleTrack = this.#player.currentTrack, 50)
            }
        })

        // Keep track of time updates
        this.#player.on('timeupdate', (currentTime) => {
            localStorage.setItem('_x_progress' + window.location.pathname.replaceAll('/', '_'), this.#player.currentTime)
        })
    }

    /**
     * Check video url for errors.
     *
     * @param {string} videoUrl - video url
     */
    #checkVideo(videoUrl) {
        if (!this.#videoCheckedForError && void this.#player && navigator.onLine) {
            this.#videoCheckedForError = true

            let xmlHttpRequest = new XMLHttpRequest()
            xmlHttpRequest.open('GET', videoUrl)
            xmlHttpRequest.timeout = 15e3
            xmlHttpRequest.onloadend = function () {
                this.#videoHasExpired = xmlHttpRequest.status !== 200
            }.bind(this)
            xmlHttpRequest.send()
        }
    }

    /**
     * Select new video quality.
     *
     * @param newQuality - new quality
     */
    #newQuality(newQuality) {
        if (newQuality === 0) {
            this.#hls.currentLevel = -1 // Enable AUTO quality if option.value = 0
        } else {
            this.#hls.levels.forEach((level, levelIndex) => {
                if (level.height === newQuality) {
                    this.#hls.currentLevel = levelIndex
                }
            })
        }
    }

    /**
     * Handle the Hls manifest parsed event.
     *
     * @param {MANIFEST_PARSED} manifestParsed - manifest parsed
     * @param {ManifestParsedData} manifestParsedData - manifest parsed data
     */
    #handleHlsManifestParsed(manifestParsed, manifestParsedData) {
        // Transform available levels into an array of integers (height values).
        let availableQualities = this.#hls.levels.map((level) => level.height)
        availableQualities.push(0) // Append 0 (Auto) to quality array
        availableQualities = availableQualities.reverse()

        // Add new qualities to option
        this.playerOptions.quality = {
            default: availableQualities[0],
            options: availableQualities,
            forced: true, // This ensures Plyr to use Hls to update quality level
            onChange: (newQuality) => this.#newQuality(newQuality),
        }

        // Initialize here
        this.#player = new Plyr(this.#videoElement, this.playerOptions)
        this.#setupPlayer()
    }

    /**
     * Handles the level switching in Hls.
     *
     * @param {LEVEL_SWITCHED} levelSwitched - level switched
     * @param {LevelSwitchedData} levelSwitchedData - level switched data
     */
    #handleHlsLevelSwitched(levelSwitched, levelSwitchedData) {
        let autoQualityOption = document.querySelector('.plyr__menu__container [data-plyr=\'quality\'][value=\'0\'] span')

        if (this.#hls.autoLevelEnabled) {
            autoQualityOption.innerHTML = `AUTO (${this.#hls.levels[levelSwitchedData.level].height}p)`
        } else {
            autoQualityOption.innerHTML = `AUTO`
        }
    }

    /**
     * Handles errors in Hls.
     *
     * @param {ERROR} error - error
     * @param {ErrorData} errorData - error data
     */
    #handleHlsError(error, errorData) {
        if (errorData.fatal) {
            switch (errorData.type) {
                case Hls.ErrorTypes.NETWORK_ERROR:
                    console.log('Network error on Hls load, retrying...')

                    if (++this.#retryCount < 11) {
                        setTimeout(function () {
                            this.#checkVideo(this.#url)
                        }.bind(this), 500)

                        let currentLevel = this.#hls.currentLevel
                        this.#hls.loadLevel = -1
                        this.#hls.startLoad()

                        setTimeout(() => {
                            this.#hls.loadLevel = currentLevel
                        }, 1e3)
                    }
                    break
                case Hls.ErrorTypes.MEDIA_ERROR:
                    console.log('Media error, recovering...')
                    this.#hls.recoverMediaError()
                    this.#player.play()
                    break
            }
        } else if (errorData.details === Hls.ErrorDetails.FRAG_LOAD_ERROR) {
            if (typeof errorData.frag.relurl == 'string' && !this.#hls.autoLevelEnabled) {
                let currentLevel = this.#hls.currentLevel
                this.#hls.stopLoad()
                this.#hls.loadLevel = -1
                this.#hls.startLoad()

                setTimeout(() => {
                    this.#hls.loadLevel = currentLevel
                }, 1e3)
            }
        }
    }
}
