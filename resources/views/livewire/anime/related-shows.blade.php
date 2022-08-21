<main>
    <x-slot:title>
        {{ __('Relations') }} | {!! $anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of sequel, prequel, side story, spin off, and adaptation to :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Relations') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of sequel, prequel, side story, spin off, and adaptation to :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}" />
        <meta property="og:image" content="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->first_aired }}" />
        <link rel="canonical" href="{{ route('anime.related-shows', $anime) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/related-shows
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <x-rows.small-lockup :related-animes="$animeRelations" :is-row="false" />

        <section class="mt-4">
            {{ $animeRelations->links() }}
        </section>
    </div>
</main>
