@php
    $gridClass = match($exploreCategory->type) {
        \App\Enums\ExploreCategoryTypes::Genres, \App\Enums\ExploreCategoryTypes::Themes => 'grid sm:grid-cols-2 lg:grid-cols-4 gap-4',
        default => '',
    };
@endphp

<main>
    <x-slot:title>
        {!! $exploreCategory->title !!}
    </x-slot:title>

    <x-slot:description>
        @switch($exploreCategory->type)
            @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
            @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
            @case(\App\Enums\ExploreCategoryTypes::NewShows)
            @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateShows)
            @case(\App\Enums\ExploreCategoryTypes::Shows)
                {{ __('Explore the latest :x anime only on Kurozora, the largest, free online anime, manga, music & game database in the world.', ['x' => $exploreCategory->title]) }} {{ $exploreCategory->description }}
            @break
            @default
                {{ __('Explore the latest :x only on Kurozora, the largest, free online anime, manga, music & game database in the world.', ['x' => $exploreCategory->title]) }} {{ $exploreCategory->description }}
        @endswitch
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Explore') . ' ' . $exploreCategory->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Explore the latest :x category only on Kurozora, the largest, free online anime, manga, music & game database in the world.', ['x' => $exploreCategory->title]) }} {{ $exploreCategory->description }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <meta property="twitter:title" content="{{ $exploreCategory->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $exploreCategory->description ?? __('app.description') }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $exploreCategory->description ?? __('app.description') }}" />
        <link rel="canonical" href="{{ route('explore.details', $exploreCategory) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        explore/{{ $exploreCategory->id }}
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <p class="text-2xl font-bold">{{ $exploreCategory->title }}</p>
            <p class="text-gray-500 font-semibold">{{ $exploreCategory->description }}</p>
        </section>

        @switch($exploreCategory->type)
            @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
            @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
            @case(\App\Enums\ExploreCategoryTypes::NewShows)
            @case(\App\Enums\ExploreCategoryTypes::RecentlyUpdateShows)
            @case(\App\Enums\ExploreCategoryTypes::RecentlyFinishedShows)
            @case(\App\Enums\ExploreCategoryTypes::AnimeContinuing)
            @case(\App\Enums\ExploreCategoryTypes::AnimeSeason)
            @case(\App\Enums\ExploreCategoryTypes::Shows)
                <x-rows.small-lockup :animes="$this->exploreCategoryItems" :is-row="false" />
            @break
            @case(\App\Enums\ExploreCategoryTypes::Genres)
                <section class="{{ $gridClass }}">
                    @foreach($this->exploreCategoryItems as $categoryItem)
                        <x-lockups.genre-lockup :genre="$categoryItem" />
                    @endforeach
                </section>
            @break
            @case(\App\Enums\ExploreCategoryTypes::Themes)
                <section class="{{ $gridClass }}">
                    @foreach($this->exploreCategoryItems as $categoryItem)
                        <x-lockups.theme-lockup :theme="$categoryItem" />
                    @endforeach
                </section>
            @break
            @case(\App\Enums\ExploreCategoryTypes::Characters)
                <x-rows.character-lockup :characters="$this->exploreCategoryItems" :is-row="false" />
            @break
            @case(\App\Enums\ExploreCategoryTypes::People)
                <x-rows.person-lockup :people="$this->exploreCategoryItems" :is-row="false" />
            @break
            @case(\App\Enums\ExploreCategoryTypes::Songs)
                <x-rows.music-lockup :anime-songs="$this->exploreCategoryItems" :show-episodes="false" :show-anime="true" :is-row="false" />
            @break
            @default
                @if (app()->environment('local'))
                    {{ 'Unhandled type: ' . $exploreCategory->type }}
                @endif
        @endswitch
    </div>
</main>
