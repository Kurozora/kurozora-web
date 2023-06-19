<main>
    <x-slot:title>
        {{ __('Studios') }} | {!! $anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of :x studios only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $anime->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Studios') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of :x studios only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $anime->title]) }}" />
        <meta property="og:image" content="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->started_at?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('anime.studios', $anime) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/studios
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Studios', ['x' => $anime->title]) }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <x-rows.studio-lockup :studios="$this->studios" :is-row="false" />

        <section class="mt-4">
            {{ $this->studios->links() }}
        </section>
    </div>
</main>
