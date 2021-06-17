<main>
    <x-slot name="title">
        {!! $anime->original_title !!}
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
                    <img class="lg:h-full lg:object-cover" src="{{ asset('images/static/star_bg_lg.jpg') }}" alt="{{ $anime->original_title }} Banner" title="{{ $anime->original_title }}">
                </picture>
            </div>

            <div class="md:absolute md:bottom-0 md:left-0 md:right-0 lg:px-4">
                <div class="flex flex-no-wrap mx-5 pt-5 pb-8 md:mx-auto md:mb-8 md:p-6 md:max-w-lg md:bg-white md:bg-opacity-50 md:backdrop-filter md:backdrop-blur md:rounded-lg">
                    <picture class="relative w-1/4 h-full mr-2 rounded-lg overflow-hidden">
                        <img src="{{ $anime->poster()->url ?? asset('images/static/placeholders/anime_poster.jpg') }}" alt="{{ $anime->original_title }} Poster" title="{{ $anime->original_title }}">
                        <div class="absolute top-0 left-0 h-full w-full ring-1 ring-gray-100 ring-opacity-25 ring-inset rounded-lg"></div>
                    </picture>

                    <div class="flex flex-col gap-2 justify-between w-3/4">
                        <div>
                            <p class="font-semibold text-lg leading-tight break-all">{{ $anime->original_title }}</p>
                            <p class="text-sm leading-tight">{{ $anime->information_summary }}</p>
                            <x-pill color="{{ $anime->status->color() }}" class="mt-2">{{ $anime->status->name }}</x-pill>
                        </div>

                        <div class="flex flex-wrap gap-2 justify-between mt-5 h-10">
                            <livewire:anime.library-button :anime="$anime" />
                            @if($isTracking)
                                <div class="flex gap-2">
                                    <x-button class="!px-2 w-10 !bg-white text-yellow-300 rounded-full shadow-md hover:!bg-gray-100 hover:text-yellow-500 active:!bg-white active:text-yellow-300" wire:click="remindAnime">
                                        @if($isReminded)
                                            @svg('bell_fill', 'fill-current', ['width' => '44'])
                                        @else
                                            @svg('bell', 'fill-current', ['width' => '44'])
                                        @endif
                                    </x-button>
                                    <x-button class="!px-2 w-10 !bg-white text-red-500 rounded-full shadow-md hover:!bg-gray-100 hover:text-red-600 active:!bg-white active:text-red-500" wire:click="favoriteAnime">
                                        @if($isFavorited)
                                            @svg('heart_fill', 'fill-current', ['width' => '44'])
                                        @else
                                            @svg('heart', 'fill-current', ['width' => '44'])
                                        @endif
                                    </x-button>
                                </div>
                            @endif
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

                @if ($anime->air_season_string)
                    <div id="badge-2" class="flex-grow px-12 border-l-2">
                        <p class="font-bold">{{ $anime->air_season_string }}</p>
                        <p class="text-sm text-gray-500">{{ __('Season') }}</p>
                    </div>
                @endif

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

            @if (!empty($anime->synopsis))
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
            @endif

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

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-x-4 gap-y-4">
                    <x-information-list title="{{ __('Type') }}" icon="{{ asset('images/symbols/tv_and_mediabox.svg') }}">
                        <x-slot name="information">
                            {{ $anime->media_type->name }}
                        </x-slot>

                        <x-slot name="footer">
                            {{ $anime->media_type->description }}
                        </x-slot>
                    </x-information-list>

                    <x-information-list title="{{ __('Source') }}" icon="{{ asset('images/symbols/target.svg') }}">
                        <x-slot name="information">
                            {{ $anime->source->name }}
                        </x-slot>

                        <x-slot name="footer">
                            {{ $anime->source->description }}
                        </x-slot>
                    </x-information-list>

                    <x-information-list title="{{ __('Genres') }}" icon="{{ asset('images/symbols/theatermasks.svg') }}">
                        <x-slot name="information">
                            {{ $anime->genres->implode('name', ', ') ?: '-' }}
                        </x-slot>
                    </x-information-list>

                    @if (in_array($anime->media_type->name, ['Unknown', 'TV', 'ONA']))
                        <x-information-list title="{{ __('Episodes') }}" icon="{{ asset('images/symbols/film.svg') }}">
                            <x-slot name="information">
                                {{ $anime->episode_count }}
                            </x-slot>

                            <x-slot name="footer">
                                <p class="text-sm">{{ trans_choice('[0,1] Across one season.|[2,*] Across :count seasons.', $anime->season_count, ['count' => $anime->season_count]) }}</p>
                            </x-slot>
                        </x-information-list>
                    @endif

                    <x-information-list title="{{ __('Duration') }}" icon="{{ asset('images/symbols/hourglass.svg') }}">
                        <x-slot name="information">
                            {{ $anime->runtime_string ?? '-' }}
                        </x-slot>

                        <x-slot name="footer">
                            <p class="text-sm">{{ __('With a total of :count.', ['count' => $anime->runtime_total]) }}</p>
                        </x-slot>
                    </x-information-list>

                    <x-information-list title="{{ __('Broadcast') }}" icon="{{ asset('images/symbols/calendar_badge_clock.svg') }}">
                        <x-slot name="information">
                            {{ $anime->broadcast }}
                        </x-slot>

                        @if (empty($anime->broadcast))
                            {{ __('No broadcast data available at the moment.') }}
                        @else
                            <div class="flex flex-col align-center mt-1">
                                <p class="font-black text-2xl" wire:poll.1000ms>
                                    {{ $anime->time_until_broadcast }}
                                </p>
                            </div>
                        @endif
                    </x-information-list>

                    <x-information-list title="{{ __('Aired') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                        @if (!empty($anime->first_aired))
                            @if (empty($anime->last_aired))
                                <x-slot name="information">
                                    🚀 {{ $anime->first_aired->toFormattedDateString() }}
                                </x-slot>

                                <x-slot name="footer">
                                    {{ __('The show is :status.', ['status' => strtolower($anime->status->name)]) }}
                                </x-slot>
                            @else
                                <div class="flex flex-col">
                                        <p class="font-semibold text-2xl">🚀 {{ $anime->first_aired->toFormattedDateString() }}</p>

                                        @svg('dotted_line', 'fill-current', ['width' => '100%'])

                                        <p class="font-semibold text-2xl text-right">🏁 {{ $anime->last_aired?->toFormattedDateString() }}</p>
                                </div>
                            @endif
                        @else
                            {{ __('Airing dates are unknown.') }}
                        @endif
                    </x-information-list>

                    <x-information-list title="{{ __('Rating') }}" icon="{{ asset('images/symbols/tv_rating.svg') }}">
                        <x-slot name="information">
                            {{ $anime->tv_rating->name }}
                        </x-slot>

                        <x-slot name="footer">
                            <p class="text-sm">{{ $anime->tv_rating->description }}.</p>
                        </x-slot>
                    </x-information-list>

