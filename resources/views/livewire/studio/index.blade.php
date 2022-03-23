<main>
    <x-slot:title>
        {{ __('Studios') }}
    </x-slot>

    <x-slot:description>
        {{ __('Discover an extensive list of anime studios, producers, and networks only on Kurozora, the largest, free online anime, manga & music database in the world.') }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Studios') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of anime studios, producers, and networks only on Kurozora, the largest, free online anime, manga & music database in the world.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('studios.index') }}">
    </x-slot>

    <x-slot:appArgument>
        studios
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <p class="text-2xl font-bold">{{ __('Studios') }}</p>

                <div class="sm:flex items-center mt-4">
                    <div class="flex-1 mb-4">
                        <x-input id="search" type="text" placeholder="{{ __('I’m searching for…') }}" wire:model.debounce.500ms="filter.search" />
                    </div>

                    <div class="flex flex-1 items-center justify-end space-x-1">
                        <x-spinner wire:target="filter" />

                        <x-square-button wire:click="randomStudio">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random anime', 'width' => '28'])
                        </x-square-button>

                        <x-dropdown align="right" width="48">
                            <x-slot:trigger>
                                <x-square-button>
                                    @svg('line_3_horizontal_decrease_circle', 'fill-current', ['aria-labelledby' => 'filter', 'width' => '28'])
                                </x-square-button>
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

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($this->studios as $studio)
                <x-lockups.studio-lockup :studio="$studio" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $this->studios->links() }}
        </section>
    </div>
</main>
