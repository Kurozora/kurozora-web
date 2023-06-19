<main>
    <x-slot:title>
        {{ __('Adaptations') }} | {!! $manga->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of manga, manhua, manhwa, and light novel adaptations to :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $manga->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Adaptations') }} | {{ $manga->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of sequel, prequel, side story, spin off, and adaptation to :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $manga->title]) }}" />
        <meta property="og:image" content="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="book" />
        <meta property="book:release_date" content="{{ $manga->started_at?->toIso8601String() }}" />
        @foreach($manga->tags() as $tag)
            <meta property="book:tag" content="{{ $tag->name }}" />
        @endforeach
        <link rel="canonical" href="{{ route('manga.related-shows', $manga) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        manga/{{ $manga->id }}/related-shows
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Adaptations', ['x' => $manga->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <x-rows.small-lockup :related-animes="$this->animeRelations" :is-row="false" />

        <section class="mt-4">
            {{ $this->animeRelations->links() }}
        </section>
    </div>
</main>
