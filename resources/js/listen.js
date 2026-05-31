import MusicManager from './helpers/music'

window.addEventListener('musickitloaded', async function () {
    let developerToken = ''

    try {
        const response = await fetch('/musickit/token', { headers: { Accept: 'application/json' } })
        developerToken = (await response.json()).token ?? ''
    } catch (error) {
        // Leave MusicKit unconfigured
    }

    window.musicManager = new MusicManager({
        developerToken,
        app: {
            build: '1.17.0-alpha.1',
            icon: '/images/static/icon/app_icon.webp',
            name: 'Kurozora',
            version: '1.17.0-alpha.1'
        }
    })
    window.dispatchEvent(new Event('musicmanagerloaded'))
})
