<main>
    <x-slot:title>
        {{ __('Staff') }} | {!! $manga->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all staff of :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $manga->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Staff') }} | {{ $manga->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all staff of :x on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $manga->title]) }}" />
        <meta property="og:image" content="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="book" />
        <meta property="book:release_date" content="{{ $manga->started_at?->toIso8601String() }}" />
        @foreach($manga->tags() as $tag)
            <meta property="book:tag" content="{{ $tag->name }}" />
        @endforeach
        <link rel="canonical" href="{{ route('anime.staff', $manga) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $manga->id }}/staff
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __(':x’s Staff', ['x' => $manga->title]) }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <x-rows.person-lockup :media-staff="$this->mediaStaff" :is-row="false" />

        <section class="mt-4">
            {{ $this->mediaStaff->links() }}
        </section>
    </div>
</main>
