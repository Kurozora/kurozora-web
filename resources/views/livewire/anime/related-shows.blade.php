<main>
    <x-slot:title>
        {{ __('Relations') }} | {!! $anime->title !!}
    </x-slot>

    <x-slot:description>
        {{ __('An extensive list sequel, prequel, side story, spin off, and adaptation to :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Relations') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list sequel, prequel, side story, spin off, and adaptation to :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}" />
        <meta property="og:image" content="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->first_aired }}" />
        <link rel="canonical" href="{{ route('anime.related-shows', $anime) }}">
    </x-slot>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/seasons
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid gap-4 sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3">
            @foreach($animeRelations as $animeRelation)
                <x-lockups.small-lockup :anime="$animeRelation->related" :relation="$animeRelation->relation" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $animeRelations->links() }}
        </section>
    </div>
</main>
