@php
    /** @var \Illuminate\Database\Eloquent\Collection $animeSongs */
    $animeSongGroups = $animeSongs->sortBy(['type.value', 'position'])
                                  ->groupBy('type.description');
@endphp

<main>
    <x-slot:title>
        {{ __('Songs') }} | {!! $anime->title !!}
    </x-slot>

    <x-slot:description>
        {{ __('Discover all openings, endings and background music of :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Songs') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all openings, endings and other music of :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}" />
        <meta property="og:image" content="{{ asset('images/static/placeholders/music_album.webp') }}" />
        <meta property="og:type" content="music.song" />
        <meta property="music:musician" content="{{ route('anime.details', $anime) }}" />
        <meta property="music:song:disc" content="1" />
        <meta property="music:album" content="{{ route('anime.details', $anime) }}" />
        <meta property="music:album:track" content="{{ $animeSongs->count() }}" />
        <meta property="music:duration" content="{{ $animeSongs->count() * 3 }}" />
        <link rel="canonical" href="{{ route('anime.songs', $anime) }}">
    </x-slot>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/songs
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 space-y-10">
        @foreach($animeSongGroups as $animeSongType => $animeSongs)
            <section id="#{{ $animeSongType }}">
                <x-section-nav>
                    <x-slot:title>
                        {{ $animeSongType . ' (' . $animeSongs->count() . ')' }}
                    </x-slot>
                </x-section-nav>

                <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @foreach($animeSongs as $animeSong)
                        <x-lockups.music-lockup :anime-song="$animeSong" :onMusicKitLoad="true" :is-row="false" />
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</main>
