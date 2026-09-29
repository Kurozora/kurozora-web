document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine

    Alpine.data('libraryButton', ({ type, id, slug, kind, status, authenticated }) => ({
        status: String(status),
        busy: false,

        init() {
            if (!authenticated) {
                this.readLocal()
            }
        },

        localStore(mode, callback) {
            if (!window.libraryDB) {
                window.addEventListener('librarydbloaded', () => this.localStore(mode, callback), { once: true })
                return
            }

            callback(window.libraryDB.transaction(['libraryData'], mode).objectStore('libraryData'))
        },

        readLocal() {
            this.localStore('readonly', (store) => {
                const request = store.get(kind + ',' + slug)

                request.onsuccess = () => {
                    if (request.result) {
                        this.status = String(request.result.libraryCategory)
                    }
                }
                request.onerror = () => console.error('Error fetching entry from library database:', request.error)
            })
        },

        writeLocal() {
            this.localStore('readwrite', (store) => {
                const entryId = kind + ',' + slug
                const request = Number(this.status) < 0
                    ? store.delete(entryId)
                    : store.put({
                        id: entryId,
                        slug,
                        libraryKind: kind,
                        libraryCategory: this.status,
                        creationDate: Math.floor(Date.now() / 1000),
                    })

                request.onerror = () => console.error('Error updating entry in library database:', request.error)
            })
        },

        update() {
            const status = Number(this.status)

            if (!authenticated) {
                if (status < 0) {
                    this.status = '-1'
                }

                this.writeLocal()
                return
            }

            this.busy = true
            window.Livewire.dispatch('library-update', { type, id, status })
        },

        sync({ type: updatedType, id: updatedId, status }) {
            if (updatedType !== type || updatedId !== id) {
                return
            }

            this.status = String(status)
            this.busy = false
        },
    }))

    Alpine.data('episodeWatchButton', ({ id, watched, authenticated, signInUrl }) => ({
        watched,
        busy: false,

        toggle() {
            if (!authenticated) {
                window.Livewire.navigate(signInUrl)
                return
            }

            this.busy = true
            window.Livewire.dispatch('episode-watch', { id })
        },

        sync({ id: updatedId, watched }) {
            if (updatedId !== id) {
                return
            }

            this.watched = watched
            this.busy = false
        },
    }))

    Alpine.data('seasonWatchButton', ({ id, watched, authenticated, signInUrl }) => ({
        watched,
        busy: false,

        toggle() {
            if (!authenticated) {
                window.Livewire.navigate(signInUrl)
                return
            }

            this.busy = true
            window.Livewire.dispatch('season-watch', { id })
        },

        sync({ id: updatedId, watched }) {
            if (updatedId !== id) {
                return
            }

            this.watched = watched
            this.busy = false
        },
    }))

    Alpine.data('reminderButton', ({ type, id, reminded, authenticated, signUpUrl }) => ({
        reminded,
        busy: false,

        remind() {
            if (this.reminded) {
                return
            }

            if (!authenticated) {
                window.Livewire.navigate(signUpUrl)
                return
            }

            this.busy = true
            window.Livewire.dispatch('title-remind', { type, id })
        },

        sync({ type: updatedType, id: updatedId, reminded }) {
            if (updatedType !== type || updatedId !== id) {
                return
            }

            this.reminded = reminded
            this.busy = false
        },
    }))

    Alpine.data('titleActions', ({ type, id, tracking, favorited, reminded }) => ({
        tracking,
        favorited,
        reminded,
        busy: false,

        favorite() {
            this.busy = true
            window.Livewire.dispatch('title-favorite', { type, id })
        },

        remind() {
            this.busy = true
            window.Livewire.dispatch('title-remind', { type, id })
        },

        syncLibrary({ type: updatedType, id: updatedId, status }) {
            if (updatedType !== type || updatedId !== id) {
                return
            }

            this.tracking = Number(status) >= 0

            if (!this.tracking) {
                this.favorited = false
                this.reminded = false
            }
        },

        syncFavorite({ type: updatedType, id: updatedId, favorited }) {
            if (updatedType !== type || updatedId !== id) {
                return
            }

            this.favorited = favorited
            this.busy = false
        },

        syncReminder({ type: updatedType, id: updatedId, reminded }) {
            if (updatedType !== type || updatedId !== id) {
                return
            }

            this.reminded = reminded
            this.busy = false
        },
    }))

    Alpine.data('addToLibraryPrompt', ({ type, id, status }) => ({
        busy: false,

        add() {
            this.busy = true
            window.Livewire.dispatch('library-update', { type, id, status })
        },

        dismiss() {
            const url = new URL(window.location.href)

            url.searchParams.delete('add_to_library')
            window.history.replaceState(window.history.state, '', url)

            this.$dispatch('close')
        },

        sync({ type: updatedType, id: updatedId }) {
            if (updatedType !== type || updatedId !== id) {
                return
            }

            this.busy = false
            this.dismiss()
        },
    }))

    Alpine.data('profileActions', ({ id, blocked, followersCount }) => ({
        blocked,
        followersCount,
        showBlockedPosts: false,
        busy: false,

        toggleBlock() {
            this.busy = true
            window.Livewire.dispatch('user-block', { id })
        },

        syncBlock({ id: updatedId, blocked }) {
            if (updatedId !== id) {
                return
            }

            this.blocked = blocked
            this.showBlockedPosts = false
            this.busy = false
            this.$dispatch('close-modal', { id: 'block-user' })
        },

        syncFollowers({ userID, followersCount }) {
            if (userID !== id) {
                return
            }

            this.followersCount += followersCount
        },

        get followersLabel() {
            if (this.followersCount === 0) {
                return '0'
            }

            const suffixes = ['', 'K', 'M', 'B', 'T']
            const index = Math.max(0, Math.min(suffixes.length - 1, Math.floor(Math.log(Math.abs(this.followersCount)) / Math.log(1000))))

            return (this.followersCount / 1000 ** index).toLocaleString(undefined, { maximumFractionDigits: 0 }) + suffixes[index]
        },
    }))

    Alpine.data('feedMessageLockup', ({ id, content, hearted, hearts, reshared, reshares, authenticated, signInUrl }) => ({
        displayContent: '',
        hearted,
        hearts,
        reshared,
        reshares,
        deleted: false,
        busy: false,

        init() {
            this.render(content)
        },

        render(text) {
            content = text
            this.displayContent = window.markdown.parse(text.replace(/(?:https?|http):\/\/[\n\S]+$/, '').trim(), 0, null, true)
        },

        signedIn() {
            if (authenticated) {
                return true
            }

            window.Livewire.navigate(signInUrl)

            return false
        },

        heart() {
            if (!this.signedIn()) {
                return
            }

            this.busy = true
            window.Livewire.dispatch('feed-message-heart', { id })
        },

        reshare() {
            if (!this.signedIn()) {
                return
            }

            this.busy = true
            window.Livewire.dispatch('feed-message-reshare', { id })
        },

        reply() {
            window.Livewire.dispatch('feed-message-reply', { id })
        },

        open(type) {
            if (!this.signedIn()) {
                return
            }

            this.$dispatch('feed-message-modal', { type, id, content })
        },

        syncHeart({ id: updatedId, hearted, count }) {
            if (updatedId !== id) {
                return
            }

            this.hearted = hearted
            this.hearts = count
            this.busy = false
        },

        syncReShare({ id: updatedId, reshared, count }) {
            if (updatedId !== id) {
                return
            }

            this.reshared = reshared
            this.reshares = count
            this.busy = false
        },

        syncEdit({ id: updatedId, content: text }) {
            if (updatedId !== id) {
                return
            }

            this.render(text)
        },

        syncDelete({ id: updatedId }) {
            if (updatedId !== id) {
                return
            }

            this.deleted = true
        },
    }))

    Alpine.data('feedMessageModals', () => ({
        type: null,
        id: null,
        content: '',
        busy: false,

        open({ type, id, content }) {
            this.type = type
            this.id = id
            this.content = type === 'edit' ? content : ''
            this.busy = false
            this.$dispatch('open-modal', { id: 'feed-message-' + type })
        },

        close() {
            if (this.type === null) {
                return
            }

            this.$dispatch('close-modal', { id: 'feed-message-' + this.type })
            this.type = null
        },

        confirmEdit() {
            this.busy = true
            window.Livewire.dispatch('feed-message-edit', { id: this.id, content: this.content })
        },

        confirmDelete() {
            this.busy = true
            window.Livewire.dispatch('feed-message-delete', { id: this.id })
        },

        confirmQuote() {
            this.busy = true
            window.Livewire.dispatch('feed-message-quote', { id: this.id, content: this.content })
        },

        settle({ id: updatedId }) {
            if (updatedId !== this.id) {
                return
            }

            this.busy = false
            this.close()
        },
    }))

    Alpine.data('presenceStatus', ({ id, status, labels }) => ({
        status,

        init() {
            if (!window.Echo) {
                return
            }

            window.Echo.channel('user-status.' + id).listen('.user.status.changed', ({ id: changedId, status }) => {
                if (changedId === id) {
                    this.status = status
                }
            })
        },

        destroy() {
            window.Echo?.leave('user-status.' + id)
        },

        get label() {
            return labels[this.status]
        },
    }))

    Alpine.data('followButton', ({ id, followed, authenticated, signInUrl }) => ({
        followed,
        busy: false,

        toggle() {
            if (!authenticated) {
                window.Livewire.navigate(signInUrl)
                return
            }

            this.busy = true
            window.Livewire.dispatch('user-follow', { id })
        },

        sync({ id: updatedId, followed }) {
            if (updatedId !== id) {
                return
            }

            this.followed = followed
            this.busy = false
        },
    }))

    Alpine.data('themeGetButton', function ({ id }) {
        return {
            currentThemeID: this.$persist('kurozora').as('currentThemeID'),
            busy: false,

            get() {
                this.busy = true
                window.Livewire.dispatch('theme-get', { id })
            },

            sync({ id: themeId }) {
                this.currentThemeID = String(themeId)
                this.busy = false
            },
        }
    })

    Alpine.data('badgeShelf', () => ({
        icon: null,
        text: null,
        tooltipOpen: false,

        updateTooltip(element) {
            const isOpen = element.innerHTML !== this.icon ? true : !this.tooltipOpen
            this.icon = element.innerHTML
            this.text = element.attributes['title'].value

            this.calculateTooltipPosition(this.tooltipOpen && !isOpen)
            this.tooltipOpen = isOpen
        },

        calculateTooltipPosition(resetPosition) {
            const tooltip = this.$refs.badgeTooltip
            tooltip.style.top = ''
            tooltip.style.right = ''
            tooltip.style.bottom = ''
            tooltip.style.left = ''
            tooltip.style.transform = ''

            if (resetPosition) {
                return
            }

            const tooltipRect = tooltip.getBoundingClientRect()
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight
            const tooltipWidth = tooltipRect.width
            const tooltipHeight = tooltipRect.height
            const space = {
                top: tooltipRect.top,
                right: viewportWidth - tooltipRect.right,
                bottom: viewportHeight - tooltipRect.bottom,
                left: tooltipRect.left,
            }

            let [top, bottom, left, right, transform] = ['', '', '', '', '']

            if (space.top >= tooltipHeight && space.top >= space.bottom && space.top >= space.left && space.top >= space.right) {
                bottom = '100%'
                left = '50%'
                transform = 'translateX(-50%)'
            } else if (space.bottom >= tooltipHeight && space.bottom >= space.top && space.bottom >= space.left && space.bottom >= space.right) {
                top = '100%'
                left = '50%'
                transform = 'translateX(-50%)'
            } else if (space.left >= tooltipWidth && space.left >= space.top && space.left >= space.bottom && space.left >= space.right) {
                top = '50%'
                right = '100%'
                transform = 'translateY(-50%)'
            } else if (space.right >= tooltipWidth && space.right >= space.top && space.right >= space.bottom && space.right >= space.left) {
                top = '50%'
                left = '100%'
                transform = 'translateY(-50%)'
            }

            tooltip.style.top = top
            tooltip.style.right = right
            tooltip.style.bottom = bottom
            tooltip.style.left = left
            tooltip.style.transform = transform
        },
    }))
})

document.addEventListener('livewire:init', () => {
    window.Livewire.hook('commit', ({ component, fail }) => {
        if (!['user-actions', 'theme-store'].includes(component.name)) {
            return
        }

        fail(() => window.dispatchEvent(new CustomEvent('user-actions-failed')))
    })
})
