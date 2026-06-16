import Marquee from './marquee'
import {hidePrompt, showPrompt} from './prompt'

export default class MusicManager {
    // MARK: - Properties
    /**
     * The shared MusicKit instance.
     *
     * @type {MusicKit.MusicKitInstance|null}
     */
    #shared = null

    /**
     * The Apple Music id of the last toggled song.
     *
     * @type {string}
     */
    #currentMusicID = ''

    /**
     * Whether configuration has started.
     *
     * @type {boolean}
     */
    #hasStarted = false

    /**
     * Whether the playback events have been bound.
     *
     * @type {boolean}
     */
    #hasBoundPlayback = false

    /**
     * Catalog songs keyed by Apple Music id.
     *
     * @type {Map<string, MusicKit.Song>}
     */
    #songCache = new Map()

    /**
     * The now playing song's captured context.
     *
     * @type {{amID: string, songID: string, url: string, title: string, appleMusicURL: string, services: Object}|null}
     */
    #nowPlaying = null

    /**
     * The persistent player's marquee labels.
     *
     * @type {{title: Marquee, artist: Marquee}|null}
     */
    #playerMarquees = null

    /**
     * The local storage key recording that the connect prompt has been shown.
     *
     * @type {string}
     */
    #promptSeenKey = 'kurozora.music.connectPromptSeen'

    /**
     * The background shown behind the artwork placeholder.
     *
     * @type {string}
     */
    #placeholderColor = '#A660B2'

