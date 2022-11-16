@php
    $backgroundColor = match ($theme->color) {
        '#ffffff' => 'background: linear-gradient(-45deg, rgb(56, 62, 87) 22%, rgb(98, 112, 170) 88%)',
        default => 'background-color: ' . $theme->color
    };
@endphp

<main>
    <x-slot:title>
        {{ $theme->name }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of :x anime only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $theme->name]) }} {{ $theme->description }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $theme->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of :x anime only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $theme->name]) }} {{ $theme->description }}" />
        <meta property="og:image" content="{{ $theme->getFirstMediaFullUrl(\App\Enums\MediaCollection::Symbol()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('themes.details', $theme) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        theme/{{ $theme->id }}
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="relative mb-8 rounded-lg shadow-md overflow-hidden" style="{{ $backgroundColor }}">
            <picture class="flex justify-center">
                <img class="aspect-square lazyload" width="250px" data-sizes="auto" data-src="{{ $theme->getFirstMediaFullUrl(\App\Enums\MediaCollection::Symbol()) ?? asset('images/static/icon/logo.webp') }}" alt="{{ $theme->name }} Symbol" title="{{ $theme->name }}">
            </picture>

            <div class="pr-3 pl-3 pt-4 pb-4 bg-black/30 backdrop-blur text-center">
                <p class="text-white font-bold leading-tight line-clamp-1">{{ $theme->name }}</p>
                <p class="text-sm text-white/90 leading-tight">{{ $theme->description }}</p>
            </div>
        </section>

        @foreach($exploreCategories as $key => $exploreCategory)
            @switch($exploreCategory->type)
                @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                    <section class="pb-8">
                        <div class="flex overflow-x-scroll no-scrollbar">
                            <div class="flex flex-nowrap gap-4">
                                @foreach($exploreCategory->most_popular_shows($theme)->explore_category_items as $categoryItem)
                                    <x-lockups.banner-lockup :anime="$categoryItem->model" />
                                @endforeach
                            </div>
                        </div>
                    </section>
                @break
                @default
                    <livewire:components.explore-category-section :exploreCategory="$exploreCategory" :theme="$theme" />
            @endswitch
        @endforeach
    </div>
</main>
