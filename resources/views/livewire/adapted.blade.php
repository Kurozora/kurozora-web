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

    <div
        class="pb-6"
        wire:init="loadPage"
        x-data="{ dimLibrary: false }"
        x-init="dimLibrary = localStorage.getItem('adapted-dim-library') === 'true'"
        x-bind:class="{ 'dim-in-library': dimLibrary }"
    >
        <x-back-link :url="$this->parentUrl" :label="$this->parentLabel" :title="$this->heading">
            <x-slot:actions>
                @auth
                    <template x-if="dimLibrary">
                        <x-toggle-button :selected="true" title="{{ __('Dim library') }}" aria-label="{{ __('Dim library') }}" x-on:click="dimLibrary = false; localStorage.setItem('adapted-dim-library', 'false')">
                            @svg('rectangle_stack_slash_fill', 'fill-current', ['width' => '16'])
                        </x-toggle-button>
                    </template>

                    <template x-if="!dimLibrary">
                        <x-toggle-button title="{{ __('Dim library') }}" aria-label="{{ __('Dim library') }}" x-on:click="dimLibrary = true; localStorage.setItem('adapted-dim-library', 'true')">
                            @svg('rectangle_stack_fill', 'fill-current', ['width' => '16'])
                        </x-toggle-button>
                    </template>
                @endauth
            </x-slot:actions>
        </x-back-link>

        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-2 pl-4 pr-4 overflow-x-scroll no-scrollbar">
                    @foreach (\App\Enums\AdaptedAnimeFilter::asSelectArray() as $value => $label)
                        @if ($adaptation === strtolower(\App\Enums\AdaptedAnimeFilter::getKey($value)))
                            <x-button>{{ __($label) }}</x-button>
                        @else
                            <x-outlined-button wire:click="$set('adaptation', '{{ strtolower(\App\Enums\AdaptedAnimeFilter::getKey($value)) }}')">{{ __($label) }}</x-outlined-button>
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

        @if ($this->searchResults?->count())
            <section class="mt-4 xl:safe-area-inset">
                @switch ($kind)
                    @case (\App\Enums\UserLibraryKind::Manga)
                        <x-rows.small-lockup :mangas="$this->searchResults" :is-row="false" :marks-library="auth()->check()" />
                        @break
                    @case (\App\Enums\UserLibraryKind::Game)
                        <x-rows.small-lockup :games="$this->searchResults" :is-row="false" :marks-library="auth()->check()" />
                        @break
                @endswitch

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section class="mt-4 xl:safe-area-inset">
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
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

    <style>
        .dim-in-library [data-in-library] {
            opacity: 0.25;
        }
    </style>
</main>
