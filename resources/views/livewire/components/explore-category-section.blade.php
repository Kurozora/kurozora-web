<div>
    @if ($exploreCategoryCount)
        <section class="pt-5 pb-8 border-t-2" wire:init="loadExploreCategoryItems">
            <x-section-nav class="flex flex-nowrap justify-between mb-5">
                <x-slot:title>
                    @switch($exploreCategory->type)
                    @case(\App\Enums\ExploreCategoryTypes::ShowsSeason)
                    @case(\App\Enums\ExploreCategoryTypes::LiteraturesSeason)
                    @case(\App\Enums\ExploreCategoryTypes::GamesSeason)
                        {{ season_of_year()->key . ' ' . now()->year }}
                    @break
                    @default
                        {{ $exploreCategory->title }}
                    @endswitch
                </x-slot:title>

                <x-slot:description>
                    {{ $exploreCategory->description }}
                </x-slot:description>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link class="whitespace-nowrap" href="{{ $exploreCategory->secondary_slug ? url($exploreCategory->secondary_slug) : route('explore.details', $exploreCategory) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            @if($isInit)
                @switch($exploreCategory->type)
                    @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                        <div class="flex flex-nowrap gap-4 snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $categoryItem)
                                <x-lockups.banner-lockup :anime="$categoryItem->model" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
                        <div class="flex flex-nowrap gap-4 snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $categoryItem)
                                <x-lockups.upcoming-lockup :anime="$categoryItem->model" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::NewShows)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateShows)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyFinishedShows)
                    @case(\App\Enums\ExploreCategoryTypes::ContinuingShows)
                    @case(\App\Enums\ExploreCategoryTypes::ShowsSeason)
                        <x-rows.small-lockup :animes="$this->exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Shows)
                        @switch($exploreCategory->size)
                            @case(\App\Enums\ExploreCategorySize::Large)
                                <div class="flex flex-nowrap gap-4 snap-x overflow-x-scroll no-scrollbar">
                                    @foreach($this->exploreCategoryItems as $categoryItem)
                                        <x-lockups.large-lockup :anime="$categoryItem->model" />
                                    @endforeach
                                </div>
                            @break
                            @case(\App\Enums\ExploreCategorySize::Small)
                                <x-rows.small-lockup :animes="$this->exploreCategoryItems" />
                            @break
                            @case(\App\Enums\ExploreCategorySize::Video)
                                <div class="flex overflow-x-scroll no-scrollbar">
                                    <div class="flex flex-nowrap gap-4">
                                    @foreach($this->exploreCategoryItems as $categoryItem)
                                        <x-lockups.video-lockup :anime="$categoryItem->model" />
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
                        <div class="flex flex-nowrap gap-4 snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $categoryItem)
                                <x-lockups.upcoming-lockup :manga="$categoryItem->model" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Literatures)
                    @case(\App\Enums\ExploreCategoryTypes::NewLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyFinishedLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::ContinuingLiteratures)
                    @case(\App\Enums\ExploreCategoryTypes::LiteraturesSeason)
                        <x-rows.small-lockup :mangas="$this->exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingGames)
                        <div class="flex flex-nowrap gap-4 snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $categoryItem)
                                <x-lockups.upcoming-lockup :game="$categoryItem->model" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Games)
                    @case(\App\Enums\ExploreCategoryTypes::NewGames)
                    @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateGames)
                    @case(\App\Enums\ExploreCategoryTypes::GamesSeason)
                        <x-rows.small-lockup :games="$this->exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Genres)
                        <div class="flex flex-nowrap gap-4 snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $categoryItem)
                                <x-lockups.medium-lockup :genre="$categoryItem->model" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Themes)
                        <div class="flex flex-nowrap gap-4 snap-x overflow-x-scroll no-scrollbar">
                            @foreach($this->exploreCategoryItems as $categoryItem)
                                <x-lockups.medium-lockup :theme="$categoryItem->model" />
                            @endforeach
                        </div>
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Characters)
                        <x-rows.character-lockup :characters="$this->exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::People)
                        <x-rows.person-lockup :people="$this->exploreCategoryItems" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Songs)
                        <x-rows.music-lockup :media-songs="$this->exploreCategoryItems" :show-episodes="false" :show-model="true" />
                    @break
                    @default
                        @if (app()->isLocal())
                            {{ 'Unhandled type: ' . $exploreCategory->type }}
                        @endif
                @endswitch
            @endif
        </section>
    @endif
</div>
