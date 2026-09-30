function predictVote(state, direction) {
    const tapped = direction === 'helpful'
    const predicted = state.helpful === tapped ? null : tapped
    let { helpfulCount, unhelpfulCount } = state

    if (state.helpful === true) {
        helpfulCount = Math.max(0, helpfulCount - 1)
    } else if (state.helpful === false) {
        unhelpfulCount = Math.max(0, unhelpfulCount - 1)
    }

    if (predicted === true) {
        helpfulCount++
    } else if (predicted === false) {
        unhelpfulCount++
    }

    return { helpful: predicted, helpfulCount, unhelpfulCount }
}

function helpfulnessLockup({ id, spoiler, storageKey, helpful, helpfulCount, unhelpfulCount, authenticated, signInUrl, event }) {
    return {
        isDisabled: false,
        helpful,
        helpfulCount,
        unhelpfulCount,
        deleted: false,
        busy: false,

        init() {
            this.isDisabled = spoiler && !sessionStorage.getItem(storageKey + id)
        },

        dismissSpoiler() {
            this.isDisabled = false
            sessionStorage.setItem(storageKey + id, '1')
        },

        signedIn() {
            if (authenticated) {
                return true
            }

            window.Livewire.navigate(signInUrl)

            return false
        },

        vote(direction) {
            if (!this.signedIn()) {
                return
            }

            Object.assign(this, predictVote(this, direction))
            this.busy = true
            window.Livewire.dispatch(event, { id, direction })
        },

        syncVote({ id: updatedId, helpful, helpfulCount, unhelpfulCount }) {
            if (updatedId !== id) {
                return
            }

            this.helpful = helpful
            this.helpfulCount = helpfulCount
            this.unhelpfulCount = unhelpfulCount
            this.busy = false
        },

        syncDelete({ id: updatedId }) {
            if (updatedId !== id) {
                return
            }

            this.deleted = true
        },
    }
}

