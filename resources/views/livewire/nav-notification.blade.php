<div class="pt-2 pb-2">
    <p class="pr-2 mb-2 pl-2 text-lg font-semibold">{{ __('Notifications') }}</p>

    @if ($isNotificationOpen)
        @if ($this->notifications->count())
            <ul class="flex flex-col gap-2 m-0">
                @foreach ($this->notifications as $key => $notification)
                    <li class="relative">
                        @switch($notification->type)
                            @case(\App\Notifications\NewSession::class)
                                <div class="flex flex-nowrap justify-between pr-2 pl-2">
                                    <div class="flex items-center">
                                        <div class="pl-2 pr-2">
                                            <p class="leading-tight line-clamp-2">{{ $notification->localized_type }}</p>
                                            <p class="text-xs leading-tight opacity-75">{{ $notification->description }}</p>
                                        </div>

                                        <a class="absolute w-full h-full" href="{{ route('profile.settings') }}"></a>
                                    </div>
                                </div>
                                @break
                            @case(\App\Notifications\SubscriptionStatus::class)
{{--                                {{ $notification->data['subscriptionStatus'] }}--}}
{{--                            @break--}}
                            @case(\App\Notifications\LocalLibraryImportFinished::class)
{{--                                {{ $notification->data['behavior'] }}--}}
{{--                            @break--}}
                            @case(\App\Notifications\LibraryImportFinished::class)
{{--                                {{ $notification->data['library'] }}--}}
                                <div class="flex flex-nowrap justify-between pr-2 pl-2">
                                    <div class="flex items-center">
                                        <div class="pl-2 pr-2">
                                            <p class="leading-tight line-clamp-2">{{ $notification->localized_type }}</p>
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
                                            <p class="leading-tight line-clamp-2">{{ $notification->getData('username') }}</p>
                                            <p class="text-xs leading-tight opacity-75">{{ $notification->description }}</p>
                                        </div>
                                    </div>

                                    @if ($notification->notifier)
                                        <a class="absolute w-full h-full" href="{{ route('profile.details', $notification->notifier) }}"></a>
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

                @auth
                    <p class="text-sm text-center">{{ __('When you have notifications, you will see them here!') }}</p>
                @else
                    <p class="text-sm text-center">{{ __('Notifications are only available to registered :x users.', ['x' => config('app.name')]) }}</p>

                    <x-link class="text-sm" href="{{ route('sign-in') }}">{{ __('Sign in') }}</x-link>
                @endauth
            </div>
        @endif
    @else
        <div class="flex justify-center w-full">
            <x-spinner />
        </div>
    @endif
</div>
