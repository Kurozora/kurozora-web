<main>
    <x-slot:title>
        {{ __('Studios') }} | {!! $game->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of :x studios only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $game->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Studios') }} | {{ $game->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of :x studios only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $game->title]) }}" />
        <meta property="og:image" content="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/game_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $game->duration }}" />
        <meta property="video:release_date" content="{{ $game->published_at?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('games.studios', $game) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        games/{{ $game->id }}/studios
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Studios', ['x' => $game->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($this->studios->count())
            <x-rows.studio-lockup :studios="$this->studios" :is-row="false" />

            <section class="mt-4">
                {{ $this->studios->links() }}
            </section>
        @elseif (!$readyToLoad)
            <section>
                <div class="flex gap-4 justify-between flex-wrap">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
