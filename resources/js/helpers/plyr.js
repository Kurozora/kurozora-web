/**
 * Plyr is a simple, lightweight, accessible and customizable HTML5, YouTube
 * and Vimeo media player that supports modern browsers.
 */
import Plyr from 'plyr'
import Screenshot from './plyr/screenshot'
import Storage from './plyr/storage'
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
     * List of available YouTube urls.
     *
     * @type {string[]}
     */
    #youtubeUrls = [
        'youtube.com',
        'youtu.be',
        'youtube-nocookie.com',
    ]

    /**
     * Whether the video is Mp4.
     *
     * @param {boolean} isMp4 - is Mp4
     */
    #isMp4 = false

    /**
     * Whether the video is Hls.
     *
     * @param {boolean} isHls - is Hls
     */
    #isHls = false

    /**
     * Whether the video is YouTube.
     *
     * @param {boolean} isYouTube - is YouTube
     */
    #isYouTube = false

    /**
     * Whether the video is iframe.
     *
     * @param {boolean} isIframe - is iframe
     */
    #isIframe = false

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

        // Determine video type
        this.#isMp4 = this.#url.includes('.mp4')
        this.#isHls = this.#url.includes('.m3u8')
        this.#isYouTube = this.#youtubeUrls.some(youtubeUrl => this.#url.includes(youtubeUrl))
        this.#isIframe = this.#videoElement.querySelector('iframe') != null

        // Merge options.
        this.playerOptions = {
            ...this.playerOptions,
            ...options,
        }

        // For more options see: https://github.com/sampotts/plyr/#options
        if (this.#isYouTube || this.#isIframe) {
            this.#videoElement.querySelector('iframe').setAttribute('src', this.#url)

            if (this.#isYouTube) {
                this.playerOptions.controls = this.playerOptions.controls.filter(control => {
                    return control !== 'screenshot'
                })
            }

            this.#player = new Plyr(videoElement, this.playerOptions)
            this.#setupPlayer()
        } else if (this.#isHls) {
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
        } else if (this.#isMp4) {
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

            // Configure audio settings
            if (this.#isHls) {
                this.#configureAudio()
            }

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

                    if (!!(focused && Element && focused instanceof Element)) {
                        function matches(element, selector) {
                            const {
                                prototype
                            } = Element

                            function match() {
                                return Array.from(document.querySelectorAll(selector)).includes(this)
                            }

                            const method = prototype.matches || prototype.webkitMatchesSelector || prototype.mozMatchesSelector || prototype.msMatchesSelector || match
                            return method.call(element, selector)
                        }

                        const {
                            editable
                        } = this.#player.config.selectors

                        if (matches(focused, editable)) {
                            return
                        }

                        if (event.key === 'Space' && matches(focused, 'button, [role^="menuitem"]')) {
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
            if (this.#isHls) {
                setTimeout(() => this.#hls.subtitleTrack = this.#player.currentTrack, 50)
            }
        })

        // Keep track of time updates
        this.#player.on('timeupdate', (currentTime) => {
            localStorage.setItem('_x_progress' + window.location.pathname.replaceAll('/', '_'), this.#player.currentTime)
        })
    }

    /**
     * Configure the audio list in the player.
     */
    #configureAudio() {
        let audioTracks = this.#hls.audioTrackController.audioTracks.filter((value, index, self) =>
            index === self.findIndex((t) => (
                t.name === value.name
            ))
        )
        let settingID = document.querySelector('.plyr__menu__container').getAttribute('id').replace('plyr-settings-', '')
        let audioMenu = 'plyr-settings-' + settingID + '-audio'
        let audioButtons = ''
        let audioDefault = ''
        let audioSelected = this.#storage.get('audio_language')
        let audioChecked = 'false'
        let homeSetting = document.querySelector('#plyr-settings-' + settingID + '-home')

        if (audioTracks.length > 1) {
            // Create button for each audio track.
            audioTracks.forEach(function (value, index) {
                if (value.lang === audioSelected || (value.default && !this.#hls.audioTrackController.audioTracks.find((track) => track.lang === audioSelected))) {
                    audioDefault = value.name
                    audioChecked = 'true'
                    this.#hls.audioTrack = value.id
                } else {
                    audioChecked = 'false'
                }

                audioButtons += '<button data-plyr="audio" type="button" role="menuitemradio" class="plyr__control" aria-checked="' + audioChecked + '" value="' + value.id + '" lang="' + value.lang + '"><span>' + value.name + '<span class="plyr__menu__value"><span class="plyr__badge">' + (typeof value.lang !== 'undefined' ? value.lang.toUpperCase() : '') + '</span></span></span></button>'
            }.bind(this))

            // Add audio menu button
            const menuButton = document.createElement('button')
            menuButton.setAttribute('data-plyr', 'audio-settings')
            menuButton.setAttribute('type', 'button')
            menuButton.setAttribute('class', 'plyr__control plyr__control--forward')
            menuButton.setAttribute('aria-haspopup', 'true')
            menuButton.innerHTML = '<span>Audio<span class="plyr__menu__value">' + audioDefault + '</span></span>'
            homeSetting.querySelector('div[role=menu]').prepend(menuButton)

            // Add audio menu options
            homeSetting.insertAdjacentHTML('afterend', '<div id="' + audioMenu + '" hidden><button type="button" class="plyr__control plyr__control--back"><span aria-hidden="true">Audio</span><span class="plyr__sr-only">Go back to previous menu</span></button><div role="menu">' + audioButtons + '</div></div>')

            // Make audio menu button clickable
            document.querySelector('button[data-plyr="audio-settings"]').addEventListener('click', function () {
                document.querySelector('#' + audioMenu).hidden = false
                homeSetting.hidden = true
            })

            // Make audio menu back button clickable
            document.querySelector('#' + audioMenu + ' .plyr__control--back').addEventListener('click', function () {
                document.querySelector('#' + audioMenu).hidden = true
                homeSetting.hidden = false
            })

            // Make audio menu options clickable
            document.querySelectorAll('button[data-plyr="audio"]')
                .forEach(function (audioButton) {
                    audioButton.addEventListener('click', function () {
                        // Set Hls audio track
                        this.#hls.audioTrack = audioButton.value
                        this.#storage.set({
                            'audio_language': audioButton.getAttribute('lang')
                        })

                        // Update audio option selection
                        document.querySelectorAll('button[data-plyr="audio"]').forEach(function (button) {
                            button.setAttribute('aria-checked', 'false')
                        })
                        audioButton.setAttribute('aria-checked', 'true')

                        // Update main menu audio label
                        let audioSettings = document.querySelector('button[data-plyr="audio-settings"]').querySelector('.plyr__menu__value')
                        audioSettings.textContent = audioButton.textContent.replace(audioButton.querySelector('.plyr__badge').textContent, '').trim()

                        // Send back to main menu
                        document.querySelector('#' + audioMenu).hidden = true
                        homeSetting.hidden = false
                    }.bind(this))
            }.bind(this))
        }
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
