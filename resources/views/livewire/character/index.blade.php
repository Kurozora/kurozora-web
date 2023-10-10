<main>
    <x-slot:title>
        {{ __('Characters') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of characters only on Kurozora, the largest, free online anime, manga, game & music database in the world.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Characters') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of characters only on Kurozora, the largest, free online anime, manga, game & music database in the world.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('characters.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        characters
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Characters') }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomCharacter">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random character', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        <section wire:init="loadPage">
            <div class="mt-4">
                <x-rows.character-lockup :characters="$this->searchResults" :is-row="false" />
            </div>

            @if ($readyToLoad)
                <div class="mt-4">
                    {{ $this->searchResults?->links() }}
                </div>
            @endif
        </section>
    </div>
</main>
