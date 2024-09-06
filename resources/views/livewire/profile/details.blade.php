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
                </div>
            </div>

            <div class="mt-2 pt-2 pb-2 px-3">{!! $user->biography_html !!}</div>

            <div class="flex justify-between">
                <x-profile-information-badge wire:click="togglePopupFor('achievements')">
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

                <x-profile-information-badge href="{{ route('profile.ratings.index', $user) }}" wire:navigate>
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
        @break
    @case ('achievements')
        <x-page-modal maxWidth="sm" model="showPopup">
            <x-slot:title>
                {{ __('Achievements') }}
            </x-slot:title>

            <livewire:profile.badges :user="$user" />
        </x-page-modal>
        @break
    @endswitch
</main>
