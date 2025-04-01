<main>
    <x-slot:title>
        {{ __('Relations') }} | {!! $manga->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of sequel, prequel, side story, spin off, and adaptations of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $manga->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Relations') }} | {{ $manga->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of sequel, prequel, side story, spin off, and adaptations of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $manga->title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="book" />
        <meta property="book:release_date" content="{{ $manga->started_at?->toIso8601String() }}" />
        @foreach ($manga->tags() as $tag)
            <meta property="book:tag" content="{{ $tag->name }}" />
        @endforeach
        <link rel="canonical" href="{{ route('manga.related-mangas', $manga) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        manga/{{ $manga->id }}/related-mangas
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Related Mangas', ['x' => $manga->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($readyToLoad)
            <x-rows.small-lockup :related-mangas="$this->mangaRelations" :is-row="false" />

            <div class="mt-4 pl-4 pr-4">
                {{ $this->mangaRelations->links() }}
            </div>
        @else
            <section>
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
