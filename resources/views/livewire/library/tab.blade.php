<div
    x-data="{
        isShown: false,
        determineIsShown() {
            if (tab === '#{{ strtolower($userLibraryStatusString) }}' && !this.isShown) {
                this.isShown = true

            }
            return tab === '#{{ strtolower($userLibraryStatusString) }}'
        }
    }"
    x-init="
        $watch('isShown', function (newValue) {
            if (newValue) {
                @this.call('loadResource')
            }
        });
    "
    x-show="determineIsShown()"
    x-cloak
>
    <section>
        <div>
            <div class="flex justify-between mt-4">
                <div>
                    <x-input id="search" type="text" placeholder="{{ __('I’m searching for…') }}" wire:model.debounce.500ms="filter.search" />
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            @svg('line_3_horizontal_decrease_circle', 'fill-current', ['aria-labelledby' => 'filter', 'width' => '28'])
                        </button>
                    </x-slot>

                    <x-slot name="content">
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

        <div class="flex justify-center p-4 pb-0">
            <div class="mb-4" wire:loading.delay.shortest>
                <svg class="animate-spin h-6 w-6 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
            </div>
        </div>
    </section>

    @if(!empty($library))
        @if(!empty($library->total()))
            <section class="grid gap-4 mt-4 sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3">
                @foreach($library as $anime)
                    <x-lockups.small-lockup :anime="$anime" wire:key="{{ $anime->id }}" />
                @endforeach
            </section>

            <section class="mt-4">
                {{ $library->links() }}
            </section>
        @else
            <section class="flex flex-col items-center mt-4 text-center">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_library.webp') }}" alt="Empty Library" title="Empty Library">
                </x-picture>

                <p class="font-bold">{{ __('No Shows') }}</p>

                <p class="text-md text-gray-500">{{ __('Add a show to your :x list and it will show up here.', ['x' => $userLibraryStatusString]) }}</p>
            </section>
        @endif
    @endif
</div>