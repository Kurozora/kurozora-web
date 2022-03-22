<main>
    <x-slot:title>
        {{ __('Anime') }}
    </x-slot>

    <x-slot:description>
        {{ __('Browse all anime on Kurozora. Join the Kurozora community and create your anime and manga list. Discover songs, games and read reviews and news!') }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Anime') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse all anime on Kurozora. Join the Kurozora community and create your anime and manga list. Discover songs, games and read reviews and news!') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('anime.index') }}">
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <p class="text-2xl font-bold">{{ __('Anime') }}</p>

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

        <section class="mt-4">
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach($this->animes as $anime)
                    <x-lockups.small-lockup :anime="$anime" :is-row="false" />
                @endforeach
            </div>
        </section>

        <section class="mt-4">
            {{ $this->animes->links() }}
        </section>
    </div>
</main>
