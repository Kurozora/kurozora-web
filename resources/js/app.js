import './echo'
import './bootstrap'

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
