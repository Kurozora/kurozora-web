<x-base-layout>
    <x-slot:title>
        Manga | {!! $person->full_name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of manga :x has worked on only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Manga | {{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of manga :x has worked on only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $person->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
        <link rel="canonical" href="{{ route('people.manga', $person) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        people/{{ $person->id }}/mangas
    </x-slot:appArgument>

    <main>
        <div class="pb-6">
            <x-back-link
                :url="route('people.details', $person)"
                :label="$person->full_name"
                :title="__(':x’s Mangas', ['x' => $person->full_name])"
            />

            <section class="xl:safe-area-inset" data-paginated="titles">
                <x-rows.small-lockup :mangas="$titles" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $titles->links() }}
                </div>
            </section>
        </div>
    </main>
</x-base-layout>
