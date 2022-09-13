<main>
    <x-slot:title>
        {{ __('Explore') }}
    </x-slot:title>

    <x-slot:meta>
        <meta property="og:title" content="{{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('home') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        explore
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 pb-6 sm:px-6">
        <div class="relative mt-4 p-5 bg-orange-500 text-white font-semibold rounded-lg">
            <a class="after:absolute after:inset-0" href="{{ config('services.patreon.url') }}" target="_blank">
                {{ __('Is Kurozora helpful? Please consider supporting me in keeping it online.') }}
            </a>
            <a target="_blank"></a>
        </div>

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
