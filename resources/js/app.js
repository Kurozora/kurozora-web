import './echo'
import './bootstrap'
import './section-refresh'
import './pagination'
import './alert'
import './subscription-sheet'
import './nav-notification'
import './navigation'
import './search'
import './minigames/kotodama'

document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ options }) => {
        const socketId = options.headers?.['X-Socket-ID']

        if (!socketId || socketId === 'undefined' || socketId === 'null') {
            delete options.headers['X-Socket-ID']
        }
    })

    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status === 419) {
                location.reload()
                preventDefault()
            }
        })
    })
})
