<main>
    <x-slot:title>
        {{ __('Continuing this Season') }} | {{ $this->ogTitleNoun }}
    </x-slot:title>

    <x-slot:description>
        {{ $this->ogDescription }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Continuing this Season') }} | {{ $this->ogTitleNoun }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $this->ogDescription }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ $this->canonicalUrl }}">
    </x-slot:meta>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ $this->heading }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomItem">
                            @svg('dice', 'fill-current', ['aria-labelledby' => $this->randomLabel, 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        @if ($this->searchResults->count())
            <section class="mt-4 xl:safe-area-inset">
                @switch ($kind)
                    @case (\App\Enums\UserLibraryKind::Anime)
                        <x-rows.small-lockup :animes="$this->searchResults" :is-row="false" />
                        @break
                    @case (\App\Enums\UserLibraryKind::Manga)
                        <x-rows.small-lockup :mangas="$this->searchResults" :is-row="false" />
                        @break
                @endswitch

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section class="xl:safe-area-inset">
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @else
            <section class="flex flex-col items-center justify-center mt-4 text-center xl:safe-area-inset" style="min-height: 50vh;">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/' . $this->emptyImage) }}" alt="{{ $this->emptyHeading }}" title="{{ $this->emptyHeading }}">
                </x-picture>

                <p class="font-bold">{{ $this->emptyHeading }}</p>

                <p class="text-sm text-secondary">{{ $this->emptyDescription }}</p>
            </section>
        @endif
    </div>
</main>
