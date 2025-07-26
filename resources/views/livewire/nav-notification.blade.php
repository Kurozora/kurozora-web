<x-dropdown
    align="right"
    width="64"
    id="notification-dropdown"
>
    <x-slot:trigger>
        <button
            class="relative inline-flex h-8 w-8 items-center justify-center text-secondary cursor-pointer transition duration-150 ease-in-out hover:text-primary focus:text-primary"
            x-show="! isSearchEnabled"
            wire:click="$dispatch('is-notifications-open', { 'isOpen': true })"
            x-transition:enter="ease-out duration-150 delay-[350ms] transform"
            x-transition:enter-start="opacity-0 scale-75"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-75"
            @auth
            x-data="{
                onFocus() {
                    $el.setAttribute('wire:poll.5s', 'pollForNewNotifications')
                    console.log('Polling for new notifications every 5 seconds')
                },
                onBlur() {
                    $el.removeAttribute('wire:poll.5s')
                    console.log('Stopped polling for new notifications')
                }
            }"
            x-on:focus.window="onFocus()"
            x-on:blur.window="onBlur()"
            x-init="onFocus()"
            @endauth
        >
            @if($newNotifications)
                <span class="absolut bg-tint aspect-square rounded-full z-1" style="top: 0.45rem;right: 0.45rem;width: 0.40rem;"></span>
            @endif
            @svg('app_badge', 'fill-current', ['width' => '18'])
        </button>

        <x-slot:content>
            <div class="pt-2 pb-2">
                <p class="pr-2 mb-2 pl-2 text-lg font-semibold">{{ __('Notifications') }}</p>

                @if ($isNotificationOpen)
                    @auth
                        @if ($this->notifications->count())
                            <ul class="flex flex-col gap-2 m-0">
                                @foreach ($this->notifications as $key => $notification)
                                    <li class="relative">
                                        @switch($notification->type)
                                            @case(\App\Notifications\NewSession::class)
                                                <div class="flex flex-nowrap justify-between pr-2 pl-2">
                                                    <div class="flex items-center">
                                                        <div class="pl-2 pr-2">
                                                            <p class="leading-tight line-clamp-2" title="{{ $notification->localized_type }}">{{ $notification->localized_type }}</p>
                                                            <p class="text-xs leading-tight opacity-75" title="{{ $notification->description }}">{{ $notification->description }}</p>
                                                        </div>

                                                        <a class="absolute w-full h-full" href="{{ route('profile.settings') }}" wire:navigate></a>
                                                    </div>
                                                </div>
                                                @break
                                            @case(\App\Notifications\SubscriptionStatus::class)
{{--                                                {{ $notification->data['subscriptionStatus'] }}--}}
{{--                                            @break--}}
                                            @case(\App\Notifications\LocalLibraryImportFinished::class)
{{--                                                {{ $notification->data['behavior'] }}--}}
{{--                                            @break--}}
                                            @case(\App\Notifications\LibraryImportFinished::class)
{{--                                                {{ $notification->data['library'] }}--}}
                                                <div class="flex flex-nowrap justify-between pr-2 pl-2">
                                                    <div class="flex items-center">
                                                        <div class="pl-2 pr-2">
                                                            <p class="leading-tight line-clamp-2" title="{{ $notification->localized_type }}">{{ $notification->localized_type }}</p>
                                                            <p class="text-xs leading-tight opacity-75">{{ $notification->description }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                @break
                                            @case(\App\Notifications\NewFeedMessageReply::class)
                                            @case(\App\Notifications\NewFollower::class)
                                                <div class="flex flex-nowrap justify-between pr-2 pl-2">
                                                    <div class="flex items-center">
                                                        <picture class="relative shrink-0 w-10 aspect-square rounded-full overflow-hidden">
                                                            <img
                                                                class="w-full h-full object-cover lazyload"
                                                                data-sizes="auto"
                                                                data-src="{{ $notification->getData('profileImageURL') ?? 'https://ui-avatars.com/api/?name=' . $notification->getData('username') . '&color=FFFFFF&background=AAAAAA&length=1&bold=true&size=256' }}"
                                                                alt="{{ $notification->getData('username') }} Profile Image"
                                                                title="{{ $notification->getData('username') }}"
                                                                width="24"
                                                                height="24"
                                                            >

                                                            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
                                                        </picture>

                                                        <div class="pl-2 pr-2">
                                                            <p class="leading-tight line-clamp-2" title="{{ $notification->getData('username') }}">{{ $notification->getData('username') }}</p>
                                                            <p class="text-xs leading-tight opacity-75">{{ $notification->description }}</p>
                                                        </div>
                                                    </div>

                                                    @if ($notification->notifier)
                                                        <a class="absolute w-full h-full" href="{{ route('profile.details', $notification->notifier) }}" wire:navigate></a>
                                                    @endif
                                                </div>
                                                @break
                                            @default
                                                <p class="pr-2 pl-2 text-sm">{{ $notification->description }}</p>
                                        @endswitch

                                        @if ($key != $this->notifications->count() - 1)
                                            <x-hr class="mt-2" />
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="flex flex-col items-center gap-2 mt-5 mb-5 pr-2 pl-2">
                                @svg('app_badge', 'fill-current', ['width' => 64])
                                <p class="text-center font-semibold">{{ __('No Notifications') }}</p>

                                <p class="text-sm text-center">{{ __('When you have notifications, you will see them here!') }}</p>
                            </div>
                        @endif

                        <div class="flex justify-center w-full">
                            <x-spinner />
                        </div>
                    @else
                        <div class="flex flex-col items-center gap-2 mt-5 mb-5 pr-2 pl-2">
                            @svg('app_badge', 'fill-current', ['width' => 64])
                            <p class="text-center font-semibold">{{ __('No Notifications') }}</p>

                            <p class="text-sm text-center">{{ __('Notifications are only available to registered :x users.', ['x' => config('app.name')]) }}</p>

                            <x-link class="text-sm" href="{{ route('sign-in') }}" wire:navigate>{{ __('Sign in') }}</x-link>
                        </div>
                    @endauth
                @endif
            </div>
        </x-slot:content>
    </x-slot:trigger>
</x-dropdown>
