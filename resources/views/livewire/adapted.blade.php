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
                <x-skeletons.lockup-row :kind="$kind" :is-row="false" />
            </section>
        @else
            <x-empty-state :image="$this->emptyImage" :heading="$this->emptyHeading" :description="$this->emptyDescription" />
        @endif
    </div>
</main>
