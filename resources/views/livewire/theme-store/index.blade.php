<main>
    <x-slot:title>
        {{ __('Theme Store') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of platform themes only on Kurozora, the largest, free online anime, manga, game & music database in the world.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Theme Store') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of platform themes only on Kurozora, the largest, free online anime, manga, game & music database in the world.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('theme-store.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        theme-store
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
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

        <section id="default" class="mb-4">
            <div>
                <h1 class="text-lg font-bold">{{ __('Default') }}</h1>
            </div>

            <div class="flex gap-4 justify-between flex-wrap">
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
            </div>
        </section>

        @if ($this->searchResults->count())
            <section id="premium" class="pt-4 border-t border-primary">
                <div>
                    <h1 class="text-lg font-bold">{{ __('Premium') }}</h1>
                </div>

                <div class="flex gap-4 justify-between flex-wrap">
                    @foreach ($this->searchResults as $platformTheme)
                        <x-lockups.platform-theme-lockup :theme="$platformTheme" />
                    @endforeach

                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>

                <div class="mt-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section id="skeleton" class="mt-4">
                <div class="flex gap-4 justify-between flex-wrap">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach

                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
