<main>
    <x-slot name="title">
        {{ $anime->title }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $page['title'] }}" />
        <meta property="og:image" content="{{ $page['image'] }}" />
        <meta property="og:type" content="{{ $page['type'] }}" />
    </x-slot>

    <x-slot name="appArgument">
        anime/{{ $anime->id }}
    </x-slot>

    <div class="grid grid-rows-[repeat(2,minmax(0,min-content))] mb-4 h-full lg:grid-rows-none lg:grid-cols-2 2xl:grid-cols-3 lg:mb-0">
        <div class="relative">
            <div class="flex flex-no-wrap md:relative md:h-full">
                <picture class="relative overflow-hidden">
                    <img class="lg:h-full lg:object-cover" src="{{ asset('images/static/star_bg_lg.jpg') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">
                </picture>
            </div>

            <div class="md:absolute md:bottom-0 md:left-0 md:right-0 lg:px-4">
                <div class="flex flex-no-wrap mx-5 pt-5 pb-8 md:mx-auto md:mb-8 md:p-6 md:max-w-lg md:bg-white md:bg-opacity-50 md:backdrop-filter md:backdrop-blur md:rounded-lg">
                    <picture class="relative w-1/4 h-full mr-2 rounded-lg overflow-hidden">
                        <img src="{{ $anime->poster()->url ?? asset('images/static/placeholders/anime_poster.jpg') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">
                        <div class="absolute top-0 left-0 h-full w-full ring-1 ring-gray-100 ring-opacity-25 ring-inset rounded-lg"></div>
                    </picture>

                    <div class="flex flex-col justify-between w-3/4">
                        <div>
                            <p class="font-semibold text-lg leading-tight break-all">{{ $anime->title }} asdas dasd asd asda sdas dasdasdasdasdasdasdasdasdasdasdasdasdas asda sd a</p>
                            <p class="text-sm leading-tight">{{ $anime->informationSummary }}</p>
                            @php
                                $airingStatus = App\Enums\AnimeStatus::fromValue($anime->air_status);
                            @endphp
                            <x-pill color="{{ $airingStatus->color() }}" class="mt-2">{{ $airingStatus->description }}</x-pill>
                        </div>
                        <div class="flex justify-between mt-5 h-10">
                            <x-button class="rounded-full shadow-md">{{ __('ADD') }}</x-button>
                            <div>
                                <x-button class="mr-2 !px-2 w-10 !bg-white text-yellow-300 rounded-full shadow-md hover:!bg-gray-50 active:!bg-gray-100">
                                    @svg('bell_fill', 'fill-current', ['width' => '44'])
                                </x-button>
                                <x-button class="!px-2 w-10 !bg-white text-red-500 rounded-full shadow-md hover:!bg-gray-50 active:!bg-gray-100">
                                    @svg('heart_fill', 'fill-current', ['width' => '44'])
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mx-5 2xl:col-span-2 lg:max-h-[calc(100vh-64px)] overflow-x-hidden overflow-y-scroll no-scrollbar">
            <section id="badges" class="flex flex-row flex-nowrap whitespace-nowrap justify-between text-center pb-5 overflow-x-scroll no-scrollbar">
                <div id="badge-1" class="flex-grow pr-12">
                    <p class="inline-flex font-bold text-orange-500">
                        2.5
                        <x-star-rating star-size="sm" />
                    </p>
                    <p class="text-sm text-gray-500">187K {{ __('Ratings') }}</p>
                </div>

                <div id="badge-2" class="flex-grow px-12 border-l-2">
                    <p class="font-bold">#13</p>
                    <p class="text-sm text-gray-500">{{ __('Thriller') }}</p>
                </div>

                <div id="badge-2" class="flex-grow px-12 border-l-2">
                    <p class="font-bold">{{ $anime->tv_rating->name }}</p>
                    <p class="text-sm text-gray-500">{{ __('Rated') }}</p>
                </div>

                @if ($anime->studios()->count())
                    <div id="badge-2" class="flex-grow px-12 border-l-2">
                        <p class="font-bold">{{ $anime->studios()->first()->name }}</p>
                        <p class="text-sm text-gray-500">{{ __('Studio') }}</p>
                    </div>
                @endif
            </section>

            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav class="flex flex-no-wrap justify-between mb-5">
                    <x-slot name="title">
                        {{ __('Synopsis') }}
                    </x-slot>
                </x-section-nav>

                <x-truncated-text>
                    <x-slot name="text">
                        {!! nl2br($anime->synopsis) !!}
                    </x-slot>
                </x-truncated-text>
            </section>

            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot name="title">
                        {{ __('Ratings') }}
                    </x-slot>
                </x-section-nav>

                <div class="flex flex-row justify-between">
                    <div class="text-center">
                        <p class="font-bold text-6xl">2.5</p>
                        <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                    </div>

                    <div class="text-right">
                        <x-star-rating star-size="lg" />
                        <p class="text-sm text-gray-500">{{ __('Not enough ratings') }}</p>
                    </div>
                </div>
            </section>

            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot name="title">
                        {{ __('Information') }}
                    </x-slot>
                </x-section-nav>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-x-10 gap-y-4">
                    <x-information-list label="{{ __('Studio') }}" infromation="{{ $anime->studios()->first()->name ?? '-' }}" />
                    <x-information-list label="{{ __('Network') }}" infromation="{{ $anime->network ?? '-' }}" />
                    <x-information-list label="{{ __('Type') }}" infromation="{{ App\Enums\AnimeType::fromValue($anime->type ?? 0)->description }}" />
                    <x-information-list label="{{ __('Aired') }}" infromation="{{ $anime->first_aired ?? 'N/A' }} - {{ $anime->last_aired ?? 'N/A' }}" />
                    <x-information-list label="{{ __('Broadcast') }}" infromation="{{ $anime->broadcast }}" />
                    <x-information-list label="{{ __('Genres') }}" infromation="{{ $anime->genres ?? '-' }}" />
                    <x-information-list label="{{ __('Rating') }}" infromation="{{ $anime->tv_rating->full_name }}" />
                    <x-information-list label="{{ __('Seasons') }}" infromation="{{ $anime->seasons_count ?? '-' }}" />
                    <x-information-list label="{{ __('Episodes') }}" infromation="{{ $anime->episodes_count ?? '-' }}" />
                    <x-information-list label="{{ __('Duration') }}" infromation="{{ $anime->runtime ?? '-' }}" />
                </div>
            </section>

            <section class="pt-5 pb-2 border-t">
                <p class="text-sm text-gray-400">{{ $anime->copyright }}</p>
            </section>
        </div>
    </div>
</main>