    // MARK: - Initializers
    constructor() {
        this.#bindInterface()

        if (window.MusicKit) {
            this.#start()
        } else {
            window.addEventListener('musickitloaded', () => this.#start(), { once: true })
        }
    }

    // MARK: - Public
    /**
     * The shared MusicKit instance.
     *
     * @returns {MusicKit.MusicKitInstance|null}
     */
    get shared() {
        return this.#shared
    }

    /**
     * Whether the listener has authorized Apple Music.
     *
     * @returns {boolean}
     */
    get isAuthorized() {
        return this.#shared?.isAuthorized ?? false
    }

    /**
     * Whether a song is currently playing.
     *
     * @returns {boolean}
     */
    get isPlaying() {
        return this.#shared?.playbackState === MusicKit.PlaybackStates.playing
    }

    /**
     * The now playing song's captured context.
     *
     * @returns {Object|null}
     */
    get nowPlaying() {
        return this.#nowPlaying
    }

    /**
     * Requests Apple Music authorization.
     *
     * @returns {Promise<string>}
     */
    async authorize() {
        return this.#shared.authorize()
    }

    /**
     * Revokes Apple Music authorization.
     *
     * @returns {Promise<void>}
     */
    async unauthorize() {
        return this.#shared.unauthorize()
    }

    /**
     * Plays, resumes or pauses the song with the given id.
     *
     * @param {MusicKit.Song|string} song - a song object or its Apple Music id
     *
     * @returns {Promise<void>}
     */
    async playSong(song) {
        const songID = typeof song === 'string' ? song : song?.id
        if (!songID || !this.#shared) {
            return
        }

        if (this.#currentMusicID === songID && this.isPlaying) {
            await this.#shared.pause()
        } else if (this.#currentMusicID === songID && this.#shared.nowPlayingItem) {
            await this.#shared.play()
        } else {
            await this.#shared.setQueue({ songs: [songID] })
            await this.#shared.play()
        }

        this.#currentMusicID = songID
    }

    /**
     * Resumes or pauses the now playing item.
     *
     * @returns {Promise<void>}
     */
    async togglePlayPause() {
        if (!this.#shared?.nowPlayingItem) {
            return
        }

        if (this.isPlaying) {
            await this.#shared.pause()
        } else {
            await this.#shared.play()
        }
    }

    /**
     * Sets playback to the given time in seconds.
     *
     * @param {number} seconds - the target time
     *
     * @returns {Promise<void>}
     */
    async seekTo(seconds) {
        return this.#shared.seekToTime(Number(seconds))
    }

    /**
     * Formats a millisecond duration as mm:ss.
     *
     * @param {number} milliseconds - the duration
     *
     * @returns {string}
     */
    getTime(milliseconds) {
        const value = !milliseconds || isNaN(milliseconds) ? 0 : milliseconds
        const date = new Date(1000 * Math.round(value / 1000))

        return `${String(`0${date.getUTCMinutes()}`).slice(-2)}:${String(`0${date.getUTCSeconds()}`).slice(-2)}`
    }

    /**
     * The artwork URL for a catalog song at the given size.
     *
     * @param {MusicKit.Song} song - the catalog song
     * @param {number} width - the width
     * @param {number} height - the height
     *
     * @returns {string}
     */
    getArtworkURL(song, width = 500, height = 500) {
        return MusicKit.formatArtworkURL(song.attributes.artwork, width, height)
    }

    /**
     * Fetches a catalog song with its library relationship.
     *
     * @param {string} id - the Apple Music id
     *
     * @returns {Promise<MusicKit.Song>}
     */
    async fetchSong(id) {
        const storefront = this.#shared.storefrontId ?? 'us'
        const response = await this.#shared.api.music(`v1/catalog/${storefront}/songs/${id}`, { include: ['library'] })

        return response.data.data[0]
    }

    /**
     * Adds the song with the given id to the listener's library.
     *
     * @param {string} id - the Apple Music id
     *
     * @returns {Promise<void>}
     */
    async addToLibrary(id) {
        return this.#shared.api.music(`v1/me/library?ids[songs]=${id}`, {}, { fetchOptions: { method: 'POST' } })
    }

    // MARK: - Lifecycle
    /**
     * Fetches the developer token, configures MusicKit and wires the surfaces.
     *
     * @returns {Promise<void>}
     */
    async #start() {
        if (this.#hasStarted) {
            return
        }
        this.#hasStarted = true

        let developerToken = ''
        let app = {}
        try {
            const response = await fetch('/musickit/token', { headers: { Accept: 'application/json' } })
            const configuration = await response.json()
            developerToken = configuration.token ?? ''
            app = configuration.app ?? {}
        } catch (error) {
        }

        this.#shared = await MusicKit.configure({ developerToken, app })

        this.#bindPlayback()
        this.#resync()

        window.dispatchEvent(new Event('musicmanagerloaded'))
    }

    /**
     * Binds the shared-instance events that drive the surfaces.
     */
    #bindPlayback() {
        if (this.#hasBoundPlayback) {
            return
        }
        this.#hasBoundPlayback = true

