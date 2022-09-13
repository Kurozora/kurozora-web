<main>
    <x-slot:title>
        {{ __('Studios') }} | {!! $anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of :x studios only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Studios') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of :x studios only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}" />
        <meta property="og:image" content="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->first_aired }}" />
        <link rel="canonical" href="{{ route('anime.studios', $anime) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/studios
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <x-rows.studio-lockup :studios="$animeStudios" :is-row="false" />

        <section class="mt-4">
            {{ $animeStudios->links() }}
        </section>
    </div>
</main>
