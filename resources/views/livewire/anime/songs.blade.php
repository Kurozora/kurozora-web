<main>
    <x-slot:title>
        {{ __('Songs') }} | {!! $anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all openings, endings and background music of :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $anime->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Songs') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all openings, endings and other music of :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $anime->title]) }}" />
        <meta property="og:image" content="{{ asset('images/static/placeholders/music_album.webp') }}" />
        <meta property="og:type" content="music.song" />
        <meta property="music:musician" content="{{ route('anime.details', $anime) }}" />
        <meta property="music:song:disc" content="1" />
        <meta property="music:album" content="{{ route('anime.details', $anime) }}" />
        <meta property="music:album:track" content="{{ $this->mediaSongs->count() }}" />
        <meta property="music:duration" content="{{ $this->mediaSongs->count() * 3 }}" />
        <link rel="canonical" href="{{ route('anime.songs', $anime) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/songs
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6 space-y-10" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Songs', ['x' => $anime->title]) }}</h1>
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
