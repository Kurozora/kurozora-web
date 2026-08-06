<main>
    <x-slot:title>
        {{ __('Notifications') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Stay up to date with replies, follows, mentions and library updates.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Notifications') }}" />
        <meta property="og:description" content="{{ __('Stay up to date with replies, follows, mentions and library updates.') }}" />
        <meta property="og:type" content="website" />
        <meta name="robots" content="noindex" />
        <link rel="canonical" href="{{ route('notifications.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        notifications
    </x-slot:appArgument>

    <div
        class="pt-4 pb-6"
        x-data="{
            selectMode: false,
            selected: {},
            get selectedIds() {
                return Object.keys(this.selected);
            },
            get hasSelection() {
                return this.selectedIds.length > 0;
            },
            get selectionCount() {
                return this.selectedIds.length;
            },
            get visibleRows() {
                return Array.from(this.$root.querySelectorAll('[data-notification-id]'));
            },
            get allSelected() {
                const rows = this.visibleRows;
                return rows.length > 0 && rows.every(row => this.selected[row.dataset.notificationId] !== undefined);
            },
            get anySelectedUnread() {
                return Object.values(this.selected).some(isUnread => isUnread);
            },
            get markActionLabel() {
                return this.anySelectedUnread
                    ? @js(__('Mark as read'))
                    : @js(__('Mark as unread'));
            },
            get countLabel() {
                return this.hasSelection
                    ? @js(__(':count Selected')).replace(':count', this.selectionCount)
                    : @js(__('Select Notifications'));
            },
            isSelected(id) {
                return this.selected[id] !== undefined;
            },
            toggleSelection(id, isUnread) {
                if (this.selected[id] !== undefined) {
                    delete this.selected[id];
                } else {
                    this.selected[id] = isUnread;
                }
            },
            enterSelectMode() {
                this.selectMode = true;
                this.selected = {};
            },
            exitSelectMode() {
                this.selectMode = false;
                this.selected = {};
            },
            toggleSelectAll() {
                if (this.allSelected) {
                    this.selected = {};
                    return;
                }
                this.visibleRows.forEach(row => {
                    this.selected[row.dataset.notificationId] = row.dataset.unread === '1';
                });
            },
            async batchMark() {
                if (!this.hasSelection) return;
                const ids = this.selectedIds;
                const read = this.anySelectedUnread;
                await $wire.setReadStatus(ids, read);
                this.exitSelectMode();
            },
            async batchDelete() {
                if (!this.hasSelection) return;
                const count = this.selectionCount;
                const message = count === 1
                    ? @js(__('This notification will be removed.'))
                    : @js(__('These notifications will be removed.'));
                if (!confirm(message)) return;
                await $wire.deleteMany(this.selectedIds);
                this.exitSelectMode();
            },
        }"
    >
        <section class="mb-4 xl:safe-area-inset">
            <div class="flex flex-wrap items-baseline justify-between gap-2 pl-4 pr-4">
                <div class="flex flex-col">
                    <h1 class="text-2xl font-bold">{{ __('Notifications') }}</h1>

                    <p
                        class="text-sm text-secondary leading-5"
                        style="min-height: 1.25rem;"
                        x-text="selectMode ? countLabel : ''"
                    ></p>
                </div>

                <div class="flex items-center gap-4">
                    <template x-if="!selectMode">
                        <button
                            type="button"
                            class="text-sm text-tint hover:opacity-75 cursor-pointer"
                            x-on:click="enterSelectMode()"
                        >
                            {{ __('Select') }}
                        </button>
                    </template>

                    <template x-if="selectMode">
                        <div class="flex items-center gap-4">
                            <button
                                type="button"
                                class="text-sm text-tint hover:opacity-75 cursor-pointer"
                                x-on:click="toggleSelectAll()"
                                x-text="allSelected ? @js(__('Deselect All')) : @js(__('Select All'))"
                            ></button>

                            <button
                                type="button"
                                class="text-sm text-tint hover:opacity-75 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                x-bind:disabled="!hasSelection"
                                x-on:click="batchMark()"
                                x-text="markActionLabel"
                            ></button>

                            <button
                                type="button"
                                class="text-sm text-red-500 hover:opacity-75 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                x-bind:disabled="!hasSelection"
                                x-on:click="batchDelete()"
                            >
                                {{ __('Delete') }}
                            </button>

                            <button
                                type="button"
                                class="text-sm text-secondary hover:text-primary cursor-pointer"
                                x-on:click="exitSelectMode()"
                                aria-label="{{ __('Cancel') }}"
                                title="{{ __('Cancel') }}"
                            >
                                @svg('xmark', 'fill-current', ['width' => 14])
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        @if ($this->notifications->count())
            <section class="xl:safe-area-inset">
                <div class="bg-secondary rounded-xl pl-2 pr-2 pt-1 pb-1 ml-4 mr-4">
                    <ul class="flex flex-col m-0">
                        @foreach ($this->notifications as $key => $notification)
                            <li
                                class="relative group rounded-md"
                                wire:key="notification-{{ $notification->id }}"
                                data-notification-id="{{ $notification->id }}"
                                data-unread="{{ $notification->isUnread() ? '1' : '0' }}"
                            >
                                <x-notifications.row :notification="$notification" :supports-select="true" />

                                <div
                                    class="absolute top-1 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-150"
                                    x-show="!selectMode"
                                >
                                    <x-notifications.row-actions :notification="$notification" />
                                </div>

                                @if ($key !== $this->notifications->count() - 1)
                                    <x-hr class="m-0" />
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->notifications->links() }}
                </div>
            </section>
        @else
            <section class="flex flex-col items-center gap-2 mt-10 mb-10 pr-2 pl-2 xl:safe-area-inset">
                @svg('app_badge', 'fill-current', ['width' => 64])
                <p class="text-center font-semibold">{{ __('No Notifications') }}</p>

                <p class="text-sm text-center">{{ __('When you have notifications, you will see them here!') }}</p>
            </section>
        @endif
    </div>
</main>
