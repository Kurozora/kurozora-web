<main>
    <x-slot:title>
        Anime | {!! $studio->name !!}
    </x-slot>

    <x-slot:description>
        {{ __('Discover all of the latest anime, movies, specials, OVA and ONA by :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $studio->name]) }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="Anime | {{ $studio->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all of the latest anime, movies, specials, OVA and ONA by :x on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $studio->name]) }}" />
        <meta property="og:image" content="{{ $studio->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $studio->name }}" />
        <link rel="canonical" href="{{ route('studios.anime', $studio) }}">
    </x-slot>

    <x-slot:appArgument>
        studio/{{ $studio->id }}/shows
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid gap-4 sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3">
            @foreach($studioAnime as $anime)
                <x-lockups.small-lockup :anime="$anime" wire:key="{{ $anime->id }}" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $studioAnime->links() }}
        </section>
    </div>
</main>
