import controls from '../utilities/controls'

export default class Amplify {
    /**
     * The Plyr instance.
     *
     * @param {Plyr} player - player
     */
    #player

    /**
     * The Storage instance.
     *
     * @param {Storage} storage - storage
     */
    #storage

    /**
     * The audio context responsible for audio amplification.
     *
     * @param {{getAmpLevel: (function(): number), context: *, source: MediaElementAudioSourceNode, media, amplify: (function(multiplier: number)), gain: GainNode}} audioContext - audio context
     */
    #audioContext

    /**
     * The minimum amplification value.
     *
     * @type {number}
     */
    #minimumAmplification = -2

    /**
     * The maximum amplification value.
     *
     * @type {number}
     */
    #maximumAmplification = 3.0

    /**
     * Create a new instance of Kurozora Player.
     *
     * @constructor
     *
     * @param {Plyr} player - player
     * @param {Storage} storage - storage
     */
    constructor(player, storage) {
        this.#player = player
        this.#storage = storage

        const { config } = this.#player

        if (config.amplify === undefined) {
            // Amplify default and options to display
            this.#player.config.amplify = {
                selected: 1.0,
                options: [0, 0.5, 0.75, 1.0, 1.25, 1.5, 1.75, 2.0, 2.5, 3.0, 4.0],
            }
        } else {
            this.#player.config.amplify.options = config.amplify.options.filter((option) => option >= this.#minimumAmplification && option <= this.#maximumAmplification)
        }

        this.#configureAudioContext()
        this.#setupAmplify()
    }

    /**
     * Configure the amplify list in the player.
     */
    #setupAmplify() {
        const { config } = this.#player
        let settingID = document.querySelector('.plyr__menu__container').getAttribute('id').replace('plyr-settings-', '')
        let amplifyMenu = 'plyr-settings-' + settingID + '-amplify'
        let amplifyButtons = ''
        let amplifyDefault = ''
        let amplifySelected = this.#storage.get('amplification') || config.amplify.selected
        let amplifyChecked = 'false'
        let homeMenu = 'plyr-settings-' + settingID + '-home'
        let homeSetting = document.querySelector('#' + homeMenu)

        // Create button for each amplify track.
        config.amplify.options.forEach(function (value, index) {
            let buttonTitle = value === 1 ? config.i18n.normal : value + '×'

            if (value === amplifySelected || (value.default && !config.amplify.options.find((value) => value === amplifySelected))) {
                amplifyDefault = buttonTitle
                amplifyChecked = 'true'
                this.#audioContext.amplify(value)
            } else {
                amplifyChecked = 'false'
            }

            amplifyButtons += '<button data-plyr="amplify" type="button" role="menuitemradio" class="plyr__control" aria-checked="' + amplifyChecked + '" value="' + index + '"><span>' + buttonTitle + '</span></button>'
        }.bind(this))

        // Add amplify menu button
        const menuButton = document.createElement('button')
        menuButton.setAttribute('data-plyr', 'amplify-settings')
        menuButton.setAttribute('type', 'button')
        menuButton.setAttribute('class', 'plyr__control plyr__control--forward')
        menuButton.setAttribute('aria-haspopup', 'true')
        menuButton.innerHTML = '<span>' + config.i18n.amplify + '<span class="plyr__menu__value">' + amplifyDefault + '</span></span>'
        homeSetting.querySelector('div[role=menu]').prepend(menuButton)

        // Add amplify menu options
        homeSetting.insertAdjacentHTML('afterend', '<div id="' + amplifyMenu + '" hidden><button type="button" class="plyr__control plyr__control--back"><span aria-hidden="true">' + config.i18n.amplify + '</span><span class="plyr__sr-only">' + config.i18n.menuBack + '</span></button><div role="menu">' + amplifyButtons + '</div></div>')

        // Make amplify menu button clickable
        document.querySelector('button[data-plyr="amplify-settings"]').addEventListener('click', function () {
            controls.showMenuPanel('#' + amplifyMenu, true)
        })

        // Make amplify menu back button clickable
        document.querySelector('#' + amplifyMenu + ' .plyr__control--back').addEventListener('click', function () {
            controls.showMenuPanel('#' + homeMenu, true)
        })

        // Make amplify menu options clickable
        document.querySelectorAll('button[data-plyr="amplify"]')
            .forEach(function (amplifyButton) {
                amplifyButton.addEventListener('click', function () {
                    let amplifyValue = config.amplify.options[amplifyButton.value]
                    // Apply settings
                    this.#audioContext.amplify(amplifyValue)

                    // Save amplify settings
                    config.amplify.selected = amplifyValue
                    this.#storage.set({
                        'amplification': amplifyValue
                    })

                    // Update amplify option selection
                    document.querySelectorAll('button[data-plyr="amplify"]').forEach(function (button) {
                        button.setAttribute('aria-checked', 'false')
                    })
                    amplifyButton.setAttribute('aria-checked', 'true')

                    // Update main menu amplify label
                    let amplifySettings = document.querySelector('button[data-plyr="amplify-settings"]').querySelector('.plyr__menu__value')
                    amplifySettings.textContent = config.amplify.selected === 1 ? config.i18n.normal : amplifyButton.textContent.trim()

                    // Send back to main menu
                    controls.showMenuPanel('#' + homeMenu, true)
                }.bind(this))
            }.bind(this))
    }

    /**
     * Configure the audio context responsible for amplifying the audio.
     */
    #configureAudioContext() {
        let context = new (window.AudioContext || window.webkitAudioContext)
        let audioContext = {
            context: context,
            source: context.createMediaElementSource(this.#player.media),
            gain: context.createGain(),
            media: this.#player.media,
            amplify: function(multiplier) {
                audioContext.gain.gain.value = multiplier
            },
            getAmpLevel: function() {
                return audioContext.gain.gain.value
            }
        }
        audioContext.source.connect(audioContext.gain)
        audioContext.gain.connect(context.destination)

        this.#audioContext = audioContext
    }
}
