<main>
    <x-slot:title>
        Manga | {!! $character->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of manga that :x appears in only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $character->name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Manga | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of manga that :x appears in only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $character->name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.manga', $character) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        characters/{{ $character->id }}/mangas
    </x-slot:appArgument>

    <div class="pb-6">
        <x-back-link
            :url="route('characters.details', $character)"
            :label="$character->name"
            :title="__(':x’s Mangas', ['x' => $character->name])"
        />

        <section class="xl:safe-area-inset">
            <x-rows.small-lockup :mangas="$this->titles" :is-row="false" />

            <div class="mt-4 pl-4 pr-4">
                {{ $this->titles->links() }}
            </div>
        </section>
    </div>
</main>
