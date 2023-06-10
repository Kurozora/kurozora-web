<main>
    <x-slot:title>
        {{ __('Characters') }} | {!! $person->full_name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of characters played by :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Characters') }} | {{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of characters played by :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name]) }}" />
        <meta property="og:image" content="{{ $person->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
        <link rel="canonical" href="{{ route('people.characters', $person) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        person/{{ $person->id }}/characters
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __(':x Voice Acted As', ['x' => $person->full_name]) }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <x-rows.character-lockup :characters="$personCharacters" :is-row="false" />

        <section class="mt-4">
            {{ $personCharacters->links() }}
        </section>
    </div>
</main>
