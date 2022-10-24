@php
    /** @var \Illuminate\Database\Eloquent\Collection $animeSongs */
    $animeSongGroups = $animeSongs->sortBy(['type.value', 'position'])
                                  ->groupBy('type.description');
@endphp

<main>
    <x-slot:title>
        {{ __('Songs') }} | {!! $anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all openings, endings and background music of :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}
    </x-slot:description>

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
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/songs
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6 space-y-10">
        @foreach($animeSongGroups as $animeSongType => $animeSongs)
            <section id="#{{ $animeSongType }}">
                <x-section-nav>
                    <x-slot:title>
                        {{ $animeSongType . ' (' . $animeSongs->count() . ')' }}
                    </x-slot:title>
                </x-section-nav>

                <x-rows.music-lockup :anime-songs="$animeSongs" :is-row="false" />
            </section>
        @endforeach
    </div>
</main>
