<main>
    <x-slot:title>
        {{ __('Continuing this Season') }} | {{ __('Anime') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse the anime continuing this season. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Continuing this Season') }} | {{ __('Anime') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse the anime continuing this season. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('anime.continuing.index') }}">
    </x-slot:meta>

    <div class="py-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Continuing Anime') }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomAnime">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random upcoming anime', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        @if (!empty($this->searchResults))
            @if (!empty($this->searchResults->total()))
                <section class="mt-4">
                    <x-rows.small-lockup :animes="$this->searchResults" :is-row="false" />
                </section>

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->searchResults->links() }}
                </div>
            @else
                <section class="flex flex-col items-center mt-4 text-center">
                    <x-picture>
                        <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="Empty Anime" title="Empty Anime">
                    </x-picture>

                    <p class="font-bold">{{ __('No Continuing Anime') }}</p>

                    <p class="text-sm text-secondary">{{ __('There are currently no ongoing anime.') }}</p>
                </section>
            @endif
        @elseif (!$readyToLoad)
            <section>
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
