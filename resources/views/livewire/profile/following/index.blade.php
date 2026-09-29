<main>
    <x-slot:title>
        {{ __(':x’s Following', ['x' => $user->username]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse the users :x is following on :y. Join the :y community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $user->username, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s Following', ['x' => $user->username]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse :x’s following on :y. Join the :y community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $user->username, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('profile.following', $user) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        users/{{ $user->id }}/following
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Following', ['x' => $user->username]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($this->following->count())
            <section class="xl:safe-area-inset">
                <x-rows.user-lockup :users="$this->following" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->following->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section class="mt-4 xl:safe-area-inset">
                <x-skeletons.lockup-row lockup="user" :is-row="false" />
            </section>
        @else
            <x-empty-state image="empty_anime_library.webp" alt="No following" :heading="__('No Following')">
                @if ($user->id != auth()->user()?->id)
                    <p class="text-sm text-secondary">{{ __('Be the first to follow :x!', ['x' => $user->username]) }}</p>
                    <x-follow-button :user="$user" :is-followed="(bool) $user->isFollowed" />
                @else
                    <p class="text-sm text-secondary">{{ __('Users you follow show up here!') }}</p>
                @endif
            </x-empty-state>
        @endif
    </div>
</main>
