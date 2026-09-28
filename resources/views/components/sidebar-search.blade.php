<div data-search-scope
    class="relative w-full"
    x-data="{
        isSearchEnabled: false,
        searchQuery: '',
        resetAndClose() {
            isSearchEnabled = false;
            this.searchQuery = '';
            window.searchManager?.reset($el);
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
        handleSlashShortcut(event) {
            const target = event.target

            if (target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT' || target.isContentEditable)) {
                return
            }

            event.preventDefault()
            this.focusOnSearch()
        },
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
    x-on:keydown.slash.window="handleSlashShortcut($event)"
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
            x-model="searchQuery"
            data-search-input
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
            <x-spinner :wire-loading-enabled="false" data-search-spinner class="hidden" />
        </div>

        <div data-search-results>
            @include('search.suggestions', ['quickLinks' => []])
        </div>
    </div>
</div>
