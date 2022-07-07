<main>
    <x-slot:title>
        {{ __('Anime') }}
    </x-slot>

    <x-slot:description>
        {{ __('Browse all anime on Kurozora. Join the Kurozora community and create your anime and manga list. Discover songs, games and read reviews and news!') }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Anime') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse all anime on Kurozora. Join the Kurozora community and create your anime and manga list. Discover songs, games and read reviews and news!') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('anime.index') }}">
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __('Anime') }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomAnime">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random anime', 'width' => '28'])
                        </x-square-button>
                    </x-slot>
                </x-search-bar>
            </div>
        </section>

        <section class="mt-4">
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach($this->searchResults as $anime)
                    <x-lockups.small-lockup :anime="$anime" :is-row="false" />
                @endforeach
            </div>
        </section>

        <section class="mt-4">
            {{ $this->searchResults->links() }}
        </section>
    </div>
</main>
