<main>
    <x-slot:title>
        {{ __('Characters') }} | {!! $person->full_name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of characters played by :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Characters') }} | {{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of characters played by :x only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name]) }}" />
        <meta property="og:image" content="{{ $person->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
        <link rel="canonical" href="{{ route('people.characters', $person) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        people/{{ $person->id }}/characters
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x Voice Acted As', ['x' => $person->full_name]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($readyToLoad)
            <x-rows.character-lockup :characters="$this->characters" :is-row="false" />

            <section class="mt-4">
                {{ $this->characters->links() }}
            </section>
        @else
            <section>
                <div class="flex gap-4 justify-between flex-wrap">
                    @foreach(range(1,25) as $range)
                        <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
