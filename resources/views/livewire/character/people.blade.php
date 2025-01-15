<main>
    <x-slot:title>
        {{ __('People') }} | {!! $character->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the list of voice actors that played :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $character->name]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('People') }} | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the list of voice actors that played :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $character->name]) }}" />
        <meta property="og:image" content="{{ $character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.people', $character) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        characters/{{ $character->id }}/people
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Voice Actors', ['x' => $character->name]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($readyToLoad)
            <x-rows.person-lockup :people="$this->people" :is-row="false" />

            <section class="mt-4">
                {{ $this->people->links() }}
            </section>
        @else
            <section>
                <div class="flex gap-4 justify-between flex-wrap">
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
