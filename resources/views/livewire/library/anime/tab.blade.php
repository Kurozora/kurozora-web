<div
    x-data="{
        loadResourceIsEnabled: @entangle('loadResourceIsEnabled').live,
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
    wire:init="loadSection"
>
    <section>
        <x-search-bar>
            <x-slot:rightBarButtonItems>
                <x-square-button wire:click="randomAnime">
                    @svg('dice', 'fill-current', ['aria-labelledby' => 'random anime from ' . strtolower($status) . ' library', 'width' => '28'])
                </x-square-button>
            </x-slot:rightBarButtonItems>
        </x-search-bar>
    </section>

    @if(!empty($this->searchResults))
        @if(!empty($this->searchResults->total()))
            <section class="mt-4" wire:key="not-empty-{{ strtolower($status) }}">
                <x-rows.small-lockup :animes="$this->searchResults" :is-row="false" />

                <div class="mt-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @else
            <section class="flex flex-col items-center mt-4 text-center" wire:key="empty-{{ strtolower($status) }}">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="Empty Library" title="Empty Library">
                </x-picture>

                <p class="font-bold">{{ __('No Shows') }}</p>

                <p class="text-sm text-gray-500">{{ __('Add a show to your :x list and it will show up here.', ['x' => strtolower($status)]) }}</p>
            </section>
        @endif
    @elseif (!$readyToLoad)
        <section class="mt-4 pt-5 pb-8 border-t-2">
            <div class="flex gap-4 justify-between flex-wrap">
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
            </div>
        </section>
    @endif
</div>
