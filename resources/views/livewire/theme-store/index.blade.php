<main>
    <x-slot:title>
        {{ __('Theme Store') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of platform themes only on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Theme Store') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of platform themes only on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('theme-store.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        theme-store
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Theme Store') }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                        <x-link-button href="{{ route('theme-store.create') }}" wire:navigate>{{ __('Create') }}</x-link-button>
                    </div>
                </div>

                <x-search-bar />
            </div>
        </section>

        @if (!$this->isSearching())
            <section id="default" class="mb-4 xl:safe-area-inset">
                <div class="pl-4 pr-4">
                    <h2 class="text-lg font-bold">{{ __('Default') }}</h2>
                </div>

                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach(\App\Enums\KTheme::defaultCases() as $theme)
                        <x-lockups.local-platform-theme-lockup
                            :title="$theme->stringValue()"
                            :subtitle="$theme->descriptionValue()"
                            :color="$theme->colorValue()"
                            :images="$theme->imageValues()"
                        />
                    @endforeach

                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif

        <section id="premium" class="{{ !$this->isSearching() ? 'pt-4 xl:safe-area-inset' : 'xl:safe-area-inset' }}">
            <div class="pt-4 pl-4 pr-4">
                <h2 class="text-lg font-bold">{{ __('Premium') }}</h2>
            </div>

            @if ($this->searchResults->count())
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach ($this->searchResults as $platformTheme)
                        <x-lockups.platform-theme-lockup :theme="$platformTheme" />
                    @endforeach

                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->searchResults->links() }}
                </div>
            @elseif (!$readyToLoad)
                <div id="skeleton" class="mt-4">
                    <x-skeletons.lockup-row lockup="theme" :is-row="false" />
                </div>
            @else
                <x-empty-state class="flex flex-col items-center justify-center mt-4 text-center" image="empty_anime_library.webp" alt="Empty Theme Store" :heading="__('Themes Not Found')" :description="__('No themes found with the selected criteria.')" />
            @endif
        </section>
    </div>
</main>
