<main>
    <x-slot:title>
        {{ __(':x’s Favorite Anime', ['x' => $user->username]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Join :x and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s Favorite Anime', ['x' => $user->username]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Join :x and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Favorite Anime', ['x' => $user->username]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
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

        @if ($this->searchResults->count())
            <section class="mt-4">
                <x-rows.small-lockup :animes="$this->searchResults" :is-row="false" />
            </section>

            <div class="mt-4 pl-4 pr-4">
                {{ $this->searchResults->links() }}
            </div>
        @elseif (!$readyToLoad)
            <section>
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @else
            <section class="flex flex-col items-center justify-center mt-4 text-center" style="min-height: 50vh;">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="Empty Favorite Anime" title="Empty Favorite Anime">
                </x-picture>

                <p class="font-bold">{{ __('No Favorite Anime') }}</p>

                <p class="text-sm text-secondary">{{ __('Favorite an anime and it will show up here.') }}</p>
            </section>
        @endif
    </div>
</main>
