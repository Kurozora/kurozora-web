<main>
    <x-slot:title>
        Games | {!! $studio->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all of the latest anime, movies, specials, OVA and ONA by :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $studio->name]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Games | {{ $studio->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all of the latest anime, movies, specials, OVA and ONA by :x on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $studio->name]) }}" />
        <meta property="og:image" content="{{ $studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $studio->name }}" />
        <link rel="canonical" href="{{ route('studios.games', $studio) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        studio/{{ $studio->id }}/games
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Games', ['x' => $studio->name]) }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($readyToLoad)
            <x-rows.small-lockup :games="$this->games" :is-row="false" />

            <section class="mt-4">
                {{ $this->games->links() }}
            </section>
        @else
            <section  class="mt-4 pt-5 pb-8 border-t-2">
                <div class="flex gap-4 justify-between flex-wrap">
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
