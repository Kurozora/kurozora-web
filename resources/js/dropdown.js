import Position from './helpers/position'

document.addEventListener('alpine:init', () => {
    window.Alpine.data('dropdown', (autoPlacement = false) => ({
        open: false,
        autoPlacement,
        toggle() {
            this.open = !this.open

            if (this.open && this.autoPlacement) {
                this.$nextTick(() => Position.placeDropdown(this.$refs.panel, this.$refs.trigger))
            }
        },
    }))
})
