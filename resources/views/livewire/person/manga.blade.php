<main>
    <x-slot:title>
        Manga | {!! $person->full_name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of manga :x has worked on only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Manga | {{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of manga :x has worked on only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name]) }}" />
        <meta property="og:image" content="{{ $person->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
        <link rel="canonical" href="{{ route('people.manga', $person) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        person/{{ $person->id }}/mangas
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __(':x’s Mangas', ['x' => $person->full_name]) }}</p>
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
