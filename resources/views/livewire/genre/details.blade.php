@php
    $backgroundColor = match ($genre->color) {
        '#ffffff' => 'background: linear-gradient(-45deg, rgb(56, 62, 87) 22%, rgb(98, 112, 170) 88%)',
        default => 'background-color: ' . $genre->color
    };
@endphp

<main>
    <x-slot name="title">
        {{ $genre->name }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $genre->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $genre->description }}" />
        <meta property="og:image" content="{{ $genre->symbol_image_url ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot>

    <x-slot name="appArgument">
        genre/{{ $genre->id }}
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-8 rounded-lg shadow-md overflow-hidden" style="{{ $backgroundColor }}">
            <picture class="flex justify-center">
                <img class="lazyload" width="250px" data-sizes="auto" data-src="{{ $genre->symbol_image_url ?? asset('images/static/icon/logo.webp') }}" alt="{{ $genre->name }} Symbol" title="{{ $genre->name }}">
            </picture>

            <div class="p-3 py-5 bg-black/30 backdrop-blur text-center">
                <p class="text-white font-bold leading-tight line-clamp-1">{{ $genre->name }}</p>
                <p class="text-sm text-white/90 leading-tight">{{ $genre->description }}</p>
            </div>
        </section>

        @foreach($exploreCategories as $key => $exploreCategory)
            @switch($exploreCategory->type)
            @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                <section class="pb-8">
                    <div class="flex overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap gap-4">
                            @foreach($exploreCategory->explore_category_items as $categoryItem)
                                <x-lockups.banner-lockup :anime="$categoryItem->model" />
                            @endforeach
                        </div>
                    </div>
                </section>
                @break
            @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
                <section class="pt-5 pb-8 border-t-2">
                    <x-section-nav class="flex flex-nowrap justify-between mb-5">
                        <x-slot name="title">
                            {{ $exploreCategory->title }}
                        </x-slot>

                        <x-slot name="action">
                            <x-simple-link href="#" :disabled="true">{{ __('See All') }}</x-simple-link>
                        </x-slot>
                    </x-section-nav>

                    <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap gap-4">
                            @foreach($exploreCategory->upcoming_shows()->explore_category_items as $categoryItem)
                                <x-lockups.upcoming-lockup :anime="$categoryItem->model" />
                            @endforeach
                        </div>
                    </div>
                </section>
                @break
            @case(\App\Enums\ExploreCategoryTypes::Shows)
                <section class="pt-5 pb-8 border-t-2">
                    <x-section-nav class="flex flex-nowrap justify-between mb-5">
                        <x-slot name="title">
                            {{ $exploreCategory->title }}
                        </x-slot>

                        <x-slot name="action">
                            <x-simple-link href="#" :disabled="true">{{ __('See All') }}</x-simple-link>
                        </x-slot>
                    </x-section-nav>

                    @switch($exploreCategory->size)
                    @case(\App\Enums\ExploreCategorySize::Large)
                        <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                            <div class="flex flex-nowrap gap-4">
                                @foreach($exploreCategory->explore_category_items as $categoryItem)
                                    <x-lockups.large-lockup :anime="$categoryItem->model" />
                                @endforeach
                            </div>
                        </div>
                        @break
                    @case(\App\Enums\ExploreCategorySize::Small)
                        <div class="grid grid-flow-col-dense mt-5 gap-4 overflow-x-scroll no-scrollbar">
                            @foreach($exploreCategory->explore_category_items as $categoryItem)
                                <x-lockups.small-lockup :anime="$categoryItem->model" />
                            @endforeach
                        </div>
                        @break
                    @case(\App\Enums\ExploreCategorySize::Video)
                        <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                            <div class="flex flex-nowrap gap-4">
                                @foreach($exploreCategory->explore_category_items as $categoryItem)
                                    <x-lockups.video-lockup :anime="$categoryItem->model" />
                                @endforeach
                            </div>
                        </div>
                        @break
                    @default
                        @if (config('app.env') === 'local')
                            {{ 'Unhandled size: ' . $exploreCategory->size }}
                        @endif
                    @endswitch
                </section>
                @break
            @case(\App\Enums\ExploreCategoryTypes::Genres)
                <section class="pt-5 pb-8 border-t-2">
                    <x-section-nav class="flex flex-nowrap justify-between mb-5">
                        <x-slot name="title">
                            {{ $exploreCategory->title }}
                        </x-slot>

                        <x-slot name="action">
                            <x-simple-link href="{{ url('/genres') }}">{{ __('See All') }}</x-simple-link>
                        </x-slot>
                    </x-section-nav>

                    <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap gap-4">
                            @foreach($exploreCategory->explore_category_items as $categoryItem)
                                <x-lockups.medium-lockup
                                    :href="route('genres.details', ['genre' => $categoryItem->model])"
                                    :title="$categoryItem->model->name"
                                    :backgroundColor="$categoryItem->model->color"
                                    :backgroundImage="$categoryItem->model->symbol_image_url ?? asset('images/static/icon/logo.webp')"
                                />
                            @endforeach
                        </div>
                    </div>
                </section>
                @break
            @case(\App\Enums\ExploreCategoryTypes::Characters)
                @if (\App\Models\Character::bornToday()->count() != 0)
                    <section class="pt-5 pb-8 border-t-2">
                        <x-section-nav class="flex flex-nowrap justify-between mb-5">
                            <x-slot name="title">
                                {{ $exploreCategory->title }}
                            </x-slot>

                            <x-slot name="action">
                                <x-simple-link href="#" :disabled="true">{{ __('See All') }}</x-simple-link>
                            </x-slot>
                        </x-section-nav>

                        <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                            <div class="flex flex-nowrap gap-4">
                                @foreach($exploreCategory->charactersBornToday()->explore_category_items as $categoryItem)
                                    <x-lockups.character-lockup :character="$categoryItem->model" />
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif
                @break
            @case(\App\Enums\ExploreCategoryTypes::People)
                @if (\App\Models\Person::bornToday()->count() != 0)
                    <section class="pt-5 pb-8 border-t-2">
                        <x-section-nav class="flex flex-nowrap justify-between mb-5">
                            <x-slot name="title">
                                {{ $exploreCategory->title }}
                            </x-slot>

                            <x-slot name="action">
                                <x-simple-link href="#" :disabled="true">{{ __('See All') }}</x-simple-link>
                            </x-slot>
                        </x-section-nav>

                        <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                            <div class="flex flex-nowrap gap-4">
                                @foreach($exploreCategory->peopleBornToday()->explore_category_items as $categoryItem)
                                    <x-lockups.person-lockup :person="$categoryItem->model" />
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif
                @break
            @default
                @if (config('app.env') === 'local')
                    {{ 'Unhandled type: ' . $exploreCategory->type }}
                @endif
            @endswitch
        @endforeach
    </div>
</main>
