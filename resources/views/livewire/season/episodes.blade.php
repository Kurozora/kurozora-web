<main>
    <x-slot:title>
        {{ __('Episodes') }} | {!! $season->title !!}
    </x-slot>

    <x-slot:description>
        {{ $season->synopsis ?? __('Discover the extensive list of :x episodes only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $season->anime->title]) }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Episodes') }} | {{ $season->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $season->synopsis ?? __('Discover the extensive list of :x episodes on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $season->anime->title]) }}" />
        <meta property="og:image" content="{{ $season->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $season->duration }}" />
        <meta property="video:release_date" content="{{ $season->first_aired }}" />
        <link rel="canonical" href="{{ route('seasons.episodes', $season) }}">
    </x-slot>

    <x-slot:appArgument>
        seasons/{{ $season->id }}/episodes
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __(':x Episodes', ['x' => $season->title]) }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomEpisode">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random episode', 'width' => '28'])
                        </x-square-button>
                    </x-slot>
                </x-search-bar>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3">
            @foreach($this->searchResults as $episode)
                <x-lockups.episode-lockup :episode="$episode" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $this->searchResults->links() }}
        </section>

        <livewire:components.modal />
    </div>
</main>
