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
        <div class="relative mt-4 mb-4 pt-2 pr-2 pb-2 pl-2 bg-orange-500 text-white font-semibold rounded-lg">
            <a class="after:absolute after:inset-0" href="{{ config('services.patreon.url') }}" target="_blank">
                {{ __('Is Kurozora helpful? Please consider supporting me in keeping it online.') }}
            </a>
            <a target="_blank"></a>
        </div>

        <section class="relative mb-8">
            <a href="{{ config('social.discord.url') }}" target="_blank" class="after:absolute after:inset-0">
                <x-picture>
                    <img class="h-32 w-full object-cover object-center rounded-lg shadow-lg sm:h-44" src="{{ asset('images/static/banners/kurozora_art_challenge_2022.webp') }}"  alt="Kurozora Art Challenge 2022" />
                </x-picture>
            </a>
        </section>

        @foreach($this->exploreCategories as $key => $exploreCategory)
            @switch($exploreCategory->type)
            @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                <section class="pb-8">
                    <div class="flex flex-nowrap gap-4 mt-5 snap-x overflow-x-scroll no-scrollbar">
                        @foreach($exploreCategory->most_popular_shows()->explore_category_items as $categoryItem)
                            <x-lockups.banner-lockup :anime="$categoryItem->model" />
                        @endforeach
                    </div>
                </section>
                @break
            @default
                <livewire:components.explore-category-section :exploreCategory="$exploreCategory" />
            @endswitch
        @endforeach
    </div>
</main>
