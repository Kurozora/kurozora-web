if ('serviceWorker' in navigator) {
    const mixManifest = require('/mix-manifest.json')

    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register(mixManifest['/service-worker.js'])
            .then(registration => {
                console.log('Service worker registered:', registration)
            })
            .catch(error => {
                console.error('Error registering service worker:', error)
            })
    })
}
