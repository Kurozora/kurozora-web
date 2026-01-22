@php
    $gridClass = match ($exploreCategory->type) {
        \App\Enums\ExploreCategoryTypes::Genres, \App\Enums\ExploreCategoryTypes::Themes => 'grid sm:grid-cols-2 lg:grid-cols-4 gap-4',
        default => '',
    };
@endphp

<main>
    <x-slot:title>
        {!! $exploreCategory->title !!}
    </x-slot:title>

    <x-slot:description>
        @switch($exploreCategory->type)
            @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
            @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
            @case(\App\Enums\ExploreCategoryTypes::NewShows)
            @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateShows)
            @case(\App\Enums\ExploreCategoryTypes::Shows)
                {{ __('Explore the latest :x anime only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $exploreCategory->title, 'y' => config('app.name')]) }} {{ $exploreCategory->description }}
                @break
            @case(\App\Enums\ExploreCategoryTypes::MostPopularLiteratures)
            @case(\App\Enums\ExploreCategoryTypes::UpcomingLiteratures)
            @case(\App\Enums\ExploreCategoryTypes::NewLiteratures)
            @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateLiteratures)
            @case(\App\Enums\ExploreCategoryTypes::Literatures)
                {{ __('Explore the latest :x manga only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $exploreCategory->title, 'y' => config('app.name')]) }} {{ $exploreCategory->description }}
                @break
            @case(\App\Enums\ExploreCategoryTypes::MostPopularGames)
            @case(\App\Enums\ExploreCategoryTypes::UpcomingGames)
            @case(\App\Enums\ExploreCategoryTypes::NewGames)
            @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateGames)
            @case(\App\Enums\ExploreCategoryTypes::Games)
                {{ __('Explore the latest :x games only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $exploreCategory->title, 'y' => config('app.name')]) }} {{ $exploreCategory->description }}
                @break
            @default
                {{ __('Explore the latest :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $exploreCategory->title, 'y' => config('app.name')]) }} {{ $exploreCategory->description }}
        @endswitch
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Explore') . ' ' . $exploreCategory->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Explore the latest :x category only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $exploreCategory->title, 'y' => config('app.name')]) }} {{ $exploreCategory->description }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <meta property="twitter:title" content="{{ $exploreCategory->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $exploreCategory->description ?? __('app.description') }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $exploreCategory->description ?? __('app.description') }}" />
        <link rel="canonical" href="{{ route('explore.details', $exploreCategory) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        explore/{{ $exploreCategory->id }}
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div class="flex gap-1 pl-4 pr-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ $exploreCategory->title }}</h1>

                    <p class="text-secondary">{{ $exploreCategory->description }}</p>
                </div>

                <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                </div>
            </div>
        </section>

        @if ($this->exploreCategoryItems->count())
            <section class="mt-4 xl:safe-area-inset">
                @switch($exploreCategory->type)
                    @case(\App\Enums\ExploreCategoryTypes::UpNextEpisodes)
                        <x-rows.episode-lockup :episodes="$this->exploreCategoryItems" :is-row="false" />
                        @break
                    @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
                    @case(\App\Enums\ExploreCategoryTypes::NewShows)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateShows)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyFinishedShows)
                    @case(\App\Enums\ExploreCategoryTypes::ContinuingShows)
                    @case(\App\Enums\ExploreCategoryTypes::ShowsSeason)
                    @case(\App\Enums\ExploreCategoryTypes::Shows)
                        <x-rows.small-lockup :animes="$this->exploreCategoryItems" :is-row="false" />
                        @break
                    @case(\App\Enums\ExploreCategoryTypes::MostPopularLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::NewLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyFinishedLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::ContinuingLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::LiteraturesSeason)
                    @case(\App\Enums\ExploreCategoryTypes::Literatures)
                        <x-rows.small-lockup :mangas="$this->exploreCategoryItems" :is-row="false" />
                        @break
                    @case(\App\Enums\ExploreCategoryTypes::MostPopularGames)
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingGames)
                    @case(\App\Enums\ExploreCategoryTypes::NewGames)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateGames)
                    @case(\App\Enums\ExploreCategoryTypes::GamesSeason)
                    @case(\App\Enums\ExploreCategoryTypes::Games)
                        <x-rows.small-lockup :games="$this->exploreCategoryItems" :is-row="false" />
                        @break
                    @case(\App\Enums\ExploreCategoryTypes::Genres)
                        <section class="{{ $gridClass }}">
                            @foreach ($this->exploreCategoryItems as $categoryItem)
                                <x-lockups.genre-lockup :genre="$categoryItem" />
                            @endforeach
                        </section>
                        @break
                    @case(\App\Enums\ExploreCategoryTypes::Themes)
                        <section class="{{ $gridClass }}">
                            @foreach ($this->exploreCategoryItems as $categoryItem)
                                <x-lockups.theme-lockup :theme="$categoryItem" />
                            @endforeach
                        </section>
                        @break
                    @case(\App\Enums\ExploreCategoryTypes::Characters)
                        <x-rows.character-lockup :characters="$this->exploreCategoryItems" :is-row="false" />
                        @break
                    @case(\App\Enums\ExploreCategoryTypes::People)
                        <x-rows.person-lockup :people="$this->exploreCategoryItems" :is-row="false" />
                        @break
                    @case(\App\Enums\ExploreCategoryTypes::Songs)
                        <x-rows.music-lockup :media-songs="$this->exploreCategoryItems" :show-episodes="false" :show-model="true" :is-row="false" />
                        @break
                    @case(\App\Enums\ExploreCategoryTypes::ReCAP)
                        <x-rows.recap-lockup :recaps="$this->exploreCategoryItems" :is-row="false" />
                        @break
                    @default
                        @if (app()->isLocal())
                            {{ 'Unhandled type: ' . $exploreCategory->type }}
                        @endif
                @endswitch
            </section>
        @else (!$readyToLoad)
            <section class="mt-4 xl:safe-area-inset">
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
