<div
    x-data="{
        loadResource() {
            if (tab === '#{{ strtolower($userLibraryStatusString) }}' && !$wire.loadResourceIsEnabled) {
                @this.call('loadResource');
                return true;
            }

            return tab === '#{{ strtolower($userLibraryStatusString) }}';
        }
    }"
    x-show="tab === '#{{ strtolower($userLibraryStatusString) }}' && loadResource()"
>
    <section>
        <x-search-bar>
            <x-slot:rightBarButtonItems>
                <x-square-button wire:click="randomAnime">
                    @svg('dice', 'fill-current', ['aria-labelledby' => 'random anime from ' . strtolower($userLibraryStatusString) . ' library', 'width' => '28'])
                </x-square-button>
            </x-slot>
        </x-search-bar>
    </section>

    @if(!empty($this->searchResults))
        @if(!empty($this->searchResults->total()))
            <section class="mt-4" wire:key="not-empty-{{ $userLibraryStatusString }}">
                <div class="grid gap-4 sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3">
                    @foreach($this->searchResults as $anime)
                        <x-lockups.small-lockup :anime="$anime" wire:key="{{ $anime->id }}" />
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @else
            <section class="flex flex-col items-center mt-4 text-center" wire:key="empty-{{ $userLibraryStatusString }}">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_library.webp') }}" alt="Empty Library" title="Empty Library">
                </x-picture>

                <p class="font-bold">{{ __('No Shows') }}</p>

                <p class="text-md text-gray-500">{{ __('Add a show to your :x list and it will show up here.', ['x' => $userLibraryStatusString]) }}</p>
            </section>
        @endif
    @endif
</div>
