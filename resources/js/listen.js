import MusicManager from './helpers/music'

window.addEventListener('musickitloaded', function ($event) {
    window.musicManager = new MusicManager({
        developerToken: process.env.MIX_APPLE_CLIENT_SECRET,
        app: {
            build: '1.17.0-alpha.1',
            icon: '/images/static/icon/app_icon.webp',
            name: 'Kurozora',
            version: '1.17.0-alpha.1'
        }
    })
    window.dispatchEvent(new Event('musicmanagerloaded'))
})
