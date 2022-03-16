<main>
    <x-slot:title>
        {{ __('Episodes') }} | {!! $season->title !!}
    </x-slot>

    <x-slot:description>
        {{ $season->synopsis ?? __('Discover the extensive list of :x episodes only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $season->anime->title]) }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Episodes') }} | {{ $season->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $season->synopsis ?? __('Discover the extensive list of :x episodes on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $season->anime->title]) }}" />
        <meta property="og:image" content="{{ $season->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $season->duration }}" />
        <meta property="video:release_date" content="{{ $season->first_aired }}" />
        <link rel="canonical" href="{{ route('seasons.episodes', $season) }}">
    </x-slot>

    <x-slot:appArgument>
        seasons/{{ $season->id }}/episodes
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <p class="text-2xl font-bold">{{ __(':x Episodes', ['x' => $season->title]) }}</p>

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

                                {{-- Hide fillers --}}
                                <div class="block px-4 py-2 bg-gray-100 text-xs text-gray-400 font-semibold">
                                    {{ __('Extras') }}
                                </div>

                                <div class="block px-4 py-2">
                                    <x-checkbox wire:model="filter.hide_fillers">{{ __('Hide fillers') }}</x-checkbox>
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3">
            @foreach($episodes as $episode)
                <x-lockups.episode-lockup :episode="$episode" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $episodes->links() }}
        </section>
    </div>
</main>
