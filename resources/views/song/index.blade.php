<x-base-layout>
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

    <main>
        <div class="pt-4 pb-6" data-paginated="songs">
            <section class="mb-4 xl:safe-area-inset">
                <div>
                    <div class="flex gap-1 pl-4 pr-4">
                        <div class="flex flex-wrap items-center w-full">
                            <h1 class="text-2xl font-bold">{{ __('Songs') }}</h1>
                        </div>

                        <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                        </div>
                    </div>

                    <x-search-bar :criteria="$criteria" :action="route('songs.index')">
                        <x-slot:rightBarButtonItems>
                            <x-square-link href="{{ route('songs.random') }}" wire:navigate>
                                @svg('dice', 'fill-current', ['aria-labelledby' => 'random song', 'width' => '28'])
                            </x-square-link>
                        </x-slot:rightBarButtonItems>
                    </x-search-bar>
                </div>
            </section>

            @if ($songs->count())
                <section class="mt-4 xl:safe-area-inset">
                    <x-rows.music-lockup :songs="$songs" :is-row="false" />

                    <div class="mt-4 pl-4 pr-4">
                        {{ $songs->links() }}
                    </div>
                </section>
            @else
                <x-empty-state image="empty_anime_library.webp" alt="Empty Songs Index" :heading="__('Songs Not Found')" :description="__('No songs found with the selected criteria.')" />
            @endif
        </div>
    </main>
</x-base-layout>
