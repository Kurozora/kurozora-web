<div>
    <div wire:init="loadSection">
        @if ($this->exploreCategoryItems->count())
            <section class="pt-5 pb-8">
                <x-section-nav class="flex flex-nowrap justify-between mb-5 pl-4 pr-4 sm:px-6">
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
                        <x-spinner />

                        @hasrole('superAdmin')
                            <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                        @endhasrole

                        <x-section-nav-link class="whitespace-nowrap" href="{{ $exploreCategory->secondary_slug ? url($exploreCategory->secondary_slug) : route('explore.details', $exploreCategory) }}">{{ __('See All') }}</x-section-nav-link>
                    </x-slot:action>
                </x-section-nav>

                @switch($exploreCategory->type)
                    @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 sm:px-6 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $show)
                                <x-lockups.banner-lockup :anime="$show" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::UpNextEpisodes)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 sm:px-6 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                            <livewire:components.episode.up-next-section />
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 sm:px-6 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $shows)
                                <x-lockups.upcoming-lockup :anime="$shows" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::NewShows)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateShows)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyFinishedShows)
                    @case(\App\Enums\ExploreCategoryTypes::ContinuingShows)
                    @case(\App\Enums\ExploreCategoryTypes::ShowsSeason)
                        <x-rows.small-lockup :animes="$this->exploreCategoryItems" class="pl-4 pr-4 sm:px-6" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Shows)
                        @switch($exploreCategory->size)
                            @case(\App\Enums\ExploreCategorySize::Large)
                                <div class="flex flex-nowrap gap-4 pl-4 pr-4 sm:px-6 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                                    @foreach($this->exploreCategoryItems as $categoryItem)
                                        <x-lockups.large-lockup :anime="$categoryItem->model" />
                                    @endforeach
                                </div>
                            @break
                            @case(\App\Enums\ExploreCategorySize::Small)
                                <x-rows.small-lockup :animes="$this->exploreCategoryItems" class="pl-4 pr-4 sm:px-6" />
                            @break
                            @case(\App\Enums\ExploreCategorySize::Video)
                                <div class="flex overflow-x-scroll no-scrollbar">
                                    <div class="flex flex-nowrap gap-4 pl-4 pr-4 sm:px-6">
                                    @foreach($this->exploreCategoryItems as $anime)
                                        <x-lockups.video-lockup :anime="$anime" />
                                    @endforeach
                                </div>
                            @break
                            @default
                                @if (app()->isLocal())
                                    {{ 'Unhandled size: ' . $exploreCategory->size }}
                                @endif
                        @endswitch
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingLiteratures)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 sm:px-6 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $literature)
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
                        <x-rows.small-lockup :mangas="$this->exploreCategoryItems" class="pl-4 pr-4 sm:px-6" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingGames)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 sm:px-6 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $game)
                                <x-lockups.upcoming-lockup :game="$game" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Games)
                    @case(\App\Enums\ExploreCategoryTypes::NewGames)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateGames)
                    @case(\App\Enums\ExploreCategoryTypes::GamesSeason)
                        <x-rows.small-lockup :games="$this->exploreCategoryItems" class="pl-4 pr-4 sm:px-6" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Genres)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 sm:px-6 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $genre)
                                <x-lockups.medium-lockup :genre="$genre" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Themes)
                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 sm:px-6 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $theme)
                                <x-lockups.medium-lockup :theme="$theme" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Characters)
                        <x-rows.character-lockup :characters="$this->exploreCategoryItems" class="pl-4 pr-4 sm:px-6" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::People)
                        <x-rows.person-lockup :people="$this->exploreCategoryItems" class="pl-4 pr-4 sm:px-6" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Songs)
                        <x-rows.music-lockup :media-songs="$this->exploreCategoryItems" :show-episodes="false" :show-model="true" class="pl-4 pr-4 sm:px-6" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::ReCAP)
                        <x-rows.recap-lockup :recaps="$this->exploreCategoryItems" class="pl-4 pr-4 sm:px-6" />
                    @break
                    @default
                        @if (app()->isLocal())
                            {{ 'Unhandled type: ' . $exploreCategory->type }}
                        @endif
                @endswitch
            </section>
        @endif
    </div>

    @if (!$readyToLoad)
        <section  class="pt-5 pb-8 border-t-2">
            <div style="height: 314px">
                <div class="flex gap-2 justify-between mb-5">
                    <div>
                        <p class="bg-gray-200" style="width: 168px; height: 28px"></p>
                        <p class="bg-gray-200" style="width: 228px; height: 22px"></p>
                    </div>

                    <div class="flex flex-wrap gap-2 justify-end"></div>
                </div>

                <div class="flex gap-4 justify-between snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                </div>
            </div>
        </section>
    @endif
</div>
