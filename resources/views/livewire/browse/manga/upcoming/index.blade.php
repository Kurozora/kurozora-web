<main>
    <x-slot:title>
        {{ __('Upcoming') }} | {{ __('Manga') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse the upcoming manga season. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Upcoming') }} | {{ __('Manga') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse the upcoming manga season. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('manga.upcoming.index') }}">
    </x-slot:meta>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Upcoming Manga') }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomManga">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random upcoming manga', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        @if ($this->searchResults->count())
            <section class="mt-4">
                <x-rows.small-lockup :mangas="$this->searchResults" :is-row="false" />
            </section>

            <section class="mt-4">
                {{ $this->searchResults->links() }}
            </section>
        @elseif (!$readyToLoad)
            <section class="mt-4">
                <div class="flex gap-4 justify-between flex-wrap">
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
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_manga_library.webp') }}" alt="Empty Manga" title="Empty Manga">
                </x-picture>

                <p class="font-bold">{{ __('No Upcoming Manga') }}</p>

                <p class="text-sm text-secondary">{{ __('There are currently no upcoming manga.') }}</p>
            </section>
        @endif
    </div>
</main>
