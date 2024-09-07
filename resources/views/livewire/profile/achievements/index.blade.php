<main>
    <x-slot:title>
        {{ __(':x’s Followers', ['x' => $user->username]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse :x’s achievements on Kurozora. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $user->username]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s Followers', ['x' => $user->username]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse :x’s achievements on Kurozora. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $user->username]) }}" />
        <meta property="og:image" content="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('profile.achievements', $user) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        users/{{ $user->id }}/achievements
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Achievements', ['x' => $user->username]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($this->achievements->count())
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($this->achievements as $achievement)
                   <x-lockups.achievement-lockup :achievement="$achievement" />
                @endforeach
            </section>

            <div class="mt-4">
                {{ $this->achievements->links() }}
            </div>
        @elseif (!$readyToLoad)
            <section class="mt-4">
                <div class="flex gap-4 justify-between flex-wrap">
                    @foreach(range(1,25) as $range)
                        <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @else
            <section class="flex flex-col items-center mt-4 text-center">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="No reviews" title="No achievements">
                </x-picture>

                <p class="font-bold">{{ __('No Achievements') }}</p>

                @if ($user->id == auth()->user()?->id)
                    <p class="text-sm text-gray-500">{{ __('Your unlocked achievements will show up here!') }}</p>
                @else
                    <p class="text-sm text-gray-500">{{ __(':x has no achievements unlocked yet.', ['x' => $user->username]) }}</p>
                @endif
            </section>
        @endif
    </div>
</main>
