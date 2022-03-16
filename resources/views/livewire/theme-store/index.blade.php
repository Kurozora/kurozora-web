<main>
    <x-slot:title>
        {{ __('Theme Store') }}
    </x-slot>

    <x-slot:description>
        {{ __('Discover the extensive list of platform themes only on Kurozora, the largest, free online anime, manga & music database in the world.') }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Theme Store') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of platform themes only on Kurozora, the largest, free online anime, manga & music database in the world.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('theme-store.index') }}">
    </x-slot>

    <x-slot:appArgument>
        theme-store
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <p class="text-2xl font-bold">{{ __('Theme Store') }}</p>

                    <div class="flex flex-wrap justify-end items-center w-full">
                        <x-link-button href="{{ route('theme-store.create') }}">{{ __('Create') }}</x-link-button>
                    </div>
                </div>

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
            </div>
        </section>

        <section class="grid gap-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach($platformThemes as $platformTheme)
                <x-lockups.platform-theme-lockup :theme="$platformTheme" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $platformThemes->links() }}
        </section>
    </div>
</main>
