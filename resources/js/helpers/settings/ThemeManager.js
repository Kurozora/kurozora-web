export default class ThemeManager {
    localStorageKey = 'theme'

    apply(theme) {
        let styleTag = document.getElementById('kurozora-theme-style')
        let themeHeader = document.querySelector('[name="theme-color"]')
        let lightThemeHeader = document.querySelector('[media*="prefers-color-scheme: light"]')
        let darkThemeHeader = document.querySelector('[media*="prefers-color-scheme: dark"]')

        if (!styleTag) {
            styleTag = document.createElement('style')
            styleTag.id = 'kurozora-theme-style'
            document.head.appendChild(styleTag)
        }

        styleTag.innerHTML = theme.css

        let backgroundColor = window.getComputedStyle(document.body).getPropertyValue('--bg-primary-color');
        themeHeader.content = backgroundColor
        lightThemeHeader.content = backgroundColor
        darkThemeHeader.content = backgroundColor
    }
}
