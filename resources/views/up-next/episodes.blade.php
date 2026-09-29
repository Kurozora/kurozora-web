<x-base-layout>
    <x-slot:title>
        {{ __('Up-Next Episodes') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Find what episodes to watch next on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Up-Next Episodes') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Find what episodes to watch next on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('up-next.episodes') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        up-next/episodes
    </x-slot:appArgument>

    <main>
        <div class="pt-4 pb-6">
            <section class="mb-4 xl:safe-area-inset">
                <div>
                    <div class="flex gap-1 pl-4 pr-4">
                        <div class="flex flex-wrap items-center w-full">
                            <h1 class="text-2xl font-bold">{{ __('Up-Next Episodes') }}</h1>
                        </div>

                        <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                        </div>
                    </div>
                </div>
            </section>

            <x-episode.up-next-episodes />

            <x-episode.past-episodes-section />
        </div>
    </main>
</x-base-layout>
