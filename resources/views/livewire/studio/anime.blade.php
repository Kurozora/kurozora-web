<main>
    <x-slot:title>
        Anime | {!! $studio->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all of the latest anime, movies, specials, OVA and ONA by :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $studio->name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Anime | {{ $studio->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all of the latest anime, movies, specials, OVA and ONA by :x on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $studio->name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $studio->name }}" />
        <link rel="canonical" href="{{ route('studios.anime', $studio) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        studios/{{ $studio->id }}/shows
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Shows', ['x' => $studio->name]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($readyToLoad)
            <section class="xl:safe-area-inset">
                <x-rows.small-lockup :animes="$this->animes" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->animes->links() }}
                </div>
            </section>
        @else
            <section class="xl:safe-area-inset">
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
