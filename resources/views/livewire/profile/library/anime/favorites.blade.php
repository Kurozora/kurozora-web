<main>
    <x-slot:title>
        {{ __(':x’s Favorite Anime', ['x' => $user->username]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Join Kurozora and build your own anime and manga library for free. Keep track of the anime you love, and the ones you will love next.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s Favorite Anime', ['x' => $user->username]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Join Kurozora and build your own anime and manga library for free. Keep track of the anime you love, and the ones you will love next.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __(':x’s Favorite Anime', ['x' => $user->username]) }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomAnime">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random anime', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        <section class="mt-4">
            <x-rows.small-lockup :animes="$this->searchResults" :is-row="false" />
        </section>

        <section class="mt-4">
            {{ $this->searchResults->links() }}
        </section>
    </div>
</main>