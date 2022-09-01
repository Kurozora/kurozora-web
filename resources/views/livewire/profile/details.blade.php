<main>
    <x-slot:title>
        {{ $user->username }}
    </x-slot:title>

    <x-slot:description>
        {{ $user->biography }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $user->username . __(' on Kurozora') }}" />
        <meta property="og:description" content="{{ $user->biography ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $user->profile_image_url }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $user->username }}" />
        <link rel="canonical" href="{{ route('profile.details', $user) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        users/{{ $user->id }}
    </x-slot:appArgument>

    <div>
        <section>
            <livewire:components.banner-image-view :user="$user" />
        </section>

        <section class="relative max-w-7xl mx-auto pl-4 pr-4 py-6 z-10 sm:px-6">
            <div class="flex items-end justify-between -mt-14 sm:-mt-20">
                <div class="flex items-end">
                    <livewire:components.profile-image-view :user="$user" />

                    <span class="flex items-baseline">
                        <p class="ml-2 text-xl font-bold">{{ $user->username }}</p>
                        @switch ($user->getActivityStatus()->value)
                            @case(\App\Enums\UserActivityStatus::SeenRecently)
                                <span class="block ml-1 w-2 h-2 bg-yellow-500 rounded-full" title="{{ __('Seen Recently') }}"></span>
                                @break
                            @case(\App\Enums\UserActivityStatus::Online)
                                <span class="block ml-1 w-2 h-2 bg-green-500 rounded-full" title="{{ __('Online') }}"></span>
                                @break
                            @case(\App\Enums\UserActivityStatus::Offline)
                                <span class="block ml-1 w-2 h-2 bg-red-500 rounded-full" title="{{ __('Offline') }}"></span>
                        @endswitch
                    </span>
                </div>

                @auth
                    @if ($user->id == Auth::user()->id)
                        <x-button wire:click="togglePopupFor('edit')">{{ __('Edit') }}</x-button>
                    @else
                        <livewire:components.follow-button :user="$user" />
                    @endif
                @endif
            </div>

            <div class="mt-2 py-2 px-3">{!! nl2br($user->biography) !!}</div>

            <div class="flex justify-between">
                <x-profile-information-badge>
                    <x-slot:title>{{ __('Reputation') }}</x-slot:title>
                    <x-slot:description>0</x-slot:description>
                </x-profile-information-badge>

                <x-profile-information-badge wire:click="togglePopupFor('badges')">
                    <x-slot:title>{{ __('Badges') }}</x-slot:title>
                    <x-slot:description>{{ $user->badges()->count() }}</x-slot:description>
                </x-profile-information-badge>

                <x-profile-information-badge wire:click="togglePopupFor('following')">
                    <x-slot:title>{{ __('Following') }}</x-slot:title>
                    <x-slot:description>{{ $user->following()->count() }}</x-slot:description>
                </x-profile-information-badge>

                <x-profile-information-badge wire:click="togglePopupFor('followers')">
                    <x-slot:title>{{ __('Followers') }}</x-slot:title>
                    <x-slot:description>{{ $user->followers()->count() }}</x-slot:description>
                </x-profile-information-badge>
            </div>

            <x-hr class="mt-2" />
        </section>
    </div>

    @switch ($selectedPopupType)
    @case('edit')
        @auth
            <x-modal-form-section wire:model="showPopup" submit="">
                <x-slot:title>
                    {{ __('Edit Profile') }}
                </x-slot:title>

                <livewire:profile.update-profile-information-form />
            </x-modal-form-section>
        @endauth
        @break
    @case ('badges')
        <x-page-modal maxWidth="sm" wire:model="showPopup">
            <x-slot:title>
                {{ __('Badges') }}
            </x-slot:title>

            <livewire:profile.badges :user="$user" />
        </x-page-modal>
        @break
    @case ('followers')
        <x-page-modal maxWidth="sm" wire:model="showPopup">
            <x-slot:title>
                {{ __('Followers') }}
            </x-slot:title>

            <livewire:profile.followers.index :user="$user" />
        </x-page-modal>
        @break
    @case ('following')
        <x-page-modal maxWidth="sm" wire:model="showPopup">
            <x-slot:title>
                {{ __('Following') }}
            </x-slot:title>

            <livewire:profile.following.index :user="$user" />
        </x-page-modal>
        @break
    @endswitch
</main>
