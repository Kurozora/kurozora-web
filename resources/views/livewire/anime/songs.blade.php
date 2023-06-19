@php
    /** @var \Illuminate\Database\Eloquent\Collection $mediaSongs */
    $mediaSongGroups = $mediaSongs->sortBy(['type.value', 'position'])
                                  ->groupBy('type.description');
@endphp

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
        <meta property="music:album:track" content="{{ $mediaSongs->count() }}" />
        <meta property="music:duration" content="{{ $mediaSongs->count() * 3 }}" />
        <link rel="canonical" href="{{ route('anime.songs', $anime) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/songs
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6 space-y-10">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Songs', ['x' => $anime->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @foreach($mediaSongGroups as $mediaSongType => $mediaSongs)
            <section id="#{{ $mediaSongType }}">
                <x-section-nav>
                    <x-slot:title>
                        {{ $mediaSongType . ' (' . $mediaSongs->count() . ')' }}
                    </x-slot:title>
                </x-section-nav>

                <x-rows.music-lockup :media-songs="$mediaSongs" :is-row="false" />
            </section>
        @endforeach
    </div>
</main>
