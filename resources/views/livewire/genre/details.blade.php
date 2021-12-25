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
        <section class="relative mb-8 rounded-lg shadow-md overflow-hidden" style="{{ $backgroundColor }}">
            <picture class="flex justify-center">
                <img class="aspect-ratio-1-1 lazyload" width="250px" data-sizes="auto" data-src="{{ $genre->symbol_image_url ?? asset('images/static/icon/logo.webp') }}" alt="{{ $genre->name }} Symbol" title="{{ $genre->name }}">
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
                                @foreach($exploreCategory->most_popular_shows($genre)->explore_category_items as $categoryItem)
                                    <x-lockups.banner-lockup :anime="$categoryItem->model" />
                                @endforeach
                            </div>
                        </div>
                    </section>
                @break
                @default
                    <livewire:components.explore-category-section :exploreCategory="$exploreCategory" :genre="$genre" />
            @endswitch
        @endforeach
    </div>
</main>
