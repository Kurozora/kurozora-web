<div
    x-data="{
        loadResourceIsEnabled: @entangle('loadResourceIsEnabled'),
        loadResource() {
            if (selectedStatus.toLowerCase() === '{{ strtolower($status) }}' && !this.loadResourceIsEnabled) {
                @this.call('loadResource')
                return true;
            }

            return selectedStatus.toLowerCase() === '{{ strtolower($status) }}'
        }
    }"
    x-show="selectedStatus.toLowerCase() === '{{ strtolower($status) }}' && loadResource()"
    x-cloak=""
>
    <section>
        <x-search-bar>
            <x-slot:rightBarButtonItems>
                <x-square-button wire:click="randomManga">
                    @svg('dice', 'fill-current', ['aria-labelledby' => 'random manga from ' . strtolower($status) . ' library', 'width' => '28'])
                </x-square-button>
            </x-slot:rightBarButtonItems>
        </x-search-bar>
    </section>

    @if(!empty($this->searchResults))
        @if(!empty($this->searchResults->total()))
            <section class="mt-4" wire:key="not-empty-{{ strtolower($status) }}">
                <x-rows.small-lockup :mangas="$this->searchResults" :is-row="false" />

                <div class="mt-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @else
            <section class="flex flex-col items-center mt-4 text-center" wire:key="empty-{{ strtolower($status) }}">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_manga_library.webp') }}" alt="Empty Library" title="Empty Library">
                </x-picture>

                <p class="font-bold">{{ __('No Mangas') }}</p>

                <p class="text-md text-gray-500">{{ __('Add a manga to your :x list and it will show up here.', ['x' => strtolower($status)]) }}</p>
            </section>
        @endif
    @endif
</div>