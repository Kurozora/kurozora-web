@php
    $gridClass = match($exploreCategory->type) {
        \App\Enums\ExploreCategoryTypes::Genres, \App\Enums\ExploreCategoryTypes::Themes => 'grid sm:grid-cols-2 lg:grid-cols-4 gap-4',
        \App\Enums\ExploreCategoryTypes::People, \App\Enums\ExploreCategoryTypes::Characters => 'grid grid-cols-3 gap-4 sm:grid-cols-4 sm:auto-cols-[unset] md:grid-cols-5 lg:grid-cols-7',
        \App\Enums\ExploreCategoryTypes::Songs => 'grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
        default => 'grid sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3 gap-4',
    };
@endphp

<main>
    <x-slot:title>
        {!! $exploreCategory->title !!}
    </x-slot>

    <x-slot:description>
        @switch($exploreCategory->type)
            @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
            @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
            @case(\App\Enums\ExploreCategoryTypes::Shows)
                {{ __('Explore the latest :x anime only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $exploreCategory->title]) }} {{ $exploreCategory->description }}
            @break
            @default
                {{ __('Explore the latest :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $exploreCategory->title]) }} {{ $exploreCategory->description }}
        @endswitch
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Explore') . ' ' . $exploreCategory->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Explore the latest :x category only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $exploreCategory->title]) }} {{ $exploreCategory->description }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <meta property="twitter:title" content="{{ $exploreCategory->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $exploreCategory->description ?? __('app.description') }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $exploreCategory->description ?? __('app.description') }}" />
        <link rel="canonical" href="{{ route('explore.details', $exploreCategory) }}">
    </x-slot>

    <x-slot:appArgument>
        explore/{{ $exploreCategory->id }}
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <p class="text-2xl font-bold">{{ $exploreCategory->title }}</p>
            <p class="text-gray-500 font-semibold">{{ $exploreCategory->description }}</p>
        </section>

        <section class="{{ $gridClass }}">
            @foreach($exploreCategoryItems as $categoryItem)
                @switch($exploreCategory->type)
                    @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                    @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
                    @case(\App\Enums\ExploreCategoryTypes::Shows)
                        <x-lockups.small-lockup :anime="$categoryItem->model" :isRow="false" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Genres)
                        <x-lockups.genre-lockup :genre="$categoryItem->model" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Themes)
                    <x-lockups.theme-lockup :theme="$categoryItem->model" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Characters)
                        <x-lockups.character-lockup :character="$categoryItem->model" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::People)
                        <x-lockups.person-lockup :person="$categoryItem->model" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::Songs)
                        <x-lockups.music-lockup :anime-song="$categoryItem->model" :show-episodes="false" :show-anime="true" :onMusicKitLoad="true" :is-row="false" />
                    @break
                    @default
                        @if (config('app.env') === 'local')
                            {{ 'Unhandled type: ' . $exploreCategory->type }}
                        @endif
                @endswitch
            @endforeach
        </section>
    </div>
</main>
