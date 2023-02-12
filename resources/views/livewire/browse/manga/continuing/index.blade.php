<main>
    <x-slot:title>
        {{ __('Continuing this Season') }} | {{ __('Manga') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse the manga continuing this season. Join the Kurozora community and create your anime and manga list. Discover songs, games and read reviews and news!') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Continuing this Season') }} | {{ __('Manga') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse the manga continuing this season. Join the Kurozora community and create your anime and manga list. Discover songs, games and read reviews and news!') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('manga.continuing.index') }}">
    </x-slot:meta>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __('Continuing Manga') }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomManga">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random upcoming manga', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        <section class="mt-4">
            <x-rows.small-lockup :mangas="$this->searchResults" :is-row="false" />
        </section>

        <section class="mt-4">
            {{ $this->searchResults->links() }}
        </section>
    </div>
</main>
