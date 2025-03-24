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

    <div class="py-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Theme Store') }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                        <x-link-button href="{{ route('theme-store.create') }}">{{ __('Create') }}</x-link-button>
                    </div>
                </div>

                <x-search-bar />
            </div>
        </section>

        @if (!$this->isSearching())
            <section id="default" class="mb-4">
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

        <section id="premium" class="{{ !$this->isSearching() ? 'pt-4' : '' }}">
            <x-hr class="ml-4 mr-4 pb-5" />

            <div class="pl-4 pr-4">
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
                <section id="skeleton" class="mt-4">
                    <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                        @foreach (range(1,25) as $range)
                            <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                        @endforeach

                        <div class="w-64 md:w-80 flex-grow"></div>
                        <div class="w-64 md:w-80 flex-grow"></div>
                    </div>
                </section>
            @else
                <section class="flex flex-col items-center mt-4 text-center">
                    <x-picture>
                        <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="Empty Theme Store" title="Empty Theme Store">
                    </x-picture>

                    <p class="font-bold">{{ __('Themes Not Found') }}</p>

                    <p class="text-sm text-secondary">{{ __('No themes found with the selected criteria.') }}</p>
                </section>
            @endif
        </section>
    </div>
</main>
