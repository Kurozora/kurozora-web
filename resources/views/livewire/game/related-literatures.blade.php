<main>
    <x-slot:title>
        {{ __('Adaptations') }} | {!! $game->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of game, mod, and dlc to :x only on Kurozora, the largest, free online anime, manga, music & game database in the world.', ['x' => $game->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Adaptations') }} | {{ $game->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of game, mod, and dlc to :x only on Kurozora, the largest, free online anime, manga, music & game database in the world.', ['x' => $game->title]) }}" />
        <meta property="og:image" content="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/games_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $game->duration }}" />
        <meta property="video:release_date" content="{{ $game->started_at?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('games.related-literatures', $game) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        games/{{ $game->id }}/related-literatures
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __(':x’s Related Literatures', ['x' => $game->title]) }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <x-rows.small-lockup :related-mangas="$this->mangaRelations" :is-row="false" />

        <section class="mt-4">
            {{ $this->mangaRelations->links() }}
        </section>
    </div>
</main>
