<div>
    @if ($exploreCategoryCount)
        <section class="pt-5 pb-8 border-t-2" wire:init="loadExploreCategoryItems">
            <x-section-nav class="flex flex-nowrap justify-between mb-5">
                <x-slot:title>
                    {{ $exploreCategory->title }}
                </x-slot>

                <x-slot:description>
                    {{ $exploreCategory->description }}
                </x-slot>

                <x-slot:action>
                    <x-section-nav-link class="whitespace-nowrap" href="{{ $exploreCategory->secondary_slug ? url($exploreCategory->secondary_slug) : route('explore.details', $exploreCategory) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                <div class="flex flex-nowrap gap-4">
                    @foreach($exploreCategoryItems as $categoryItem)
                        @switch($exploreCategory->type)
                            @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                                <x-lockups.banner-lockup :anime="$categoryItem->model" />
                            @break
                            @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
                                <x-lockups.upcoming-lockup :anime="$categoryItem->model" />
                            @break
                            @case(\App\Enums\ExploreCategoryTypes::Shows)
                                @switch($exploreCategory->size)
                                    @case(\App\Enums\ExploreCategorySize::Large)
                                        <x-lockups.large-lockup :anime="$categoryItem->model" />
                                    @break
                                    @case(\App\Enums\ExploreCategorySize::Small)
                                        <x-lockups.small-lockup :anime="$categoryItem->model" />
                                    @break
                                    @case(\App\Enums\ExploreCategorySize::Video)
                                        <x-lockups.video-lockup :anime="$categoryItem->model" />
                                    @break
                                    @default
                                        @if (config('app.env') === 'local')
                                            {{ 'Unhandled size: ' . $exploreCategory->size }}
                                        @endif
                                @endswitch
                            @break
                            @case(\App\Enums\ExploreCategoryTypes::Genres)
                                <x-lockups.medium-lockup
                                    :href="route('genres.details', ['genre' => $categoryItem->model])"
                                    :title="$categoryItem->model->name"
                                    :backgroundColor="$categoryItem->model->color"
                                    :backgroundImage="$categoryItem->model->symbol_image_url ?? asset('images/static/icon/logo.webp')"
                                />
                            @break
                            @case(\App\Enums\ExploreCategoryTypes::Themes)
                                <x-lockups.medium-lockup
                                    :href="route('themes.details', ['theme' => $categoryItem->model])"
                                    :title="$categoryItem->model->name"
                                    :backgroundColor="$categoryItem->model->color"
                                    :backgroundImage="$categoryItem->model->symbol_image_url ?? asset('images/static/icon/logo.webp')"
                                />
                            @break
                            @case(\App\Enums\ExploreCategoryTypes::Characters)
                                <x-lockups.character-lockup :character="$categoryItem->model" />
                            @break
                            @case(\App\Enums\ExploreCategoryTypes::People)
                                <x-lockups.person-lockup :person="$categoryItem->model" />
                            @break
                            @case(\App\Enums\ExploreCategoryTypes::Songs)
                                <x-lockups.music-lockup :anime-song="$categoryItem->model" :show-episodes="false" :show-anime="true" />
                            @break
                            @default
                                @if (config('app.env') === 'local')
                                    {{ 'Unhandled type: ' . $exploreCategory->type }}
                                @endif
                        @endswitch
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
