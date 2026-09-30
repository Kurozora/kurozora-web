<x-base-layout>
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

    <main>
        <div
            class="pt-4 pb-6"
            x-data="notificationsPage({{ Js::from([
                'userId' => auth()->id(),
                'url' => route('notifications.index'),
                'labels' => [
                    'selected' => __(':count Selected'),
                    'select' => __('Select Notifications'),
                    'markRead' => __('Mark as read'),
                    'markUnread' => __('Mark as unread'),
                ],
            ]) }})"
            x-on:notifications-updated.window="settle()"
            x-on:user-actions-failed.window="busy = false"
        >
            <section class="mb-4 xl:safe-area-inset">
                <div class="flex flex-wrap items-baseline justify-between gap-2 pl-4 pr-4">
                    <div class="flex flex-col">
                        <h1 class="text-2xl font-bold">{{ __('Notifications') }}</h1>

                        <p
                            class="text-sm text-secondary leading-5"
                            style="min-height: 1.25rem;"
                            x-text="selectMode ? countLabel() : ''"
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
                                    x-text="markActionLabel()"
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

            <section class="xl:safe-area-inset" data-paginated="notifications" data-paginated-refresh-on="notifications-updated">
                @if ($notifications->count())
                    <div class="bg-secondary rounded-xl ml-4 mr-4">
                        <ul class="flex flex-col m-0">
                            @foreach ($notifications as $key => $notification)
                                <li
                                    class="relative group rounded-md"
                                    key="notification-{{ $notification->id }}"
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

                                    @if ($key !== $notifications->count() - 1)
                                        <x-hr class="m-0" />
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-4 pl-4 pr-4">
                        {{ $notifications->links() }}
                    </div>
                @else
                    <section class="flex flex-col items-center gap-2 mt-10 mb-10 pr-2 pl-2">
                        @svg('app_badge', 'fill-current', ['width' => 64])
                        <p class="text-center font-semibold">{{ __('No Notifications') }}</p>

                        <p class="text-sm text-center">{{ __('When you have notifications, you will see them here!') }}</p>
                    </section>
                @endif
            </section>

            <div
                x-data="confirmModal({ modal: 'notifications-delete', event: 'notifications-delete' })"
                x-on:notifications-delete-modal.window="open($event.detail)"
                x-on:notifications-updated.window="settle()"
                x-on:user-actions-failed.window="busy = false"
            >
                <x-dialog-modal id="notifications-delete" maxWidth="md">
                    <x-slot:title>
                        {{ __('Delete Notifications') }}
                    </x-slot:title>

                    <x-slot:content>
                        <div class="pt-4 pb-4 pl-4 pr-4">
                            <p x-text="payload && payload.ids.length === 1 ? @js(__('This notification will be removed.')) : @js(__('These notifications will be removed.'))"></p>
                        </div>
                    </x-slot:content>

                    <x-slot:footer>
                        <x-outlined-button x-on:click="close()" x-bind:disabled="busy">
                            {{ __('Cancel') }}
                        </x-outlined-button>

                        <x-button class="ml-2" x-on:click="confirm()" x-bind:disabled="busy">
                            {{ __('Delete') }}
                        </x-button>
                    </x-slot:footer>
                </x-dialog-modal>
            </div>
        </div>
    </main>
</x-base-layout>