{{--                    <x-information-list title="{{ __('Studio') }}" icon="{{ asset('images/symbols/building_2.svg') }}">--}}
{{--                        <x-slot name="information">--}}
{{--                            {{ $anime->studios()->first()->name ?? '-' }}--}}
{{--                        </x-slot>--}}
{{--                    </x-information-list>--}}

{{--                    <x-information-list title="{{ __('Network') }}" icon="{{ asset('images/symbols/dot_radiowaves_left_and_right.svg') }}">--}}
{{--                        <x-slot name="information">--}}
{{--                            {{ $anime->studios()->first()->name ?? '-' }}--}}
{{--                        </x-slot>--}}
{{--                    </x-information-list>--}}
                </div>
            </section>

            <section class="pt-5 pb-2 border-t">
                <p class="text-sm text-gray-400">{{ $anime->copyright }}</p>
            </section>

            <x-dialog-modal maxWidth="md" wire:model="showPopup">
                <x-slot name="title">
                    {{ $popupData['title'] }}
                </x-slot>
                <x-slot name="content">
                    <p class="">{{ $popupData['message'] }}</p>
                </x-slot>
                <x-slot name="footer">
                    <x-button wire:click="$toggle('showPopup')">{{ __('Ok') }}</x-button>
                </x-slot>
            </x-dialog-modal>
        </div>
    </div>
</main>
