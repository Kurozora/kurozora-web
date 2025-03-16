<main>
    <x-slot:title>
        Anime | {!! $character->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of anime that :x appears in only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x', $character->name]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Anime | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of anime that :x appears in only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x', $character->name]) }}" />
        <meta property="og:image" content="{{ $character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.anime', $character) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        characters/{{ $character->id }}/shows
    </x-slot:appArgument>

    <div class="py-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Anime', ['x' => $character->name]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($readyToLoad)
            <x-rows.small-lockup :animes="$this->anime" :is-row="false" />

            <div class="mt-4 pl-4 pr-4">
                {{ $this->anime->links() }}
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
