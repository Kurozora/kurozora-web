require('./bootstrap')

document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status === 419) {
                location.reload()
                preventDefault()
            }
        })
    })
})
