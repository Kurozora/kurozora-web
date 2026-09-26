<main>
    <x-slot:title>
        {{ __('Songs') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse all songs and music on :x. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Songs') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse all songs and music on :x. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('songs.index') }}">
    </x-slot:meta>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Songs') }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomSong">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random song', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        @if ($this->searchResults->count())
            <section class="mt-4 xl:safe-area-inset">
                <x-rows.music-lockup :songs="$this->searchResults" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section class="mt-4 xl:safe-area-inset">
                <x-skeletons.lockup-row lockup="music" :is-row="false" />
            </section>
        @else
            <x-empty-state image="empty_anime_library.webp" alt="Empty Songs Index" :heading="__('Songs Not Found')" :description="__('No songs found with the selected criteria.')" />
        @endif
    </div>
</main>
