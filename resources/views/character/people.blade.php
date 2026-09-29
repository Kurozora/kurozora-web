<x-base-layout>
    <x-slot:title>
        {{ __('People') }} | {!! $character->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the list of voice actors that played :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $character->name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('People') }} | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the list of voice actors that played :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $character->name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.people', $character) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        characters/{{ $character->id }}/people
    </x-slot:appArgument>

    <main>
        <div class="pb-6">
            <x-back-link
                :url="route('characters.details', $character)"
                :label="$character->name"
                :title="__(':x’s Voice Actors', ['x' => $character->name])"
            />

            <section class="xl:safe-area-inset" data-paginated="people">
                <x-rows.person-lockup :people="$people" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $people->links() }}
                </div>
            </section>
        </div>
    </main>
</x-base-layout>
