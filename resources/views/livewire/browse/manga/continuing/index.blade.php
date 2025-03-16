<main>
    <x-slot:title>
        {{ __('Continuing this Season') }} | {{ __('Manga') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse the manga continuing this season. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Continuing this Season') }} | {{ __('Manga') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse the manga continuing this season. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('manga.continuing.index') }}">
    </x-slot:meta>

    <div class="py-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Continuing Manga') }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomManga">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random upcoming manga', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        @if ($this->searchResults->count())
            <section class="mt-4">
                <x-rows.small-lockup :mangas="$this->searchResults" :is-row="false" />
            </section>

            <div class="mt-4 pl-4 pr-4">
                {{ $this->searchResults->links() }}
            </div>
        @elseif (!$readyToLoad)
            <section>
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
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_manga_library.webp') }}" alt="Empty Ongoing Manga" title="Empty Ongoing Manga">
                </x-picture>

                <p class="font-bold">{{ __('No Continuing Manga') }}</p>

                <p class="text-sm text-secondary">{{ __('There are currently no ongoing manga.') }}</p>
            </section>
        @endif
    </div>
</main>
