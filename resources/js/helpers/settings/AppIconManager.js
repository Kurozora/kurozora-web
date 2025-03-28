export default class AppIconManager {
    localStorageKey = 'appIcon'

    apply(appIcon) {
        let appIconElements = document.querySelectorAll('#app-icon')
        appIconElements.forEach(function (appIconElement) {
            appIconElement.style.backgroundImage = `url('${appIcon.url}')`
        })

        let faviconElement = document.querySelector('[rel="shortcut icon"]')
        faviconElement.href = appIcon.url
    }
}
