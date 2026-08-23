<main>
    <x-slot:title>
        {{ $this->ogTitleNoun }}
    </x-slot:title>

    <x-slot:description>
        {{ $this->ogDescription }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $this->ogTitleNoun }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $this->ogDescription }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ $this->canonicalUrl }}">
    </x-slot:meta>

    <x-slot:styles>
        @vite(['resources/css/watch.css'])
    </x-slot:styles>

    <x-slot:scripts>
        @vite(['resources/js/trailer-hero.js'])
    </x-slot:scripts>

    <x-slot:appArgument>
        {{ $this->appArgument }}
    </x-slot:appArgument>

    <div
        class="pb-6"
        wire:init="loadPage"
        x-data="{ dimLibrary: false }"
        x-init="dimLibrary = localStorage.getItem('trailers-dim-library') === 'true'"
        x-bind:class="{ 'dim-in-library': dimLibrary }"
    >
        <x-back-link :url="$this->parentUrl" :label="$this->parentLabel" :title="$this->heading">
            <x-slot:actions>
                @auth
                    <template x-if="dimLibrary">
                        <x-toggle-button :selected="true" title="{{ __('Dim library') }}" aria-label="{{ __('Dim library') }}" x-on:click="dimLibrary = false; localStorage.setItem('trailers-dim-library', 'false')">
                            @svg('rectangle_stack_slash_fill', 'fill-current', ['width' => '16'])
                        </x-toggle-button>
                    </template>

                    <template x-if="!dimLibrary">
                        <x-toggle-button title="{{ __('Dim library') }}" aria-label="{{ __('Dim library') }}" x-on:click="dimLibrary = true; localStorage.setItem('trailers-dim-library', 'true')">
                            @svg('rectangle_stack_fill', 'fill-current', ['width' => '16'])
                        </x-toggle-button>
                    </template>
                @endauth
            </x-slot:actions>
        </x-back-link>

        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-2 pl-4 pr-4 overflow-x-scroll no-scrollbar">
                    @foreach ($this->sortOptions as $value => $label)
                        @if ($sort === $value)
                            <x-button>{{ $label }}</x-button>
                        @else
                            <x-outlined-button wire:click="$set('sort', '{{ $value }}')">{{ $label }}</x-outlined-button>
                        @endif
                    @endforeach
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

        @if ($this->heroVideos->isNotEmpty())
            <section class="mt-4 pb-8 xl:safe-area-inset">
                <x-lockups.trailer-hero :videos="$this->heroVideos" />
            </section>
        @endif

        @if ($this->searchResults?->count())
            <section class="mt-4 xl:safe-area-inset">
                <x-rows.trailer-lockup :videos="$this->searchResults" :is-row="false" :marks-library="auth()->check()" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @elseif ($this->heroVideos->isNotEmpty())
        @elseif (!$readyToLoad)
            <section class="mt-4 xl:safe-area-inset">
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2" style="height: 356px;"></div>
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
