export default class MusicManager {
    // MARK: - Properties
    /**
     * The shared MusicKit instance.
     *
     * @type {MusicKit} shared
     */
    shared = null

    /**
     * Whether a music is playing.
     *
     * @type {boolean} isPlaying
     */
    isPlaying = false

    /**
     * Current music id.
     *
     * @type {string} currentMusicID
     */
    currentMusicID = ''

    /**
     * The audio player's current progress.
     *
     * @type {number} progress
     */
    progress = 0

    /**
     * The current playback duration.
     *
     * @type {string} currentPlaybackDuration
     */
    currentPlaybackDuration = "00:30"

    /**
     * The options used to initialize MusicKit.
     *
     * @type {{app: {build: string, icon: string, name: string, version: string}, developerToken: string}} options
     */
    options = {
        developerToken: '',
        app: {
            build: '',
            icon: '',
            name: '',
            version: ''
        }
    }

    /**
     * Whether the events have been initialized.
     *
     * @type {boolean} hasInitEvents
     */
    hasInitEvents = false

    // MARK: - Initializers
    /**
     * Create a new instance of Kurozora Player.
     *
     * @constructor
     *
     * @param {{app: {build: string, icon: string, name: string, version: string}, developerToken: string}} options - options
     */
    constructor(options) {
        MusicKit.configure(options)
        this.shared = MusicKit.getInstance()
    }

    // MARK: - Functions
    /**
     * Bind events to the audio player.
     */
    initEvents() {
        if (this.hasInitEvents) {
            return
        }
        this.hasInitEvents = true

        let audioElement = this.shared.player._currentPlayer.audio

        audioElement.addEventListener('play', (event) => {
            this.progress = audioElement.currentTime
        })
        audioElement.addEventListener('timeupdate', (event) => {
            this.progress = audioElement.currentTime
            this.currentPlaybackDuration = this.getTime(this.shared.player.currentPlaybackTimeRemaining * 1000)
        })
        audioElement.addEventListener('ended', (event) => {
            this.isPlaying = false
        })
    }

    /**
     * Create a media item from the given song object.
     *
     * @param {MusicKit.Song} song - a MusicKit song object.
     *
     * @returns {*&{container: {id}}}
     */
    createMediaItem(song) {
        return {
            ...song,
            container: {
                id: song.id
            }
        }
    }

    /**
     * Returns the url of the artwork for the given song.
     *
     * @param {MusicKit.Song} song - the song object
     * @param {number} width - the width of the artwork
     * @param {number} height - the height of the artwork
     *
     * @returns {string}
     */
    getArtworkURL(song, width = 500, height = 500) {
        return MusicKit.formatArtworkURL(song.attributes.artwork, width, height)
    }

    /**
     * Get time from milliseconds.
     *
     * @param {number} milliseconds
     *
     * @returns {string}
     */
    getTime(milliseconds) {
        if (!milliseconds || isNaN(milliseconds)) {
            milliseconds = 0
        }

        const seconds = 1000 * Math.round(milliseconds / 1000)
        const date = new Date(seconds)

        return `${String(`0${date.getUTCMinutes()}`).slice(-2)}:${String(`0${date.getUTCSeconds()}`).slice(-2)}`
    }

    /**
     * Set audio player's current time to the given value.
     *
     * @param {number} value
     */
    seekTo(value) {
        this.shared.player.seekToTime(value)
    }

    /**
     * Create a queue with the given items.
     *
     * @param {MusicKit.Song[]} items - an array of songs.
     * @returns {Promise<void>}
     */
    async setQueueItems(items) {
        const filteredItems = items.filter(item => item)

        if (filteredItems.length === 0) {
            return
        }

        await this.shared.setQueue({
            items: filteredItems.map(item => this.createMediaItem(item))
        })
    }

    /**
     * Toggle song.
     *
     * @param song
     * @returns {Promise<void>}
     */
    async playSong(song) {
        if (!!song) {
            if (this.currentMusicID === song.id && this.shared.player.isPlaying) {
                this.shared.player.pause()
                this.isPlaying = false
            } else if (this.currentMusicID === song.id) {
                this.shared.player.play()
                this.isPlaying = true
            } else {
                await this.setQueueItems([song]).then(() => {
                    this.shared.player.play()
                })
                this.isPlaying = true
                this.initEvents()
            }

            this.currentMusicID = song.id
        }
    }

    /**
     * Fetch song with the given ID.
     *
     * @param {string} id - id of the song
     *
     * @returns {Promise<{songName, albumName, isExplicit: *, artworkURL: *, artistName, artwork: *, songUrl: *}>}
     */
    async fetchSong(id) {
        return await this.shared.api.song(id)
    }
}