function selection(property, rowValue = () => true) {
    const attribute = 'data-' + property.replace(/[A-Z]/g, (letter) => '-' + letter.toLowerCase())

    return {
        selectMode: false,
        selected: {},

        get selectedKeys() {
            return Object.keys(this.selected)
        },

        get hasSelection() {
            return this.selectedKeys.length > 0
        },

        get selectionCount() {
            return this.selectedKeys.length
        },

        get visibleRows() {
            return Array.from(this.$root.querySelectorAll('[' + attribute + ']'))
        },

        get allSelected() {
            const rows = this.visibleRows

            return rows.length > 0 && rows.every((row) => this.selected[row.dataset[property]] !== undefined)
        },

        isSelected(key) {
            return this.selected[key] !== undefined
        },

        toggleSelection(key, value = true) {
            if (this.selected[key] !== undefined) {
                delete this.selected[key]
            } else {
                this.selected[key] = value
            }
        },

        enterSelectMode() {
            this.selectMode = true
            this.selected = {}
        },

        exitSelectMode() {
            this.selectMode = false
            this.selected = {}
        },

        toggleSelectAll() {
            if (this.allSelected) {
                this.selected = {}
                return
            }

            this.visibleRows.forEach((row) => {
                this.selected[row.dataset[property]] = rowValue(row)
            })
        },
    }
}

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

    Alpine.data('reviewLockup', (lockup) => Object.assign(helpfulnessLockup({ ...lockup, storageKey: 'review-spoiler-dismissed-', event: 'review-vote' }), {
        elevated: lockup.elevated,

        elevate() {
            this.busy = true
            window.Livewire.dispatch('review-elevate', { id: lockup.id })
        },

        remove() {
            this.busy = true
            window.Livewire.dispatch('review-delete', { id: lockup.id })
        },

        report() {
            if (!this.signedIn()) {
                return
            }

            this.$dispatch('review-report-modal', { id: lockup.id })
        },

        update() {
            window.Livewire.dispatch('show-review-box', { id: lockup.reviewBoxId })
        },

        syncElevate({ id, elevated }) {
            if (id === lockup.id) {
                this.elevated = elevated
                this.busy = false
                return
            }

            if (elevated) {
                this.elevated = false
            }
        },
    }))

    Alpine.data('parentalGuideEntryLockup', (lockup) => Object.assign(helpfulnessLockup({ ...lockup, storageKey: 'pg-spoiler-dismissed-', event: 'parental-guide-vote' }), {
        edit() {
            window.Livewire.dispatch('parental-guide-box-open', { entry: lockup.id })
        },

        remove() {
            this.$dispatch('parental-guide-delete-modal', { id: lockup.id })
        },

        report() {
            if (!this.signedIn()) {
                return
            }

            this.$dispatch('parental-guide-report-modal', { id: lockup.id })
        },
    }))

    Alpine.data('reportModal', ({ modal, event, reason: defaultReason, otherReason }) => ({
        id: null,
        reason: defaultReason,
        details: '',
        error: null,
        busy: false,

        open({ id }) {
            this.id = id
            this.reason = defaultReason
            this.details = ''
            this.error = null
            this.busy = false
            this.$dispatch('open-modal', { id: modal })
        },

        close() {
            this.$dispatch('close-modal', { id: modal })
        },

        get requiresDetails() {
            return this.reason === otherReason
        },

        submit() {
            this.busy = true
            this.error = null
            window.Livewire.dispatch(event, { id: this.id, reason: this.reason, details: this.details })
        },

        settle({ id }) {
            if (id !== this.id) {
                return
            }

            this.busy = false
            this.close()
        },

        fail({ id, message }) {
            if (id !== this.id) {
                return
            }

            this.busy = false
            this.error = message
        },
    }))

    Alpine.data('confirmModal', ({ modal, event }) => ({
        payload: null,
        busy: false,

        open(payload) {
            this.payload = payload
            this.busy = false
            this.$dispatch('open-modal', { id: modal })
        },

        close() {
            this.$dispatch('close-modal', { id: modal })
        },

        confirm() {
            this.busy = true
            window.Livewire.dispatch(event, this.payload)
        },

        settle() {
            if (this.payload === null) {
                return
            }

            this.payload = null
            this.busy = false
            this.close()
        },
    }))

    Alpine.data('appIconButton', function ({ name, url, premium, authenticated, signInUrl }) {
        return {
            currentAppIconName: this.$persist('Kurozora').as('currentAppIconName'),
            busy: false,

            get selected() {
                return this.currentAppIconName.toLowerCase() === name.toLowerCase()
            },

            select() {
                if (!premium) {
                    window.Livewire.dispatch('app-icon-changed', { appIcon: { name, url } })
                    return
                }

                if (!authenticated) {
                    window.Livewire.navigate(signInUrl)
                    return
                }

                this.busy = true
                window.Livewire.dispatch('app-icon-set', { name })
            },

            sync({ appIcon }) {
                this.currentAppIconName = appIcon.name
                this.busy = false
            },
        }
    })

    Alpine.data('notificationsPage', ({ userId, url, labels }) => Object.assign(selection('notificationId', (row) => row.dataset.unread === '1'), {
        busy: false,
        listeners: {},

        init() {
            const channel = window.Echo?.private('users.' + userId)

            if (!channel) {
                return
            }

            this.listeners = {
                '.notification.created': () => this.reload(),
                '.notification.read': () => this.refresh(),
                '.notification.deleted': () => this.reload(),
            }

            for (const [name, listener] of Object.entries(this.listeners)) {
                channel.listen(name, listener)
            }
        },

        destroy() {
            const channel = window.Echo?.private('users.' + userId)

            for (const [name, listener] of Object.entries(this.listeners)) {
                channel?.stopListening(name, listener)
            }
        },

        list() {
            return this.$root.querySelector('[data-paginated]')
        },

        refresh() {
            const list = this.list()

            window.paginationManager.load(list, list.dataset.paginatedUrl ?? window.location.href)
        },

        reload() {
            window.paginationManager.load(this.list(), url, { history: 'replace' })
        },

        anySelectedUnread() {
            return Object.values(this.selected).some((isUnread) => isUnread)
        },

        markActionLabel() {
            return this.anySelectedUnread() ? labels.markRead : labels.markUnread
        },

        countLabel() {
            return this.hasSelection ? labels.selected.replace(':count', this.selectionCount) : labels.select
        },

        setRead(ids, read) {
            this.busy = true
            window.Livewire.dispatch('notifications-read', { ids, read })
        },

        confirmDelete(ids) {
            this.$dispatch('notifications-delete-modal', { ids })
        },

        batchMark() {
            if (!this.hasSelection) {
                return
            }

            this.setRead(this.selectedKeys, this.anySelectedUnread())
        },

        batchDelete() {
            if (!this.hasSelection) {
                return
            }

            this.confirmDelete(this.selectedKeys)
        },

        settle() {
            this.busy = false
            this.exitSelectMode()
        },
    }))

    Alpine.data('sessionsPage', ({ labels }) => Object.assign(selection('sessionKey'), {
        keys: [],
        all: false,
        password: '',
        error: null,
        busy: false,

        countLabel() {
            return this.hasSelection ? labels.selected.replace(':count', this.selectionCount) : labels.select
        },

        confirm(keys, all = false) {
            this.keys = keys
            this.all = all
            this.password = ''
            this.error = null
            this.busy = false
            this.$dispatch('open-modal', { id: 'sessions-sign-out' })
        },

        close() {
            this.$dispatch('close-modal', { id: 'sessions-sign-out' })
        },

        signOut() {
            this.busy = true
            this.error = null
            window.Livewire.dispatch('sessions-sign-out', { keys: this.keys, all: this.all, password: this.password })
        },

        batchSignOut() {
            if (!this.hasSelection) {
                return
            }

            this.confirm(this.selectedKeys)
        },

        settle() {
            this.busy = false
            this.close()
            this.exitSelectMode()
        },

        fail({ message }) {
            this.busy = false
            this.error = message
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
