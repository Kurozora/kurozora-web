<div
    x-data="{
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
        submit() {
            let search = document.getElementById('search');
            search.submit();
        }
    }"
    x-on:close.stop="resetAndClose()"
    x-on:keydown.escape.window="resetAndClose()"
    x-on:keydown.meta.k.window.prevent="focusOnSearch()"
    x-on:keydown.slash.window.prevent="focusOnSearch()"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-init="$watch('isSearchEnabled', function(value) {
        if (value) {
            focusOnSearch()
        }
    })"
>
    <div class="absolute top-0 right-0 left-0 mx-auto max-w-full sm:max-w-2xl"
         style="z-index: 300; max-height: 85vh"
         x-cloak
         x-show="isSearchEnabled"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-[400ms]"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
    >
        <div class="h-12">
            <form
                id="search"
                class="absolute h-full w-full"
                action="{{ route('search.index') }}"
                method="get"
                x-show="isSearchEnabled"
                x-transition:enter="ease duration-[500ms] delay-300 transform"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                {{-- Search icon --}}
                <div class="absolute inline-flex left-0 pl-4 h-full text-secondary sm:pl-2">
                    @svg('magnifyingglass', 'fill-current', ['width' => '16'])
                </div>

                {{-- Search field --}}
                <input
                    class="absolute top-0 left-0 pl-12 pr-12 h-full w-full border-0 bg-transparent text-primary focus:ring-0 sm:px-10"
                    type="search"
                    name="q"
                    placeholder="{{ [__('I’m searching for…'), __('Search faster with ⌘+K, ctrl+K or /')][array_rand([0,1])] }}"
                    x-ref="search"
                    wire:model.live.debounce.500ms="searchQuery"
                />
            </form>

            {{-- Close button --}}
            <x-square-button
                class="absolute right-0 mt-2 mr-1"
                x-show="isSearchEnabled"
                x-on:click="resetAndClose()"
                x-transition:enter="ease duration-[400ms] delay-[325ms] transform"
                x-transition:enter-start="opacity-0 translate-x-1"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                type="reset"
            >
                @svg('xmark', 'fill-current', ['width' => '20'])
            </x-square-button>
        </div>

        {{-- Search Results --}}
        <div
            class="absolute right-0 left-0 pt-4 pb-4 bg-primary rounded-b-2xl overflow-y-scroll"
            style="max-height: 85vh;"
        >
            <div class="flex justify-center">
                <x-spinner wire:target="searchQuery" />
            </div>

            @if (!empty($searchResults))
                @foreach ($searchResults as $searchResult)
                    <x-search-header>
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
                                <x-rows.small-lockup :animes="$searchResult['results']" />
                            @break
                            @case(\App\Models\Manga::TABLE_NAME)
                                <x-rows.small-lockup :mangas="$searchResult['results']" />
                            @break
                            @case(\App\Models\Game::TABLE_NAME)
                                <x-rows.small-lockup :games="$searchResult['results']" />
                            @break
                            @case(\App\Models\Episode::TABLE_NAME)
                                <x-rows.episode-lockup :episodes="$searchResult['results']" />
                            @break
                            @case(\App\Models\Character::TABLE_NAME)
                                <x-rows.character-lockup :characters="$searchResult['results']" />
                            @break
                            @case(\App\Models\Person::TABLE_NAME)
                                <x-rows.person-lockup :people="$searchResult['results']" />
                            @break
                            @case(\App\Models\Studio::TABLE_NAME)
                                <x-rows.studio-lockup :studios="$searchResult['results']" />
                            @break
                            @case(\App\Models\User::TABLE_NAME)
                                <x-rows.user-lockup :users="$searchResult['results']" />
                            @break
                            @case(\App\Models\Song::TABLE_NAME)
                                <x-rows.music-lockup :songs="$searchResult['results']" />
                            @break
                        @endswitch

                        <x-hr class="mt-4 mb-4 ml-4 mr-4" />
                    </div>
                @endforeach
            @endif

            @if (empty($searchResults) && !empty($searchQuery))
                <p class="text-sm text-secondary text-center font-bold" wire:key="no-results-found">{{ __('No search results found :(') }}</p>
            @endif

            {{-- Quick Links --}}
            @if (!empty($quickLinks) && empty($searchResults))
                <x-search-header
                    x-show="isSearchEnabled"
                    x-transition:enter="ease duration-[400ms] transform"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <x-slot:title>
                        {{ __('Quick Links') }}
                    </x-slot:title>
                </x-search-header>

                <ul class="space-y-4 ml-4 mr-4">
                    @foreach ($quickLinks as $key => $quickLink)
                        <li
                            x-show="isSearchEnabled"
                            x-bind:style="isSearchEnabled ? 'transition-duration: {{ $key * 50 + 500 }}ms;' : ''"
                            x-transition:enter="ease duration-100 transform"
                            x-transition:enter-start="opacity-0 translate-x-8"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                        >
                            @if (isset($quickLink['action']))
                                <x-footer-button
                                    class="inline-block w-full"
                                    wire:click="{{ $quickLink['action'] }}"
                                >
                                    {{ $quickLink['title'] }}
                                </x-footer-button>
                            @elseif (isset($quickLink['link']))
                                <x-footer-link
                                    class="inline-block w-full"
                                    href="{{ $quickLink['link'] }}"
                                >
                                    {{ $quickLink['title'] }}
                                </x-footer-link>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Overlay --}}
    <div
        class="fixed inset-0 transform transition-all backdrop-blur"
        style="z-index: 299;"
        x-cloak
        x-show="isSearchEnabled"
        x-on:click="resetAndClose()"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-black opacity-75"></div>
    </div>
</div>
