<main>
    <x-slot:title>
        {{ __('Songs') }} | {!! $game->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all openings, endings and background music of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $game->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Songs') }} | {{ $game->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all openings, endings and other music of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $game->title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/placeholders/music_album.webp') }}" />
        <meta property="og:type" content="music.song" />
        <meta property="music:musician" content="{{ route('games.details', $game) }}" />
        <meta property="music:song:disc" content="1" />
        <meta property="music:album" content="{{ route('games.details', $game) }}" />
        <meta property="music:album:track" content="{{ $this->mediaSongs->count() }}" />
        <meta property="music:duration" content="{{ $this->mediaSongs->count() * 3 }}" />
        <link rel="canonical" href="{{ route('games.songs', $game) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        games/{{ $game->id }}/songs
    </x-slot:appArgument>

    <div class="pt-4 pb-6 space-y-10" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Songs', ['x' => $game->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($this->mediaSongs->count())
            @foreach ($this->mediaSongs as $mediaSongType => $mediaSongs)
                <section id="#{{ $mediaSongType }}">
                    <x-section-nav>
                        <x-slot:title>
                            {{ $mediaSongType . ' (' . $mediaSongs->count() . ')' }}
                        </x-slot:title>
                    </x-section-nav>

                    <x-rows.music-lockup :media-songs="$mediaSongs" :is-row="false" />
                </section>
            @endforeach
        @elseif (!$readyToLoad)
            <section>
                <div class="flex gap-2 justify-between mb-5 pl-4 pr-4">
                    <div>
                        <p class="bg-secondary rounded-md" style="width: 168px; height: 28px"></p>
                    </div>

                    <div class="flex flex-wrap gap-2 justify-end"></div>
                </div>

                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
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
