<main>
    <x-slot:title>
        {{ __(':x’s Favorite Game', ['x' => $user->username]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Join Kurozora and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s Favorite Game', ['x' => $user->username]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Join Kurozora and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Favorite Game', ['x' => $user->username]) }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomGame">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random game', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        @if(!empty($this->searchResults))
            @if(!empty($this->searchResults->total()))
                <section class="mt-4">
                    <x-rows.small-lockup :games="$this->searchResults" :is-row="false" />
                </section>

                <section class="mt-4">
                    {{ $this->searchResults->links() }}
                </section>
            @else
                <section class="flex flex-col items-center mt-4 text-center">
                    <x-picture>
                        <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_game_library.webp') }}" alt="Empty Favorite Game" title="Empty Favorite Game">
                    </x-picture>

                    <p class="font-bold">{{ __('No Favorite Games') }}</p>

                    <p class="text-sm text-gray-500">{{ __('Favorite a game and it will show up here.') }}</p>
                </section>
            @endif
        @elseif (!$readyToLoad)
            <section>
                <div class="flex gap-4 justify-between flex-wrap">
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
