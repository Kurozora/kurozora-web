<x-base-layout>
    <x-slot:title>
        {{ $heading }}
    </x-slot:title>

    <x-slot:description>
        {{ $description }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $heading }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $description }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ $canonicalUrl }}">
    </x-slot:meta>

    <x-slot:styles>
        @vite(['resources/css/watch.css', 'resources/css/trailer.css'])
    </x-slot:styles>

    <x-slot:scripts>
        @vite(['resources/js/trailer-hero.js'])
    </x-slot:scripts>

    <x-slot:appArgument>
        {{ $appArgument }}
    </x-slot:appArgument>

    <main>
        <div
            class="pb-6"
            data-paginated="catalog"
            x-data="{ dimLibrary: false }"
            x-init="dimLibrary = localStorage.getItem('trailers-dim-library') === 'true'"
            x-bind:class="{ 'dim-in-library': dimLibrary }"
        >
            <x-back-link data-trailer-header :url="$parentUrl" :label="$parentLabel" :title="$heading">
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
                        @foreach ($sortOptions as $value => $label)
                            @if ($sort === $value)
                                <x-button>{{ $label }}</x-button>
                            @else
                                <x-outlined-button type="button" form="search-bar" value="{{ $value === $defaultSort ? '' : $value }}" data-search-choice="sort">{{ $label }}</x-outlined-button>
                            @endif
                        @endforeach
                    </div>

                    <x-search-bar :criteria="$criteria" :action="$canonicalUrl" :hidden="['sort' => $sort === $defaultSort ? '' : $sort, 'video' => $featuredVideoId ?? '']">
                        <x-slot:rightBarButtonItems>
                            <x-square-link href="{{ $randomUrl }}" wire:navigate>
                                @svg('dice', 'fill-current', ['aria-labelledby' => $randomLabel, 'width' => '28'])
                            </x-square-link>
                        </x-slot:rightBarButtonItems>
                    </x-search-bar>
                </div>
            </section>

            @if ($featuredVideo)
                <section class="mt-4 pb-8 xl:safe-area-inset">
                    <x-lockups.trailer-hero :video="$featuredVideo" />
                </section>
            @endif

            @if ($results->count())
                <section class="mt-4 xl:safe-area-inset">
                    <x-rows.trailer-lockup :videos="$results" :is-row="false" :marks-library="auth()->check()" />

                    <div class="mt-4 pl-4 pr-4">
                        {{ $results->links() }}
                    </div>
                </section>
            @else
                <x-empty-state :image="$emptyImage" :heading="$emptyHeading" :description="$emptyDescription" />
            @endif
        </div>
    </main>
</x-base-layout>
