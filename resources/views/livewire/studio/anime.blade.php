<main>
    <x-slot:title>
        Anime | {!! $studio->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all of the latest anime, movies, specials, OVA and ONA by :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $studio->name]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Anime | {{ $studio->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all of the latest anime, movies, specials, OVA and ONA by :x on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $studio->name]) }}" />
        <meta property="og:image" content="{{ $studio->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $studio->name }}" />
        <link rel="canonical" href="{{ route('studios.anime', $studio) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        studio/{{ $studio->id }}/shows
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <x-rows.small-lockup :animes="$studioAnime" :is-row="false" />

        <section class="mt-4">
            {{ $studioAnime->links() }}
        </section>
    </div>
</main>
