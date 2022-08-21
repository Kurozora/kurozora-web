<div>
    @if ($exploreCategoryCount)
        <section class="pt-5 pb-8 border-t-2" wire:init="loadExploreCategoryItems">
            <x-section-nav class="flex flex-nowrap justify-between mb-5">
                <x-slot:title>
                    {{ $exploreCategory->title }}
                </x-slot:title>

                <x-slot:description>
                    {{ $exploreCategory->description }}
                </x-slot:description>

                <x-slot:action>
                    <x-section-nav-link class="whitespace-nowrap" href="{{ $exploreCategory->secondary_slug ? url($exploreCategory->secondary_slug) : route('explore.details', $exploreCategory) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            @switch($exploreCategory->type)
                @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                    <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap gap-4">
                        @foreach($exploreCategoryItems as $categoryItem)
                            <x-lockups.banner-lockup :anime="$categoryItem->model" />
                        @endforeach
                        </div>
                    </div>
                @break
                @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
                    <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap gap-4">
                        @foreach($exploreCategoryItems as $categoryItem)
                            <x-lockups.upcoming-lockup :anime="$categoryItem->model" />
                        @endforeach
                        </div>
                    </div>
                @break
                @case(\App\Enums\ExploreCategoryTypes::Shows)
                    @switch($exploreCategory->size)
                        @case(\App\Enums\ExploreCategorySize::Large)
                            <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                                <div class="flex flex-nowrap gap-4">
                                @foreach($exploreCategoryItems as $categoryItem)
                                    <x-lockups.large-lockup :anime="$categoryItem->model" />
                                @endforeach
                                </div>
                            </div>
                        @break
                        @case(\App\Enums\ExploreCategorySize::Small)
                            <x-rows.small-lockup :animes="$exploreCategoryItems" />
                        @break
                        @case(\App\Enums\ExploreCategorySize::Video)
                            <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                                <div class="flex flex-nowrap gap-4">
                                @foreach($exploreCategoryItems as $categoryItem)
                                    <x-lockups.video-lockup :anime="$categoryItem->model" />
                                @endforeach
                                </div>
                            </div>
                        @break
                        @default
                            @if (app()->environment('local'))
                                {{ 'Unhandled size: ' . $exploreCategory->size }}
                            @endif
                    @endswitch
                @break
                @case(\App\Enums\ExploreCategoryTypes::Genres)
                    <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap gap-4">
                        @foreach($exploreCategoryItems as $categoryItem)
                            <x-lockups.medium-lockup
                                :href="route('genres.details', ['genre' => $categoryItem->model])"
                                :title="$categoryItem->model->name"
                                :backgroundColor="$categoryItem->model->color"
                                :backgroundImage="$categoryItem->model->symbol_image_url ?? asset('images/static/icon/logo.webp')"
                            />
                        @endforeach
                        </div>
                    </div>
                @break
                @case(\App\Enums\ExploreCategoryTypes::Themes)
                    <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap gap-4">
                        @foreach($exploreCategoryItems as $categoryItem)
                            <x-lockups.medium-lockup
                                :href="route('themes.details', ['theme' => $categoryItem->model])"
                                :title="$categoryItem->model->name"
                                :backgroundColor="$categoryItem->model->color"
                                :backgroundImage="$categoryItem->model->symbol_image_url ?? asset('images/static/icon/logo.webp')"
                            />
                        @endforeach
                        </div>
                    </div>
                @break
                @case(\App\Enums\ExploreCategoryTypes::Characters)
                    <x-rows.character-lockup :characters="$exploreCategoryItems" />
                @break
                @case(\App\Enums\ExploreCategoryTypes::People)
                    <x-rows.person-lockup :people="$exploreCategoryItems" />
                @break
                @case(\App\Enums\ExploreCategoryTypes::Songs)
                    <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap gap-4">
                        @foreach($exploreCategoryItems as $categoryItem)
                            <x-lockups.music-lockup :anime-song="$categoryItem->model" :show-episodes="false" :show-anime="true" />
                        @endforeach
                        </div>
                    </div>
                @break
                @default
                    @if (app()->environment('local'))
                        {{ 'Unhandled type: ' . $exploreCategory->type }}
                    @endif
            @endswitch
        </section>
    @endif
</div>
