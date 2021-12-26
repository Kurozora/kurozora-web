<main>
    <x-slot name="title">
        {{ __('Characters') }} | {!! $person->full_name !!} — {{ config('app.name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Discover the extensive list of characters played by :x only on Kurozora, the largest, free online anime and manga database in the world.', ['x' => $person->full_name]) }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Characters') }} | {{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of characters played by :x only on Kurozora, the largest, free online anime and manga database in the world.', ['x' => $person->full_name]) }}" />
        <meta property="og:image" content="{{ $person->profile_image_url ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
    </x-slot>

    <x-slot name="appArgument">
        person/{{ $person->id }}/characters
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid grid-cols-3 gap-4 sm:grid-cols-4 sm:auto-cols-[unset] md:grid-cols-5 lg:grid-cols-7">
            @foreach($personCharacters as $character)
                <x-lockups.character-lockup :character="$character" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $personCharacters->links() }}
        </section>
    </div>
</main>
