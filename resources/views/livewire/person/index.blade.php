<main>
    <x-slot:title>
        {{ __('People') }}
    </x-slot>

    <x-slot:description>
        {{ __('Discover the extensive list of people, voice actors, cast, and staff only on Kurozora, the largest, free online anime, manga & music database in the world.') }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('People') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover an extensive list of people, voice actors, cast, and staff on Kurozora, the largest, free online anime, manga & music database in the world.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('people.index') }}">
    </x-slot>

    <x-slot:appArgument>
        people
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __('People') }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomPerson">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random person', 'width' => '28'])
                        </x-square-button>
                    </x-slot>
                </x-search-bar>
            </div>
        </section>

        <x-rows.person-lockup :people="$this->searchResults" :is-row="false" />

        <section class="mt-4">
            {{ $this->searchResults->links() }}
        </section>
    </div>
</main>
