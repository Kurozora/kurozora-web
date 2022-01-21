<main>
    <x-slot name="title">
        Anime | {!! $character->name !!}
    </x-slot>

    <x-slot name="description">
        {{ __('Discover the extensive list of anime that :x appears in only on Kurozora, the largest, free online anime and manga database in the world.', ['x', $character->name]) }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="Anime | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of anime that :x appears in only on Kurozora, the largest, free online anime and manga database in the world.', ['x', $character->name]) }}" />
        <meta property="og:image" content="{{ $character->profile_image_url ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.anime', $character) }}">
    </x-slot>

    <x-slot name="appArgument">
        character/{{ $character->id }}/shows
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid gap-4 sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3">
            @foreach($characterAnime as $anime)
                <x-lockups.small-lockup :anime="$anime" :isRow="false" wire:key="{{ $anime->id }}" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $characterAnime->links() }}
        </section>
    </div>
</main>
