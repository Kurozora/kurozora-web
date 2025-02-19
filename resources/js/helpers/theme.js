export default class KThemeStyle {
    // MARK: - Initializers
    constructor() {
        this.localStorageKey = 'theme'
        this.encryptionKey = 'your-strong-encryption-key'
        this.themeData = null

        document.addEventListener('livewire:init', () => {
            Livewire.on('theme-download', (event) => {
                this.applyTheme(event.theme)
                this.saveTheme(event.theme)
            });
        });

        this.loadTheme()
    }

    // MARK: - Function
    encryptTheme(theme) {
        const themeString = JSON.stringify(theme)
        return btoa(themeString)
    }

    decryptTheme(encryptedTheme) {
        try {
            const decoded = atob(encryptedTheme)
            return JSON.parse(decoded)
        } catch {
            return null
        }
    }

    saveTheme(theme) {
        const encryptedTheme = this.encryptTheme(theme)
        localStorage.setItem(this.localStorageKey, encryptedTheme)
    }

    loadTheme() {
        const storedTheme = localStorage.getItem(this.localStorageKey)
        if (storedTheme) {
            this.themeData = this.decryptTheme(storedTheme)

            if (this.themeData) {
                this.applyTheme(this.themeData)
            }
        }
    }

    applyTheme(theme) {
        let styleTag = document.getElementById('kurozora-theme-style')

        if (!styleTag) {
            styleTag = document.createElement('style')
            styleTag.id = 'kurozora-theme-style'
            document.head.appendChild(styleTag)
        }

        styleTag.innerHTML = theme.css
    }

    clearTheme() {
        localStorage.removeItem(this.localStorageKey)
        const styleTag = document.getElementById('kurozora-theme-style')

        if (styleTag) {
            styleTag.remove()
        }
    }
}

window.themeStyle = new KThemeStyle()
