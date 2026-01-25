<div
    class="relative w-full"
    x-data="{
        isSearchEnabled: @entangle('isSearchEnabled').live,
        searchQuery: @entangle('searchQuery').live,
        resetAndClose() {
            isSearchEnabled = false;
            this.searchQuery = '';
        },
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input, textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'

            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
        focusOnSearch() { isSearchEnabled = true; setTimeout(() => $refs.search.focus(), 0) },
        handleTabKeydown($event) {
            if (this.searchQuery !== '') {
                $event.preventDefault()
                return $event.shiftKey || this.nextFocusable().focus()
            }
        },
        submit() {
            let search = document.getElementById('search');
            search.submit();
        }
    }"
    x-on:close.stop="resetAndClose()"
    x-on:keydown.escape.window="resetAndClose()"
    x-on:keydown.meta.k.window.prevent="focusOnSearch()"
    x-on:keydown.slash.window.prevent="focusOnSearch()"
    x-on:keydown.tab="handleTabKeydown($event)"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
>
    <form
        id="search"
        class="relative flex items-center gap-2 w-full"
        action="{{ route('search.index') }}"
        method="get"
        x-transition:enter="ease duration-[500ms] delay-300 transform"
        x-transition:enter-start="opacity-0 translate-x-8"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="isSearchEnabled = true"
    >
        {{-- Search icon --}}
        <div class="absolute inline-flex left-0 pl-2 h-full text-secondary">
            @svg('magnifyingglass', 'fill-current', ['width' => '14'])
        </div>

        {{-- Search field --}}
        <x-input
            class="pr-8 pl-8 h-8 w-full text-sm bg-blur border-secondary"
            type="search"
            name="q"
            placeholder="{{ [__('Search'), '⌘+K, ctrl+K or /'][array_rand([0,1])] }}"
            x-ref="search"
            wire:model.live.debounce.500ms="searchQuery"
        />

        {{-- Close button --}}
        <button
            class="absolute right-0 pl-2 pr-2 h-full text-secondary transition duration-150 ease-in-out hover:text-primary"
            x-cloak
            x-show="isSearchEnabled && searchQuery !== ''"
            x-on:click="resetAndClose()"
            x-transition:enter="ease duration-[400ms] delay-[325ms] transform"
            x-transition:enter-start="opacity-0 translate-x-1"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            type="reset"
        >
            @svg('xmark', 'fill-current', ['width' => '14'])
        </button>
    </form>

    {{-- Search Results --}}
    <div
        class="absolute right-0 left-0 pt-4 pb-4 bg-primary border border-black/20 rounded-lg shadow-md overflow-y-auto z-10"
        style="max-height: 85vh; width: 360px;"
        x-cloak
        x-show="isSearchEnabled && searchQuery !== ''"
    >
        <div class="flex justify-center">
            <x-spinner wire:target="searchQuery" />
        </div>

        @if (!empty($searchResults))
            @foreach ($searchResults as $searchResult)
                <x-search-header class="{{ $loop->first ? 'mt-0' : 'mt-6' }}">
                    <x-slot:title>
                        {{ $searchResult['title'] }}
                    </x-slot:title>

                    <x-slot:action>
                        <x-section-nav-link href="{{ route('search.index', ['q' => $this->searchQuery, 'type' => $searchResult['search_type']]) }}">{{ __('See All') }}</x-section-nav-link>
                    </x-slot:action>
                </x-search-header>

                <div class="mt-4">
                    @switch($searchResult['type'])
                        @case(\App\Models\Anime::TABLE_NAME)
                            <x-rows.small-lockup :animes="$searchResult['results']" :safe-area-inset-enabled="false" />
                            @break
                        @case(\App\Models\Manga::TABLE_NAME)
                            <x-rows.small-lockup :mangas="$searchResult['results']" :safe-area-inset-enabled="false" />
                            @break
                        @case(\App\Models\Game::TABLE_NAME)
                            <x-rows.small-lockup :games="$searchResult['results']" :safe-area-inset-enabled="false" />
                            @break
                        @case(\App\Models\Episode::TABLE_NAME)
                            <x-rows.episode-lockup :episodes="$searchResult['results']" :safe-area-inset-enabled="false" />
                            @break
                        @case(\App\Models\Character::TABLE_NAME)
                            <x-rows.character-lockup :characters="$searchResult['results']" :safe-area-inset-enabled="false" />
                            @break
                        @case(\App\Models\Person::TABLE_NAME)
                            <x-rows.person-lockup :people="$searchResult['results']" :safe-area-inset-enabled="false" />
                            @break
                        @case(\App\Models\Studio::TABLE_NAME)
                            <x-rows.studio-lockup :studios="$searchResult['results']" :safe-area-inset-enabled="false" />
                            @break
                        @case(\App\Models\Song::TABLE_NAME)
                            <x-rows.music-lockup :songs="$searchResult['results']" :safe-area-inset-enabled="false" />
                            @break
                        @case(\App\Models\User::TABLE_NAME)
                            <x-rows.user-lockup :users="$searchResult['results']" :safe-area-inset-enabled="false" />
                            @break
                    @endswitch
                </div>
            @endforeach
        @endif

        @if (empty($searchResults) && !empty($searchQuery))
            <p class="text-sm text-secondary text-center font-bold" wire:key="no-results-found">{{ __('No search results found :(') }}</p>
        @endif
    </div>
</div>
