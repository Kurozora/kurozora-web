<main>
    <x-slot:title>
        {{ __('Adaptations') }} | {!! $anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of manga, manhua, manhwa, and light novel adaptations to :x only on Kurozora, the largest, free online anime, manga, music & game database in the world.', ['x' => $anime->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Adaptations') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of manga, manhua, manhwa, and light novel adaptations to :x only on Kurozora, the largest, free online anime, manga, music & game database in the world.', ['x' => $anime->title]) }}" />
        <meta property="og:image" content="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->first_aired?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('anime.related-mangas', $anime) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/related-mangas
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __(':x’s Adaptations', ['x' => $anime->title]) }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <x-rows.small-lockup :related-mangas="$this->mangaRelations" :is-row="false" />

        <section class="mt-4">
            {{ $this->mangaRelations->links() }}
        </section>
    </div>
</main>