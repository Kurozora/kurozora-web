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
        <section class="mb-4">
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
            <x-rows.user-lockup :users="$this->following" :is-row="false" />

            <div class="mt-4 pl-4 pr-4">
                {{ $this->following->links() }}
            </div>
        @elseif (!$readyToLoad)
            <section class="mt-4">
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @else
            <section class="flex flex-col items-center mt-4 text-center">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="No following" title="No following">
                </x-picture>

                <p class="font-bold">{{ __('No Following') }}</p>

                @if ($user->id != auth()->user()?->id)
                    <p class="text-sm text-secondary">{{ __('Be the first to follow :x!', ['x' => $user->username]) }}</p>
                    <livewire:components.follow-button :user="$user" :is-followed="(bool) $user->isFollowed" wire:key="{{ uniqid(more_entropy: true) }}" />
                @else
                    <p class="text-sm text-secondary">{{ __('Users you follow show up here!') }}</p>
                @endif
            </section>
        @endif
    </div>
</main>
