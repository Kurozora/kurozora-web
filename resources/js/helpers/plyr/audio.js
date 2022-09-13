import controls from '../utilities/controls'

export default class Audio {
    /**
     * The Plyr instance.
     *
     * @param {Plyr} player - player
     */
    #player

    /**
     * The Hls instance.
     *
     * @param {Hls} hls - hls
     */
    #hls

    /**
     * The Storage instance.
     *
     * @param {Storage} storage - storage
     */
    #storage

    /**
     * Create a new instance of Kurozora Player.
     *
     * @constructor
     *
     * @param {Plyr} player - player
     * @param {Hls} hls - hls
     * @param {Storage} storage - storage
     */
    constructor(player, hls, storage) {
        this.#player = player
        this.#hls = hls
        this.#storage = storage

        this.#setupAudio()
    }

    /**
     * Configure the audio list in the player.
     */
    #setupAudio() {
        const { config } = this.#player
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
        let homeMenu = 'plyr-settings-' + settingID + '-home'
        let homeSetting = document.querySelector('#' + homeMenu)

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
            menuButton.innerHTML = '<span>' + config.i18n.audio + '<span class="plyr__menu__value">' + audioDefault + '</span></span>'
            homeSetting.querySelector('div[role=menu]').prepend(menuButton)

            // Add audio menu options
            homeSetting.insertAdjacentHTML('afterend', '<div id="' + audioMenu + '" hidden><button type="button" class="plyr__control plyr__control--back"><span aria-hidden="true">' + config.i18n.audio + '</span><span class="plyr__sr-only">' + config.i18n.menuBack + '</span></button><div role="menu">' + audioButtons + '</div></div>')

            // Make audio menu button clickable
            document.querySelector('button[data-plyr="audio-settings"]').addEventListener('click', function () {
                controls.showMenuPanel('#' + audioMenu, true)
            })

            // Make audio menu back button clickable
            document.querySelector('#' + audioMenu + ' .plyr__control--back').addEventListener('click', function () {
                controls.showMenuPanel('#' + homeMenu, true)
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
                        controls.showMenuPanel('#' + homeMenu, true)
                    }.bind(this))
                }.bind(this))
        }
    }
}
