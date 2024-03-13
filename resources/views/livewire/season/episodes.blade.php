<main>
    <x-slot:title>
        {{ __(':x Episodes', ['x' => $season->title]) }} | {!! $season->anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ $season->synopsis ?? __('Discover the extensive list of :x episodes only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $season->anime->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x Episodes', ['x' => $season->title]) }} | {{ $season->anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $season->synopsis ?? __('Discover the extensive list of :x episodes on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $season->anime->title]) }}" />
        <meta property="og:image" content="{{ $season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $season->duration }}" />
        <meta property="video:release_date" content="{{ $season->started_at?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('seasons.episodes', $season) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        seasons/{{ $season->id }}/episodes
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x Episodes', ['x' => $season->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center gap-2 w-full">
                        <livewire:season.watch-button :season="$season" />

                        <x-nova-link :resource="\App\Nova\Season::class" :model="$season">
                            @svg('pencil', 'fill-current', ['width' => '44'])
                        </x-nova-link>
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomEpisode">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random episode', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        @if ($this->searchResults->count())
            <x-rows.episode-lockup :episodes="$this->searchResults" :is-row="false" />

            <section class="mt-4">
                {{ $this->searchResults->links() }}
            </section>
        @elseif (!$readyToLoad)
            <section class="mt-4">
                <div class="flex gap-4 justify-between flex-wrap">
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @else
            <section class="flex flex-col items-center mt-4 text-center">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="Empty Episodes" title="Empty Episodes">
                </x-picture>

                <p class="font-bold">{{ __('Episodes Not Found') }}</p>

                <p class="text-sm text-gray-500">{{ __('No episodes found in the selected season.') }}</p>
            </section>
        @endif
    </div>
</main>
