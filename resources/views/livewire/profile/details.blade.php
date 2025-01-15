<main>
    <x-slot:title>
        {{ $user->username }}
    </x-slot:title>

    <x-slot:description>
        {{ $user->biography }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x on Kurozora', ['x' => $user->username]) }}" />
        <meta property="og:description" content="{{ $user->biography ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" />
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
                    <div class="relative">
                        <livewire:components.profile-image-view :user="$user" :on-profile="true" />
                    </div>

                    <div class="flex flex-col gap-1 sm:flex-row">
                        <p class="ml-2 text-xl font-bold">{{ $user->username }}</p>

                        <livewire:components.user.badge-shelf :user="$user" wire:key="{{ uniqid('badges-', true) }}" />
                    </div>
                </div>

                <div class="flex gap-2 items-end">
                    @auth
                        @if ($user->id == auth()->user()->id)
                            <x-button wire:click="togglePopupFor('edit')">{{ __('Edit') }}</x-button>
                        @endif
                    @endif

                    @if ($user->id != auth()->user()?->id)
                        <livewire:components.follow-button :user="$user" :is-followed="$this->isFollowed" wire:key="{{ uniqid(more_entropy: true) }}" />
                    @endif

                    {{-- More Options --}}
                    <x-dropdown align="right" width="48">
                        <x-slot:trigger>
                            <x-circle-button
                                title="{{ __('More') }}"
                            >
                                @svg('ellipsis', 'fill-current', ['width' => '28'])
                            </x-circle-button>
                        </x-slot:trigger>

                        <x-slot:content>
                            <button
                                class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                wire:click="togglePopupFor('showSharePopup')"
                            >
                                {{ __('Share') }}
                            </button>

                            @auth
                                @if ($user->id != auth()->user()?->id)
                                    <button
                                        class="block w-full pl-4 pr-4 pt-2 pb-2 text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                        wire:click="togglePopupFor('block')"
                                    >
                                        {{ $this->isBlocked ? __ ('Unblock') : __('Block') }}
                                    </button>
                                @endif
                            @endauth
                        </x-slot:content>
                    </x-dropdown>
                </div>
            </div>

            <div class="mt-2 pt-2 pb-2 px-3">{!! $user->biography_html !!}</div>

            <div class="flex justify-between">
                <x-profile-information-badge href="{{ route('profile.achievements', $user) }}" wire:navigate>
                    <x-slot:title>{{ __('Achievements') }}</x-slot:title>
                    <x-slot:description>{{ number_shorten($counts['achievements_count'], 0, true) }}</x-slot:description>
                </x-profile-information-badge>

                <x-profile-information-badge href="{{ route('profile.following', $user) }}" wire:navigate>
                    <x-slot:title>{{ __('Following') }}</x-slot:title>
                    <x-slot:description>{{ number_shorten($counts['following_count'], 0, true) }}</x-slot:description>
                </x-profile-information-badge>

                <x-profile-information-badge href="{{ route('profile.followers', $user) }}" wire:navigate>
                    <x-slot:title>{{ __('Followers') }}</x-slot:title>
                    <x-slot:description>{{ number_shorten($counts['followers_count'], 0, true) }}</x-slot:description>
                </x-profile-information-badge>

                <x-profile-information-badge href="{{ route('profile.ratings', $user) }}" wire:navigate>
                    <x-slot:title>{{ __('Reviews') }}</x-slot:title>
                    <x-slot:description>{{ number_shorten($counts['media_ratings_count'], 0, true) }}</x-slot:description>
                </x-profile-information-badge>
            </div>

            <x-hr class="mt-2" />
        </section>

        <livewire:components.user.library-section :user="$user" :type="\App\Models\Anime::class" />

        <livewire:components.user.library-section :user="$user" :type="\App\Models\Manga::class" />

        <livewire:components.user.library-section :user="$user" :type="\App\Models\Game::class" />

        <livewire:components.user.favorites-section :user="$user" :type="\App\Models\Anime::class" />

        <livewire:components.user.favorites-section :user="$user" :type="\App\Models\Manga::class" />

        <livewire:components.user.favorites-section :user="$user" :type="\App\Models\Game::class" />

        <livewire:components.user.feed-messages-section :user="$user" />
    </div>

    @switch($selectedPopupType)
    @case('edit')
        @auth
            <x-modal-form-section model="showPopup" submit="">
                <x-slot:title>
                    {{ __('Edit Profile') }}
                </x-slot:title>

                <livewire:profile.update-profile-information-form />
            </x-modal-form-section>
        @endauth
    @case('block')
        @auth
            <x-dialog-modal model="showPopup" submit="">
                <x-slot:title>
                    {{ $this->isBlocked ? __('Unblock :x', ['x' => $this->user->username]) : __('Block :x', ['x' => $this->user->username]) }}
                </x-slot:title>

                <x-slot:content>
                    <p>{{ $this->isBlocked ? __('They wil be able to follow you and view your messages.') : __('They won’t be able to see your profile, and public posts. They will also not be able to follow you, and you will not see notifications from them.') }}</p>
                </x-slot:content>

                <x-slot:footer>
                    <x-outlined-button wire:click="$toggle('showPopup')">{{ __('Cancel') }}</x-outlined-button>

                    <x-button wire:click="toggleBlockUser">{{ $this->isBlocked ? __('Unblock') : __('Block') }}</x-button>
                </x-slot:footer>
            </x-dialog-modal>
        @endauth
        @break
    @endswitch
</main>

{{--<style data-client-themes="true" data-rh="true">--}}
{{--    .custom-theme-background {--}}
{{--        --custom-theme-background: linear-gradient(162.27deg, var(--bg-gradient-forest-1) 11.2%, var(--bg-gradient-forest-2) 29.93%, var(--bg-gradient-forest-3) 48.64%, var(--bg-gradient-forest-4) 67.85%, var(--bg-gradient-forest-5) 83.54%);--}}
{{--        --custom-theme-primary-color: #142215;--}}
{{--        --custom-theme-secondary-color: #a98e4b;--}}

{{--        --theme-base-color-light-hsl: 42.35294117647058 100% 90%;--}}
{{--        --theme-base-color-light: rgb(255,240,204);--}}
{{--        --theme-text-color-light: rgb(0,77,6);--}}
{{--        --theme-base-color-dark-hsl: 124.61538461538463 100% 5.098039215686274%;--}}
{{--        --theme-base-color-dark: rgb(0,26,2);--}}
{{--        --theme-text-color-dark: rgb(255,248,229);--}}
{{--        --theme-base-color-amount: 50%;--}}
{{--        --theme-text-color-amount: 38%;--}}
{{--        --bg-overlay-selected: unset;--}}
{{--        --bg-overlay-hover: unset;--}}
{{--        --bg-overlay-active: unset;--}}
{{--    }--}}
{{--</style>--}}
