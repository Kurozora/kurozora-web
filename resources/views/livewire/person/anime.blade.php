<main>
    <x-slot:title>
       Anime | {!! $person->full_name !!}
    </x-slot>

    <x-slot:description>
        {{ __('Discover the extensive list of anime :x has worked on only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $person->full_name]) }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="Anime | {{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of anime :x has worked on only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $person->full_name]) }}" />
        <meta property="og:image" content="{{ $person->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
        <link rel="canonical" href="{{ route('people.anime', $person) }}">
    </x-slot>

    <x-slot:appArgument>
        person/{{ $person->id }}/shows
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <x-rows.small-lockup :animes="$personAnime" :is-row="false" />

        <section class="mt-4">
            {{ $personAnime->links() }}
        </section>
    </div>
</main>
