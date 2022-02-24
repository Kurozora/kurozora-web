<main>
    <x-slot name="title">
        {{ __('People') }} | {!! $character->name !!}
    </x-slot>

    <x-slot name="description">
        {{ __('Discover the list of voice actors that played :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $character->name]) }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('People') }} | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the list of voice actors that played :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $character->name]) }}" />
        <meta property="og:image" content="{{ $character->profile_image_url ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.people', $character) }}">
    </x-slot>

    <x-slot name="appArgument">
        character/{{ $character->id }}/people
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid grid-cols-3 gap-4 sm:grid-cols-4 sm:auto-cols-[unset] md:grid-cols-5 lg:grid-cols-7">
            @foreach($characterPeople as $person)
                <x-lockups.person-lockup :person="$person" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $characterPeople->links() }}
        </section>
    </div>
</main>
