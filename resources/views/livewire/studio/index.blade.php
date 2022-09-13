<main>
    <x-slot:title>
        {{ __('Studios') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover an extensive list of anime studios, producers, and networks only on Kurozora, the largest, free online anime, manga & music database in the world.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Studios') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of anime studios, producers, and networks only on Kurozora, the largest, free online anime, manga & music database in the world.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('studios.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        studios
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __('Studios') }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomStudio">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random studio', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        <x-rows.studio-lockup :studios="$this->searchResults" :is-row="false" />

        <section class="mt-4">
            {{ $this->searchResults->links() }}
        </section>
    </div>
</main>
