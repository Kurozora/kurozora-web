<main>
    <x-slot name="title">
        {{ __('Anime Characters') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Discover the extensive list of anime characters only on Kurozora, the largest, free online anime and manga database in the world.') }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Anime Characters') }} — {{ config('app.name') }}" />
        <meta property="og:og:description" content="{{ __('Discover the extensive list of anime characters only on Kurozora, the largest, free online anime and manga database in the world.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('characters.index') }}">
    </x-slot>

    <x-slot name="appArgument">
        characters
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <p class="text-2xl font-bold">{{ __('Characters') }}</p>

                <div
                    class="flex justify-between mt-4"
                    x-data="{
                        isFilterOpen: @entangle('isFilterOpen')
                    }"
                >
                    <div>
                        <x-input id="search" type="text" placeholder="{{ __('I’m searching for…') }}" wire:model.debounce.500ms="filter.search" />
                    </div>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                                x-on:click="$toggle('isFilterOpen')"
                            >
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

        <section class="grid grid-cols-2 gap-4 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7">
            @foreach($this->characters as $character)
                <x-lockups.character-lockup :character="$character" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $this->characters->links() }}
        </section>
    </div>
</main>
