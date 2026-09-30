<x-base-layout>
    <x-slot:title>
        {{ __('Characters') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of characters only on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Characters') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of characters only on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('characters.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        characters
    </x-slot:appArgument>

    <main>
        <div class="pt-4 pb-6" data-paginated="characters">
            <section class="mb-4 xl:safe-area-inset">
                <div>
                    <div class="flex gap-1 pl-4 pr-4">
                        <div class="flex flex-wrap items-center w-full">
                            <h1 class="text-2xl font-bold">{{ __('Characters') }}</h1>
                        </div>

                        <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                        </div>
                    </div>

                    <x-search-bar :criteria="$criteria" :action="route('characters.index')">
                        <x-slot:rightBarButtonItems>
                            <x-square-link href="{{ route('characters.random') }}" wire:navigate>
                                @svg('dice', 'fill-current', ['aria-labelledby' => 'random character', 'width' => '28'])
                            </x-square-link>
                        </x-slot:rightBarButtonItems>
                    </x-search-bar>
                </div>
            </section>

            @if ($characters->count())
                <section class="mt-4 xl:safe-area-inset">
                    <x-rows.character-lockup :characters="$characters" :is-row="false" />

                    <div class="mt-4 pl-4 pr-4">
                        {{ $characters->links() }}
                    </div>
                </section>
            @else
                <x-empty-state image="empty_anime_library.webp" alt="Empty Characters Index" :heading="__('Characters Not Found')" :description="__('No characters found with the selected criteria.')" />
            @endif
        </div>
    </main>
</x-base-layout>
