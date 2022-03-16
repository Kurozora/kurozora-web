<div
    x-data="{
        searchQuery: @entangle('searchQuery'),
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
        focusOnSearch() { setTimeout(() => $refs.search.focus(), 0) },
    }"
    x-on:close.stop="resetAndClose()"
    x-on:keydown.escape.window="resetAndClose()"
    x-on:keydown.meta.k.window.prevent="isSearchEnabled = true"
    x-on:keydown.window.prevent.slash="isSearchEnabled = true"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-on:transitionstart="focusOnSearch()"
>
    <div class="absolute top-0 right-0 left-0 mx-auto max-w-full z-[300] sm:max-w-2xl"
         x-cloak=""
         x-show="isSearchEnabled"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-[400ms]"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
    >
        <div class="h-[64px]">
            <span
                class="absolute h-full w-full"
                x-show="isSearchEnabled"
                x-transition:enter="ease duration-[500ms] delay-300 transform"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                {{-- Search icon --}}
                <button class="absolute left-0 pl-4 h-full text-gray-500 sm:pl-3">
                    @svg('magnifyingglass', 'fill-current', ['width' => '16'])
                </button>

                {{-- Search field --}}
                <input
                    class="absolute top-0 left-0 px-10 h-full w-full border-0 bg-transparent text-black focus:ring-0"
                    type="text"
                    placeholder="{{ [__('I’m searching for…'), __('Search faster with ⌘+K, ctrl+K or /')][array_rand([0,1])] }}"
                    x-ref="search"
                    wire:model.debounce.500ms="searchQuery"
                />
            </span>

            {{-- Close button --}}
            <button
                class="absolute right-0 pr-4 h-full text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 sm:pr-2"
                x-show="isSearchEnabled"
                x-on:click="resetAndClose()"
                x-transition:enter="ease duration-[400ms] delay-[325ms] transform"
                x-transition:enter-start="opacity-0 translate-x-1"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Quick Links --}}
        <div class="absolute right-0 left-0 mx-auto p-4 max-w-7xl bg-white rounded-b-2xl sm:px-10">
            <div class="flex justify-center">
                <x-spinner wire:target="searchQuery" />
            </div>

            @if(!empty($searchResults))
                @foreach($searchResults as $key => $query)
                    @switch($key)
                        @case('anime')
                            @if(!empty($query->total()))
                                <x-search-header>{{ __('Anime') }}</x-search-header>

                                <div class="mt-4">
                                    @foreach($query as $key => $anime)
                                        <x-lockups.search-anime-lockup :anime="$anime" wire:key="{{ uniqid(md5($anime->title), true) }}" />

                                        <x-hr class="my-4" />
                                    @endforeach
                                </div>
                            @endif
                        @break
                        @case('users')
                            @if(!empty($query->total()))
                                <x-search-header>{{ __('Users') }}</x-search-header>

                                <div class="mt-4">
                                    @foreach($query as $user)
                                        <x-lockups.search-user-lockup :user="$user" wire:key="{{ uniqid(md5($user->username), true) }}" />

                                        <x-hr class="my-4" />
                                    @endforeach
                                </div>
                            @endif
                        @break
                    @endswitch
                @endforeach
            @endif

            @if($searchResultsTotal == 0 && !empty($searchQuery))
                <p class="text-sm text-gray-500 text-center font-bold" wire:key="no-results-found">{{ __('No search results found :(') }}</p>
            @endif

            @if(!empty($quickLinks))
                <x-search-header
                    x-show="isSearchEnabled"
                    x-transition:enter="ease duration-[400ms] transform"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >{{ __('Quick Links') }}</x-search-header>

                <ul class="space-y-4">
                    @foreach($quickLinks as $key => $quickLink)
                        <li
                            x-show="isSearchEnabled"
                            x-bind:style="isSearchEnabled ? 'transition-duration: {{ $key * 50 + 400 }}ms;' : ''"
                            x-transition:enter="ease duration-100 transform"
                            x-transition:enter-start="opacity-0 translate-x-8"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                        >
                            <x-footer-link class="inline-block w-full" href="{{ $quickLink['link'] }}">{{ $quickLink['title'] }}</x-footer-link>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Overlay --}}
    <div
        class="fixed inset-0 transform transition-all z-[299]"
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
