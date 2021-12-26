<main>
    <x-slot name="title">
        {{ __('Explore') }} — {{ config('app.name') }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Explore') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot>

    <x-slot name="appArgument">
        explore
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 pb-6 sm:px-6">
        @foreach($exploreCategories as $key => $exploreCategory)
            @switch($exploreCategory->type)
            @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                <section class="pb-8">
                    <div class="flex overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap gap-4">
                            @foreach($exploreCategory->most_popular_shows()->explore_category_items as $categoryItem)
                                <x-lockups.banner-lockup :anime="$categoryItem->model" />
                            @endforeach
                        </div>
                    </div>
                </section>
                @break
            @default
                <livewire:components.explore-category-section :exploreCategory="$exploreCategory" />
            @endswitch
        @endforeach
    </div>
</main>
