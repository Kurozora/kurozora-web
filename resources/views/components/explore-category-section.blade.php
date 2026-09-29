@props(['exploreCategory', 'genre' => null, 'theme' => null, 'items' => null])

@php
    $exploreCategoryItems = $items ?? $exploreCategory->items($genre ?? $theme);
@endphp

<div
    data-section
    @if ($exploreCategory->type == \App\Enums\ExploreCategoryTypes::UpNextEpisodes)
        data-section-refresh-on="refresh-up-next-section"
        data-section-url="{{ route('explore.section', ['exploreCategory' => $exploreCategory, 'genre' => $genre?->slug, 'theme' => $theme?->slug], false) }}"
    @endif
>
    <div>
        @if ($exploreCategoryItems->count())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav class="flex flex-nowrap justify-between mb-5 pt-4 pl-4 pr-4">
                        <x-slot:title>
                            @switch($exploreCategory->type)
                            @case(\App\Enums\ExploreCategoryTypes::ShowsSeason)
                            @case(\App\Enums\ExploreCategoryTypes::LiteraturesSeason)
                            @case(\App\Enums\ExploreCategoryTypes::GamesSeason)
                                {{ season_of_year(today()->addDays(3))->key . ' ' . today()->addDays(3)->year }}
                            @break
                            @default
                                {{ $exploreCategory->title }}
                            @endswitch
                        </x-slot:title>

                        <x-slot:description>
                            {{ $exploreCategory->description }}
                        </x-slot:description>

                        <x-slot:action>
                            <x-spinner :wire-loading-enabled="false" data-section-spinner class="hidden" />

                            @hasrole('superAdmin')
                                <x-button data-section-refresh="{{ route('explore.section', ['exploreCategory' => $exploreCategory, 'genre' => $genre?->slug, 'theme' => $theme?->slug], false) }}">{{ __('Refresh') }}</x-button>
                            @endhasrole

                            @switch($exploreCategory->type)
                            @case(\App\Enums\ExploreCategoryTypes::ShowsSeason)
                                <x-section-nav-link href="{{ route('anime.seasons.year.season', [today()->addDays(3)->year, season_of_year(today()->addDays(3))->key]) }}">{{ __('See All') }}</x-section-nav-link>
                            @break
                            @case(\App\Enums\ExploreCategoryTypes::LiteraturesSeason)
                                <x-section-nav-link href="{{ route('manga.seasons.year.season', [today()->addDays(3)->year, season_of_year(today()->addDays(3))->key]) }}">{{ __('See All') }}</x-section-nav-link>
                            @break
                            @case(\App\Enums\ExploreCategoryTypes::GamesSeason)
                                <x-section-nav-link href="{{ route('games.seasons.year.season', [today()->addDays(3)->year, season_of_year(today()->addDays(3))->key]) }}">{{ __('See All') }}</x-section-nav-link>
                            @break
                            @default
                                <x-section-nav-link href="{{ $exploreCategory->secondary_slug ? url($exploreCategory->secondary_slug) : route('explore.details', $exploreCategory) }}">{{ __('See All') }}</x-section-nav-link>
                            @endswitch
                        </x-slot:action>
                    </x-section-nav>
                </div>

                @switch($exploreCategory->type)
                    @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                            @foreach ($exploreCategoryItems as $show)
                                <x-lockups.banner-lockup :anime="$show" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::UpNextEpisodes)
                        <x-rows.episode-lockup :episodes="$exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                            @foreach ($exploreCategoryItems as $shows)
                                <x-lockups.upcoming-lockup :anime="$shows" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::NewShows)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateShows)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyFinishedShows)
                    @case(\App\Enums\ExploreCategoryTypes::ContinuingShows)
                    @case(\App\Enums\ExploreCategoryTypes::ShowsSeason)
                        <x-rows.small-lockup :animes="$exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Shows)
                        @switch($exploreCategory->size)
                            @case(\App\Enums\ExploreCategorySize::Large)
                                <div class="flex flex-nowrap gap-4 pl-4 pr-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                                    @foreach ($exploreCategoryItems as $categoryItem)
                                        <x-lockups.large-lockup :anime="$categoryItem->model" />
                                    @endforeach
                                </div>
                            @break
                            @case(\App\Enums\ExploreCategorySize::Small)
                                <x-rows.small-lockup :animes="$exploreCategoryItems" />
                            @break
                            @case(\App\Enums\ExploreCategorySize::Video)
                                <x-rows.video-lockup :animes="$exploreCategoryItems" />
                            @break
                            @default
                                @if (app()->isLocal())
                                    {{ 'Unhandled size: ' . $exploreCategory->size }}
                                @endif
                        @endswitch
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingLiteratures)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                            @foreach ($exploreCategoryItems as $literature)
                                <x-lockups.upcoming-lockup :manga="$literature" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Literatures)
                    @case(\App\Enums\ExploreCategoryTypes::NewLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyFinishedLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::ContinuingLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::LiteraturesSeason)
                        <x-rows.small-lockup :mangas="$exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingGames)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                            @foreach ($exploreCategoryItems as $game)
                                <x-lockups.upcoming-lockup :game="$game" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Games)
                    @case(\App\Enums\ExploreCategoryTypes::NewGames)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateGames)
                    @case(\App\Enums\ExploreCategoryTypes::GamesSeason)
                        <x-rows.small-lockup :games="$exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Genres)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                            @foreach ($exploreCategoryItems as $genre)
                                <x-lockups.medium-lockup :genre="$genre" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Themes)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                            @foreach ($exploreCategoryItems as $theme)
                                <x-lockups.medium-lockup :theme="$theme" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Characters)
                        <x-rows.character-lockup :characters="$exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::People)
                        <x-rows.person-lockup :people="$exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Songs)
                        <x-rows.music-lockup :media-songs="$exploreCategoryItems" :show-episodes="false" :show-model="true" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::ReCAP)
                        <x-rows.recap-lockup :recaps="$exploreCategoryItems" />
                    @break
                    @default
                        @if (app()->isLocal())
                            {{ 'Unhandled type: ' . $exploreCategory->type }}
                        @endif
                @endswitch
            </section>
        @endif
    </div>
</div>
