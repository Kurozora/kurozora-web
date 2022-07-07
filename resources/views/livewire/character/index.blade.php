<main>
    <x-slot:title>
        {{ __('Characters') }}
    </x-slot>

    <x-slot:description>
        {{ __('Discover the extensive list of characters only on Kurozora, the largest, free online anime, manga & music database in the world.') }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Characters') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of characters only on Kurozora, the largest, free online anime, manga & music database in the world.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('characters.index') }}">
    </x-slot>

    <x-slot:appArgument>
        characters
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __('Characters') }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomCharacter">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random character', 'width' => '28'])
                        </x-square-button>
                    </x-slot>
                </x-search-bar>
            </div>
        </section>

        <section class="grid grid-cols-2 gap-4 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7">
            @foreach($this->searchResults as $character)
                <x-lockups.character-lockup :character="$character" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $this->searchResults->links() }}
        </section>
    </div>
</main>
