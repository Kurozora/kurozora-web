<x-base-layout>
    <x-slot:title>
        {{ __('Songs') }} | {!! $parent->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all openings, endings and background music of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $parent->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Songs') }} | {{ $parent->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all openings, endings and other music of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $parent->title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/placeholders/music_album.webp') }}" />
        <meta property="og:type" content="music.song" />
        <meta property="music:musician" content="{{ $detailsUrl }}" />
        <meta property="music:song:disc" content="1" />
        <meta property="music:album" content="{{ $detailsUrl }}" />
        <meta property="music:album:track" content="{{ $mediaSongs->count() }}" />
        <meta property="music:duration" content="{{ $mediaSongs->count() * 3 }}" />
        <link rel="canonical" href="{{ $canonicalUrl }}">
    </x-slot:meta>

    <x-slot:appArgument>
        {{ $appArgumentSegment }}/{{ $parent->id }}/songs
    </x-slot:appArgument>

    <main>
        <div class="pb-6 space-y-10">
            <x-back-link
                :url="$parent->schemaUrl()"
                :label="$parent->title"
                :title="__(':x’s Songs', ['x' => $parent->title])"
            />

            @foreach ($mediaSongs as $mediaSongType => $typeMediaSongs)
                <section id="#{{ $mediaSongType }}" class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>
                            {{ $mediaSongType . ' (' . $typeMediaSongs->count() . ')' }}
                        </x-slot:title>
                    </x-section-nav>

                    <x-rows.music-lockup :media-songs="$typeMediaSongs" :is-row="false" />
                </section>
            @endforeach
        </div>
    </main>
</x-base-layout>
