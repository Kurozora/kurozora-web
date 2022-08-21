<main>
    <x-slot:title>
        Anime | {!! $character->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of anime that :x appears in only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x', $character->name]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Anime | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of anime that :x appears in only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x', $character->name]) }}" />
        <meta property="og:image" content="{{ $character->profile_image_url ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.anime', $character) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        character/{{ $character->id }}/shows
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <x-rows.small-lockup :animes="$characterAnime" :is-row="false" />

        <section class="mt-4">
            {{ $characterAnime->links() }}
        </section>
    </div>
</main>
