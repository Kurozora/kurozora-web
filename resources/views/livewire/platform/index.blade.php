<main>
    <x-slot:title>
        {{ __('Platforms') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover an extensive list of anime, manga, game & music platforms only on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Platforms') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of anime, manga, game & music platforms only on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('platforms.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        platforms
    </x-slot:appArgument>

    <div class="pt-4 pb-6">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Platforms') }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomPlatform">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random platform', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        @if ($this->searchResults->count())
            <section class="mt-4 xl:safe-area-inset">
                <x-rows.platform-lockup :platforms="$this->searchResults" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @else
            <x-empty-state image="empty_anime_library.webp" alt="Empty Platforms Index" :heading="__('Platforms Not Found')" :description="__('No platforms found with the selected criteria.')" />
        @endif
    </div>
</main>
