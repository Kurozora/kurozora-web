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

                    <div>
                        <span class="flex items-end">
                            <p class="ml-2 text-xl font-bold">{{ $user->username }}</p>
                            @if ($user->is_verified)
                                <span class="block ml-1" title="{{ __('This account is verified because it’s notable in animators, voice actors, entertainment studios, or another designated category.') }}">
                                    @svg('checkmark_seal_fill', 'text-orange-500 fill-current', ['width' => 24, 'style' => 'min-width: 24px;'])
                                </span>
                            @endif
                            @if ($user->is_pro || $user->is_subscribed)
                                <x-pro-badge class="ml-1" />
                            @endif
                        </span>
                    </div>
                </div>

                @auth
                    @if ($user->id == auth()->user()->id)
                        <x-button wire:click="togglePopupFor('edit')">{{ __('Edit') }}</x-button>
                    @else
                        <livewire:components.follow-button :user="$user" />
                    @endif
                @endif
            </div>

            <div class="mt-2 pt-2 pb-2 px-3">{!! $user->biography_html !!}</div>

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

        @if ($this->userLibrary->count())
            <section class="relative max-w-7xl mx-auto pl-4 pr-4 pb-6 mb-8 z-10 sm:px-6">
                <x-section-nav class="flex flex-nowrap justify-between mb-5">
                    <x-slot:title>
                        {{ __('Anime Library') }}
                    </x-slot:title>

                    <x-slot:action>
                        <x-section-nav-link class="whitespace-nowrap" href="{{ route('profile.anime-library', $user) }}">{{ __('See All') }}</x-section-nav-link>
                    </x-slot:action>
                </x-section-nav>

                <x-rows.small-lockup :animes="$this->userLibrary->map(function($userLibrary) { return $userLibrary->anime; })" />
            </section>
        @endif

        @if ($this->favoriteAnime->count())
            <section class="relative max-w-7xl mx-auto pl-4 pr-4 pb-6 mb-8 z-10 sm:px-6">
                <x-section-nav class="flex flex-nowrap justify-between mb-5">
                    <x-slot:title>
                        {{ __('Favorite Anime') }}
                    </x-slot:title>

                    <x-slot:action>
                        <x-section-nav-link class="whitespace-nowrap" href="{{ route('profile.favorite-anime', $user) }}">{{ __('See All') }}</x-section-nav-link>
                    </x-slot:action>
                </x-section-nav>

                <x-rows.small-lockup :animes="$this->favoriteAnime" />
            </section>
        @endif

        <section class="relative max-w-7xl mx-auto pl-4 pr-4 pb-6 mb-8 z-10 sm:px-6">
            <x-section-nav class="flex flex-nowrap justify-between mb-5">
                <x-slot:title>
                    {{ __('Feed') }}
                </x-slot:title>
            </x-section-nav>

            <div class="flex flex-col gap-6">
                @foreach ($this->feedMessages as $feedMessage)
                    <x-lockups.feed-message-lockup :feed-message="$feedMessage" />
                @endforeach
            </div>

            <div class="mt-4">
                {{ $this->feedMessages->links() }}
            </div>
        </section>
    </div>

    @switch ($selectedPopupType)
    @case('edit')
        @auth
            <x-modal-form-section model="showPopup" submit="">
                <x-slot:title>
                    {{ __('Edit Profile') }}
                </x-slot:title>

                <livewire:profile.update-profile-information-form />
            </x-modal-form-section>
        @endauth
        @break
    @case ('badges')
        <x-page-modal maxWidth="sm" model="showPopup">
            <x-slot:title>
                {{ __('Badges') }}
            </x-slot:title>

            <livewire:profile.badges :user="$user" />
        </x-page-modal>
        @break
    @case ('followers')
        <x-page-modal maxWidth="sm" model="showPopup">
            <x-slot:title>
                {{ __('Followers') }}
            </x-slot:title>

            <livewire:profile.followers.index :user="$user" />
        </x-page-modal>
        @break
    @case ('following')
        <x-page-modal maxWidth="sm" model="showPopup">
            <x-slot:title>
                {{ __('Following') }}
            </x-slot:title>

            <livewire:profile.following.index :user="$user" />
        </x-page-modal>
        @break
    @endswitch
</main>
