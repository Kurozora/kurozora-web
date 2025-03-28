import ThemeManager from './settings/ThemeManager.js'
import AppIconManager from './settings/AppIconManager.js'

export default class KSettings {
    #localStorageKey
    #themeManager
    #appIconManager
    #settingsData

    selectedAppIcon

    constructor() {
        this.#localStorageKey = 'settings'
        this.#themeManager = new ThemeManager()
        this.#appIconManager = new AppIconManager()
        this.#settingsData = this.#loadSettings()

        // Listen for relevant events
        document.addEventListener('livewire:init', () => {
            Livewire.on('app-icon-changed', (event) => {
                this.#applyAppIcon(event.appIcon)
                this.save(this.#appIconManager.localStorageKey, event.appIcon)
            });

            Livewire.on('theme-download', (event) => {
                this.#applyTheme(event.theme)
                this.save(this.#themeManager.localStorageKey, event.theme)
            });
        });

        // Apply stored settings
        if (this.#settingsData.theme) {
            this.#applyTheme(this.#settingsData.theme)
        }
        if (this.#settingsData.appIcon) {
            this.#applyAppIcon(this.#settingsData.appIcon)
        }
    }

    // MARK: - Encryption/Decryption
    #encryptData(data) {
        return btoa(JSON.stringify(data))
    }

    #decryptData(data) {
        try {
            return JSON.parse(atob(data))
        } catch {
            return {}
        }
    }

    // MARK: - Storage
    #defaultSettingsData() {
        let defaultSettings = {}
        defaultSettings[this.#appIconManager.localStorageKey] = {
            name: 'Kurozora',
            url: null
        }
        return defaultSettings
    }

    #loadSettings() {
        const storedSettings = localStorage.getItem(this.#localStorageKey)
        return storedSettings ? this.#decryptData(storedSettings) : this.#defaultSettingsData()
    }

    save(key, value) {
        this.#settingsData[key] = value
        localStorage.setItem(this.#localStorageKey, this.#encryptData(this.#settingsData))
    }

    // MARK: - Apply Settings
    #applyTheme(theme) {
        this.#themeManager.apply(theme)
    }

    #applyAppIcon(appIcon) {
        this.selectedAppIcon = appIcon
        this.#appIconManager.apply(appIcon)
    }
}

window.settings = new KSettings()
