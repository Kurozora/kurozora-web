@php
    $gridClass = match($exploreCategory->type) {
        \App\Enums\ExploreCategoryTypes::Genres => 'grid sm:grid-cols-2 lg:grid-cols-4 gap-4',
        \App\Enums\ExploreCategoryTypes::People, \App\Enums\ExploreCategoryTypes::Characters => 'grid grid-cols-3 gap-4 sm:grid-cols-4 sm:auto-cols-[unset] md:grid-cols-5 lg:grid-cols-7',
        default => 'grid sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3 gap-4',
    };
@endphp

<main>
    <x-slot name="title">
        {{ $exploreCategory->title }} — {{ config('app.name') }}
    </x-slot>

    <x-slot name="description">
        @switch($exploreCategory->type)
            @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
            @case(\App\Enums\ExploreCategoryTypes::UpcomingShows)
            @case(\App\Enums\ExploreCategoryTypes::Shows)
                {{ __('Explore the latest :x anime only on Kurozora, the largest, free online anime and manga database in the world.', ['x' => $exploreCategory->title]) }} {{ $exploreCategory->description }}
            @break
            @default
                {{ __('Explore the latest :x only on Kurozora, the largest, free online anime and manga database in the world.', ['x' => $exploreCategory->title]) }} {{ $exploreCategory->description }}
        @endswitch
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Explore') . ' ' . $exploreCategory->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Explore the latest :x category only on Kurozora, the largest, free online anime and manga database in the world.', ['x' => $exploreCategory->title]) }} {{ $exploreCategory->description }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <meta property="twitter:title" content="{{ $exploreCategory->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $exploreCategory->description ?? __('app.description') }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $exploreCategory->description ?? __('app.description') }}" />
        <link rel="canonical" href="{{ route('explore.details', $exploreCategory) }}">
    </x-slot>

    <x-slot name="appArgument">
        explore/{{ $exploreCategory->id }}
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <p class="text-2xl font-bold">{{ $exploreCategory->title }}</p>
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
                    @case(\App\Enums\ExploreCategoryTypes::Characters)
                        <x-lockups.character-lockup :character="$categoryItem->model" />
                    @break
                    @case(\App\Enums\ExploreCategoryTypes::People)
                        <x-lockups.person-lockup :person="$categoryItem->model" />
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
