<x-base-layout>
    <x-slot:title>
        {{ __('Games') }} | {!! $character->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of games that :x appears in only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $character->name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Games') }} | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of games that :x appears in only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $character->name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.games', $character) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        characters/{{ $character->id }}/games
    </x-slot:appArgument>

    <main>
        <div class="pb-6">
            <x-back-link
                :url="route('characters.details', $character)"
                :label="$character->name"
                :title="__(':x’s Games', ['x' => $character->name])"
            />

            <section class="xl:safe-area-inset" data-paginated="titles">
                <x-rows.small-lockup :games="$titles" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $titles->links() }}
                </div>
            </section>
        </div>
    </main>
</x-base-layout>
