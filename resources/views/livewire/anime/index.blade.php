<main>
    <x-slot:title>
        {{ __('Anime') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse all games on Kurozora. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Anime') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse all games on Kurozora. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('anime.index') }}">
    </x-slot:meta>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div
                x-data="{
                    type: @entangle('typeQuery').live
                }"
            >
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Anime') }}</h1>
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

                <x-hr class="mt-4 mb-4" />

                <div class="flex gap-2 whitespace-nowrap overflow-x-scroll no-scrollbar">
                    @foreach ($this->searchScopes as $key => $value)
                        @php($type = str($value)->slug())
                        <template x-if="type === '{{ $type }}'">
                            <x-button>{{ $value }}</x-button>
                        </template>

                        <template x-if="type !== '{{ $type }}'">
                            <x-outlined-button
                                x-on:click="type = '{{ $type }}'"
                            >{{ __($value) }}</x-outlined-button>
                        </template>
                    @endforeach
                </div>
            </div>
        </section>

        @if ($this->searchResults->count())
            <section class="mt-4">
                <x-rows.small-lockup :animes="$this->searchResults" :is-row="false" />
            </section>

            <section class="mt-4">
                {{ $this->searchResults->links() }}
            </section>
        @elseif (!$readyToLoad)
            <section class="mt-4">
                <div class="flex gap-4 justify-between flex-wrap">
                    @foreach (range(1,25) as $range)
                        <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @else
            <section class="flex flex-col items-center mt-4 text-center">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="Empty Anime Index" title="Empty Anime Index">
                </x-picture>

                <p class="font-bold">{{ __('Anime Not Found') }}</p>

                <p class="text-sm text-gray-500">{{ __('No anime found with the selected criteria.') }}</p>
            </section>
        @endif
    </div>
</main>
