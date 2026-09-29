<x-base-layout>
    <x-slot:title>
        {{ __('Characters') }} | {!! $person->full_name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of characters played by :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Characters') }} | {{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of characters played by :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $person->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
        <link rel="canonical" href="{{ route('people.characters', $person) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        people/{{ $person->id }}/characters
    </x-slot:appArgument>

    <main>
        <div class="pb-6">
            <x-back-link
                :url="route('people.details', $person)"
                :label="$person->full_name"
                :title="__(':x Voice Acted As', ['x' => $person->full_name])"
            />

            <section class="xl:safe-area-inset">
                <x-rows.character-lockup :characters="$characters" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $characters->links() }}
                </div>
            </section>
        </div>
    </main>
</x-base-layout>
