<main>
    <x-slot:title>
        Manga | {!! $character->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of manga that :x appears in only on Kurozora, the largest, free online anime, manga, music & game database in the world.', ['x', $character->name]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Manga | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of manga that :x appears in only on Kurozora, the largest, free online anime, manga, music & game database in the world.', ['x', $character->name]) }}" />
        <meta property="og:image" content="{{ $character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.manga', $character) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        character/{{ $character->id }}/mangas
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __(':x’s Mangas', ['x' => $character->name]) }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <x-rows.small-lockup :mangas="$this->manga" :is-row="false" />

        <section class="mt-4">
            {{ $this->manga->links() }}
        </section>
    </div>
</main>