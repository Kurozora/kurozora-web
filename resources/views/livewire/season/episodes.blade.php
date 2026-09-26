<main>
    <x-slot:title>
        {{ __(':x Episodes', ['x' => $season->title]) }} | {!! $anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ $season->synopsis ?? __('Discover the extensive list of :x episodes only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $anime->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x Episodes', ['x' => $season->title]) }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $season->synopsis ?? __('Discover the extensive list of :x episodes on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $anime->title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $season->duration }}" />
        <meta property="video:release_date" content="{{ $season->started_at?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('seasons.episodes', $season) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        seasons/{{ $season->id }}/episodes
    </x-slot:appArgument>

    <div class="pb-6" wire:init="loadPage">
        <x-back-link
            :url="route('anime.seasons', $anime)"
            :label="__(':x Seasons', ['x' => $anime->title])"
            :title="__(':x Episodes', ['x' => $season->title])"
        >
            <x-slot:actions>
                <livewire:season.watch-button :season="$season" />

                @if ($this->canUpdateEpisodes && app()->isLocal())
                    <x-circle-button
                        wire:click="updateEpisodes"
                        wire:loading.attr="disabled"
                    >
                        @svg('arrow_clockwise', 'fill-current', ['width' => '44'])
                    </x-circle-button>
                @endif

                <x-nova-link :href="route('seasons.edit', $season)">
                    @svg('pencil', 'fill-current', ['width' => '44'])
                </x-nova-link>
            </x-slot:actions>

            <x-search-bar>
                <x-slot:rightBarButtonItems>
                    <x-square-button wire:click="randomEpisode">
                        @svg('dice', 'fill-current', ['aria-labelledby' => 'random episode', 'width' => '28'])
                    </x-square-button>
                </x-slot:rightBarButtonItems>
            </x-search-bar>
        </x-back-link>

        @if ($this->searchResults->count())
            <section class="xl:safe-area-inset">
                <x-rows.episode-lockup :episodes="$this->searchResults" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section class="mt-4 xl:safe-area-inset">
                <x-skeletons.lockup-row lockup="episode" :is-row="false" />
            </section>
        @else
            <x-empty-state image="empty_anime_library.webp" alt="Empty Episodes" :heading="__('Episodes Not Found')" :description="__('No episodes found in the selected season.')" />
        @endif
    </div>
</main>