        this.#shared.addEventListener('playbackStateDidChange', () => this.#refreshPlayButtons())
        this.#shared.addEventListener('nowPlayingItemDidChange', () => {
            this.#refreshPlayButtons()
            this.#updateMiniPlayer()
        })
        this.#shared.addEventListener('playbackTimeDidChange', () => this.#updateProgress())
        this.#shared.addEventListener('authorizationStatusDidChange', () => this.#onAuthorizationChange())
    }

    /**
     * Binds the delegated interface controls.
     */
    #bindInterface() {
        document.addEventListener('click', (event) => this.#onClick(event))
        document.addEventListener('input', (event) => this.#onInput(event))
        document.addEventListener('livewire:navigated', () => this.#shared && this.#resync())
        document.addEventListener('livewire:init', () => Livewire.hook('morphed', () => this.#shared && this.#resync()))
    }

    /**
     * Re-applies the current state to every surface after a navigation or re-render.
     */
    #resync() {
        this.#refreshPlayButtons()
        this.#updatePreviewBadges()
        this.#updatePlaybackLabels()
        this.#updateConnectionState()
        document.querySelectorAll('[data-music-song]').forEach((root) => this.#updateServicesVisibility(root))
        this.#updateMiniPlayer()
        this.#hydrateSongs()
    }

    // MARK: - Surfaces
    /**
     * The Apple Music id of the now playing item.
     *
     * @returns {string}
     */
    #currentSongID() {
        return this.#shared?.nowPlayingItem?.id ?? ''
    }

    /**
     * Reflects the current playback state on every play button.
     */
    #refreshPlayButtons() {
        const playingID = this.isPlaying ? this.#currentSongID() : ''

        document.querySelectorAll('[data-music-song]').forEach((root) => {
            const isCurrent = root.dataset.amId === playingID
            root.querySelectorAll('[data-music-icon="play"]').forEach((icon) => icon.classList.toggle('hidden', isCurrent))
            root.querySelectorAll('[data-music-icon="pause"]').forEach((icon) => icon.classList.toggle('hidden', !isCurrent))
        })
    }

    /**
     * Updates the progress slider and remaining time of the playing song.
     */
    #updateProgress() {
        const songID = this.#currentSongID()
        if (!songID) {
            return
        }

        const elapsed = this.#shared.currentPlaybackTime ?? 0
        const remaining = this.#shared.currentPlaybackTimeRemaining ?? 0
        const duration = this.#shared.currentPlaybackDuration ?? 0

        document.querySelectorAll(`[data-music-song][data-am-id="${songID}"]`).forEach((root) => {
            root.querySelectorAll('[data-music-seek]').forEach((slider) => {
                if (duration) {
                    slider.max = Math.ceil(duration)
                }
                slider.value = elapsed
            })
            root.querySelectorAll('[data-music-duration]').forEach((label) => {
                label.textContent = this.getTime(remaining * 1000)
            })
        })
    }

    /**
     * Hides the "Preview" badge once the listener has connected Apple Music.
     */
    #updatePreviewBadges() {
        document.querySelectorAll('[data-music-preview-badge]').forEach((badge) => badge.classList.toggle('hidden', this.isAuthorized))
    }

    /**
     * Swaps preview wording for full-playback wording once connected.
     */
    #updatePlaybackLabels() {
        document.querySelectorAll('[data-music-when-preview]').forEach((label) => label.classList.toggle('hidden', this.isAuthorized))
        document.querySelectorAll('[data-music-when-authorized]').forEach((label) => label.classList.toggle('hidden', !this.isAuthorized))
    }

    /**
     * Toggles the connect and disconnect controls to match authorization.
     */
    #updateConnectionState() {
        document.querySelectorAll('[data-music-connected]').forEach((control) => control.classList.toggle('hidden', !this.isAuthorized))
        document.querySelectorAll('[data-music-disconnected]').forEach((control) => control.classList.toggle('hidden', this.isAuthorized))
    }

    /**
     * Toggles the Add / In Library entries; both stay hidden until connected.
     *
     * @param {HTMLElement} root - the song surface
     */
    #updateLibraryState(root) {
        const inLibrary = root.dataset.musicInLibrary === '1'
        root.querySelectorAll('[data-music-add]').forEach((entry) => entry.classList.toggle('hidden', !this.isAuthorized || inLibrary))
        root.querySelectorAll('[data-music-added]').forEach((entry) => entry.classList.toggle('hidden', !this.isAuthorized || !inLibrary))
    }

    /**
     * Shows the "View on" group only when the surface has at least one link.
     *
     * @param {HTMLElement} root - the song surface
     */
    #updateServicesVisibility(root) {
        let hasService = false
        root.querySelectorAll('[data-music-service]').forEach((link) => {
            const visible = link.getAttribute('href') && link.getAttribute('href') !== '#'
            link.classList.toggle('hidden', !visible)
            hasService = hasService || !!visible
        })
        root.querySelectorAll('[data-music-services]').forEach((group) => group.classList.toggle('hidden', !hasService))
    }

    /**
     * Reflects an authorization change across every dependent control.
     */
    #onAuthorizationChange() {
        this.#updatePreviewBadges()
        this.#updatePlaybackLabels()
        this.#updateConnectionState()
        document.querySelectorAll('[data-music-song]').forEach((root) => this.#updateLibraryState(root))
    }

    /**
     * Re-fetches detail surfaces so library membership reflects the new token.
     *
     * @returns {Promise<void>}
     */
    async #rehydrate() {
        this.#songCache.clear()
        document.querySelectorAll('[data-music-detail]').forEach((root) => root.removeAttribute('data-music-hydrated'))
        await this.#hydrateSongs()
    }

    /**
     * Enriches detail surfaces with catalog data once MusicKit is ready.
     *
     * @returns {Promise<void>}
     */
    async #hydrateSongs() {
        const roots = document.querySelectorAll('[data-music-detail]:not([data-music-hydrated])')

        for (const root of roots) {
            const songID = root.dataset.amId
            if (!songID) {
                continue
            }

            root.setAttribute('data-music-hydrated', '')

            if (this.#songCache.has(songID)) {
                this.#applySong(root, this.#songCache.get(songID))
                continue
            }

            try {
                const song = await this.fetchSong(songID)
                this.#songCache.set(songID, song)
                this.#applySong(root, song)
            } catch (error) {
                root.removeAttribute('data-music-hydrated')
            }
        }
    }

    /**
     * Applies catalog data to a hydrated surface.
     *
     * @param {HTMLElement} root - the song surface
     * @param {MusicKit.Song} song - the catalog song
     */
    #applySong(root, song) {
        const attributes = song.attributes ?? {}
        const artwork = attributes.artwork ?? {}

        if (root.hasAttribute('data-music-colors')) {
            const setColor = (name, value) => value && root.style.setProperty(name, `#${value}`)
            setColor('--am-bg', artwork.bgColor)
            setColor('--am-text-1', artwork.textColor1)
            setColor('--am-text-2', artwork.textColor2)
            setColor('--am-text-3', artwork.textColor3)
            setColor('--am-text-4', artwork.textColor4)
        }

        root.querySelectorAll('[data-music-artwork]').forEach((image) => {
            if (image.src.includes('/placeholders/')) {
                image.src = this.getArtworkURL(song, 500, 500)
                image.style.backgroundColor = artwork.bgColor ? `#${artwork.bgColor}` : this.#placeholderColor
            }
        })

        root.querySelectorAll('[data-music-name]').forEach((label) => {
            if (attributes.name) {
                label.textContent = attributes.name
            }
        })

        root.querySelectorAll('[data-music-album]').forEach((label) => label.textContent = attributes.albumName ?? '')
        root.querySelectorAll('[data-music-explicit]').forEach((badge) => badge.classList.toggle('hidden', attributes.contentRating !== 'explicit'))

        root.querySelectorAll('[data-music-service="appleMusic"]').forEach((link) => {
            if (attributes.url) {
                link.href = attributes.url
            }
        })

        root.querySelectorAll('[data-music-seek]').forEach((slider) => {
            if (attributes.durationInMillis) {
                slider.max = Math.ceil(attributes.durationInMillis / 1000)
            }
        })

        root.dataset.musicInLibrary = (song.relationships?.library?.data?.length ?? 0) > 0 ? '1' : ''
        this.#updateServicesVisibility(root)
        this.#updateLibraryState(root)
    }

    /**
     * Captures the now playing song's context from the given surface.
     *
     * @param {HTMLElement} root - the song surface playback started from
     */
    #captureNowPlaying(root) {
        if (!root) {
            return
        }

        const services = {}
        root.querySelectorAll('[data-music-service]').forEach((link) => {
            const href = link.getAttribute('href')
            if (link.dataset.musicService !== 'appleMusic' && href && href !== '#') {
                services[link.dataset.musicService] = link.href
            }
        })

        const cached = this.#songCache.get(root.dataset.amId)

        this.#nowPlaying = {
            amID: root.dataset.amId ?? '',
            songID: root.dataset.musicSongId ?? '',
            url: document.querySelector('link[rel="canonical"]')?.href ?? '',
            title: cached?.attributes?.name ?? root.querySelector('[data-music-name]')?.textContent?.trim() ?? '',
            appleMusicURL: cached?.attributes?.url ?? '',
            services
        }
    }

    /**
     * Reveals and populates the persistent player from the now playing item.
     */
    #updateMiniPlayer() {
        const player = document.querySelector('[data-music-player]')
        if (!player) {
            return
        }

        const item = this.#shared?.nowPlayingItem
        const isLoaded = !!item

        player.classList.toggle('translate-y-full', !isLoaded)
        player.classList.toggle('opacity-0', !isLoaded)
        player.classList.toggle('pointer-events-none', !isLoaded)

        if (!isLoaded) {
            return
        }

        const attributes = item.attributes ?? {}
        const artwork = item.artwork ?? attributes.artwork
        const title = item.title ?? attributes.name ?? ''
        const artist = item.artistName ?? attributes.artistName ?? ''

        if (!this.#playerMarquees) {
            this.#playerMarquees = {
                title: new Marquee(player.querySelector('[data-music-player-title]')),
                artist: new Marquee(player.querySelector('[data-music-player-artist]'))
            }
        }
        this.#playerMarquees.title.setText(title)
        this.#playerMarquees.artist.setText(artist)

        player.querySelectorAll('[data-music-player-artwork]').forEach((image) => {
            if (artwork) {
                image.src = MusicKit.formatArtworkURL(artwork, 192, 192)
            }
            image.style.backgroundColor = artwork?.bgColor ? `#${artwork.bgColor}` : this.#placeholderColor
        })

        player.dataset.amId = item.id ?? ''
        player.dataset.musicInLibrary = (this.#songCache.get(item.id)?.relationships?.library?.data?.length ?? 0) > 0 ? '1' : ''

        this.#populatePlayerMenu(player)
        this.#updateLibraryState(player)
        this.#refreshPlayButtons()
    }

    /**
     * Fills the player menu's links and copy values from the captured context.
     *
     * @param {HTMLElement} player - the persistent player
     */
    #populatePlayerMenu(player) {
        const context = this.#nowPlaying ?? {}
        const services = { appleMusic: context.appleMusicURL, ...(context.services ?? {}) }

        player.querySelectorAll('[data-music-service]').forEach((link) => {
            link.href = services[link.dataset.musicService] || '#'
        })
        player.querySelectorAll('[data-music-copy-title]').forEach((entry) => entry.dataset.copyValue = context.title ?? '')
        player.querySelectorAll('[data-music-copy-link]').forEach((entry) => entry.dataset.copyValue = context.url ?? '')

        this.#updateServicesVisibility(player)
    }

    // MARK: - Interaction
    /**
     * Handles the delegated click controls.
     *
     * @param {MouseEvent} event - the click event
     *
     * @returns {Promise<void>}
     */
    async #onClick(event) {
        const playButton = event.target.closest('[data-music-play]')
        if (playButton) {
            event.preventDefault()
            const root = playButton.closest('[data-music-song]')
            const songID = root?.dataset.amId

            if (songID) {
                this.#captureNowPlaying(root)
                await this.playSong(songID)
                this.#promptConnectOnce()
                this.#refreshPlayButtons()
                this.#updateMiniPlayer()
            }
            return
        }

        if (event.target.closest('[data-music-player-lyrics]')) {
            event.preventDefault()
            this.#openLyrics()
            return
        }

        if (event.target.closest('[data-music-share]')) {
            event.preventDefault()
            this.#share()
            return
        }

        if (event.target.closest('[data-music-goto]')) {
            event.preventDefault()
            const url = this.#nowPlaying?.url
            if (url) {
                window.Livewire?.navigate ? Livewire.navigate(url) : (window.location.href = url)
            }
            return
        }

        const addButton = event.target.closest('[data-music-add]')
        if (addButton) {
            event.preventDefault()
            const root = addButton.closest('[data-music-song]')
            const songID = root?.dataset.amId

            if (songID) {
                try {
                    await this.addToLibrary(songID)
                    root.dataset.musicInLibrary = '1'
                    this.#updateLibraryState(root)
                } catch (error) {
                }
            }
            return
        }

        if (event.target.closest('[data-music-added]')) {
            event.preventDefault()
            showPrompt('music-remove-prompt')
            return
        }

        const copyButton = event.target.closest('[data-music-copy]')
        if (copyButton) {
            event.preventDefault()
            this.#copyText(copyButton.dataset.copyValue)
            return
        }

        if (event.target.closest('[data-music-connect]')) {
            hidePrompt('music-connect-prompt')
            try {
                await this.authorize()
            } catch (error) {
                console.error('Apple Music authorization failed:', error)
            }
            this.#onAuthorizationChange()
            await this.#rehydrate()
            this.#refreshPlayButtons()
            return
        }

        if (event.target.closest('[data-music-disconnect]')) {
            try {
                await this.unauthorize()
            } catch (error) {
                console.error('Apple Music disconnect failed:', error)
            }
            this.#onAuthorizationChange()
            this.#refreshPlayButtons()
            return
        }

        const dismissTrigger = event.target.closest('[data-music-dismiss]')
        if (dismissTrigger) {
            const overlay = dismissTrigger.closest('[data-prompt]')
            if (overlay) {
                hidePrompt(overlay.id)
            }
        }
    }

    /**
     * Handles the delegated seek slider.
     *
     * @param {InputEvent} event - the input event
     */
    #onInput(event) {
        const slider = event.target.closest('[data-music-seek]')
        if (slider) {
            this.seekTo(slider.value)
        }
    }

    /**
     * Opens the lyrics overlay for the now playing song.
     */
    #openLyrics() {
        const id = document.querySelector('[data-lyrics-root]')?.getAttribute('wire:id')
        if (id && this.#nowPlaying?.songID) {
            window.Livewire?.find(id)?.call('open', Number(this.#nowPlaying.songID))
        }
    }

    /**
     * Shares the now playing song, falling back to copying its link.
     */
    #share() {
        const url = this.#nowPlaying?.url || window.location.href
        const title = this.#nowPlaying?.title || document.title

        if (navigator.share) {
            navigator.share({ title, url }).catch(() => {})
        } else {
            this.#copyText(url)
        }
    }

    /**
     * Shows the connect prompt once per visitor, the first time a preview plays.
     */
    #promptConnectOnce() {
        if (this.isAuthorized || localStorage.getItem(this.#promptSeenKey)) {
            return
        }

        if (!document.getElementById('music-connect-prompt')) {
            return
        }

        localStorage.setItem(this.#promptSeenKey, '1')
        showPrompt('music-connect-prompt')
    }

    /**
     * Copies text to the clipboard, falling back when the async API is unavailable.
     *
     * @param {string} value - the text to copy
     */
    #copyText(value) {
        if (!value) {
            return
        }

        if (navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(value).catch(() => this.#fallbackCopy(value))
        } else {
            this.#fallbackCopy(value)
        }
    }

    /**
     * Copies text using a temporary selection for non-secure contexts.
     *
     * @param {string} value - the text to copy
     */
    #fallbackCopy(value) {
        const textarea = document.createElement('textarea')
        textarea.value = value
        textarea.setAttribute('readonly', '')
        textarea.style.position = 'fixed'
        textarea.style.opacity = '0'
        document.body.appendChild(textarea)
        textarea.select()

        try {
            document.execCommand('copy')
        } catch (error) {
        }

        document.body.removeChild(textarea)
    }
}
