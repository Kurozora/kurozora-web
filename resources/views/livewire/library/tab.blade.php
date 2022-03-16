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
        <div class="sm:flex items-center mt-4">
            <div class="flex-1 mb-4">
                <x-input id="search" type="text" placeholder="{{ __('I’m searching for…') }}" wire:model.debounce.500ms="filter.search" />
            </div>

            <div class="flex flex-1 items-center justify-end space-x-1">
                <x-spinner />

                <x-dropdown align="right" width="48">
                    <x-slot:trigger>
                        <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            @svg('line_3_horizontal_decrease_circle', 'fill-current', ['aria-labelledby' => 'filter', 'width' => '28'])
                        </button>
                    </x-slot>

                    <x-slot:content>
                        {{-- Order --}}
                        <div class="block px-4 py-2 bg-gray-100 text-xs text-gray-400 font-semibold">
                            {{ __('Order') }}
                        </div>

                        <div class="block px-4 py-2">
                            <x-select id="orderType" wire:model="filter.order_type">
                                <option value="">{{ __('Default') }}</option>
                                <option value="asc">{{ __('A-Z') }}</option>
                                <option value="desc">{{ __('Z-A') }}</option>
                            </x-select>
                        </div>

                        {{-- Per Page --}}
                        <div class="block px-4 py-2 bg-gray-100 text-xs text-gray-400 font-semibold">
                            {{ __('Per Page') }}
                        </div>

                        <div class="block px-4 py-2">
                            <x-select id="perPage" wire:model="filter.per_page">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </x-select>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </section>

    @if(!empty($library))
        @if(!empty($library->total()))
            <section class="mt-4" wire:key="not-empty-{{ $userLibraryStatusString }}">
                <div class="grid gap-4 sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3">
                    @foreach($library as $anime)
                        <x-lockups.small-lockup :anime="$anime" wire:key="{{ $anime->id }}" />
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $library->links() }}
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
