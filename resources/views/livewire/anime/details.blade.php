<main>
    <x-slot name="title">
        {!! $anime->title !!}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $anime->synopsis }}" />
        <meta property="og:image" content="{{ $anime->banner_image_url ?? $anime->poster_image_url ?? asset('images/static/placeholders/anime_banner.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->first_aired }}" />
    </x-slot>

    <x-slot name="appArgument">
        anime/{{ $anime->id }}
    </x-slot>

    <div class="grid grid-rows-[repeat(2,minmax(0,min-content))] h-full lg:grid-rows-none lg:grid-cols-2 2xl:grid-cols-3 lg:mb-0">
        <div class="relative">
            <div class="flex flex-no-wrap aspect-ratio-16-9 md:relative md:h-full lg:aspect-ratio-auto">
                <picture class="relative w-full overflow-hidden">
                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->banner_image_url ?? $anime->poster_image_url ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">
                </picture>
            </div>

            <div class="md:absolute md:bottom-0 md:left-0 md:right-0 lg:px-4">
                <div class="flex flex-no-wrap pt-5 pb-8 px-4 md:mx-auto md:mb-8 md:p-6 md:max-w-lg md:bg-white md:bg-opacity-50 md:backdrop-filter md:backdrop-blur md:rounded-lg">
                    <picture class="relative min-w-[100px] max-w-[100px] min-h-[150px] max-h-[150px] mr-2 rounded-lg overflow-hidden">
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">
                        <div class="absolute top-0 left-0 h-full w-full ring-1 ring-gray-100 ring-opacity-25 ring-inset rounded-lg"></div>
                    </picture>

                    <div class="flex flex-col gap-2 justify-between w-3/4">
                        <div>
                            <p class="font-semibold text-lg leading-tight break-all">{{ $anime->title }}</p>
                            <p class="text-sm leading-tight">{{ $anime->information_summary }}</p>
                            <x-pill color="{{ $anime->status->color }}" class="mt-2">{{ $anime->status->name }}</x-pill>
                        </div>

                        <div class="flex flex-wrap gap-1 justify-between h-10">
                            <livewire:anime.library-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
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

        <div class="pt-4 2xl:col-span-2 lg:max-h-[calc(100vh-64px)] overflow-x-hidden overflow-y-scroll no-scrollbar">
            <section id="badges" class="flex flex-row flex-nowrap whitespace-nowrap justify-between text-center pb-5 px-4 overflow-x-scroll no-scrollbar">
                <div id="ratingBadge" class="flex-grow pr-12">
                    <a href="#ratingsAndReviews">
                        <p class="inline-flex font-bold text-orange-500">
                            {{ number_format($anime->rating_average, 1) }}
                            <livewire:anime.star-rating :rating="$anime->rating_average" :star-size="'sm'" :disabled="true" />
                        </p>
                        <p class="text-sm text-gray-500">{{ __('Not enough ratings') }}</p>
                    </a>
                </div>

                @if ($anime->air_season_string)
                    <div id="seasonBadge" class="flex-grow px-12 border-l-2">
                        <a href="#aired">
                            <p class="font-bold">{{ $anime->air_season_string }}</p>
                            <p class="text-sm text-gray-500">{{ __('Season') }}</p>
                        </a>
                    </div>
                @endif

                <div id="rankingBadge" class="flex-grow px-12 border-l-2">
                    <a href="#genres">
                        <p class="font-bold">#13</p>
                        <p class="text-sm text-gray-500">{{ __('Thriller') }}</p>
                    </a>
                </div>

                <div id="tvRatingBadge" class="flex-grow px-12 border-l-2">
                    <a href="#tvRating">
                        <p class="font-bold">{{ $anime->tv_rating->name }}</p>
                        <p class="text-sm text-gray-500">{{ __('Rated') }}</p>
                    </a>
                </div>

                @if (!empty($studio))
                    <div id="studioBadge" class="flex-grow px-12 border-l-2">
                        <a href="{{ route('studios.details', $studio) }}">
                            <p class="font-bold">{{ $studio->name }}</p>
                            <p class="text-sm text-gray-500">{{ __('Studio') }}</p>
                        </a>
                    </div>
                @endif

                <div id="languageBadge" class="flex-grow px-12 border-l-2">
                    <a href="#languages">
                        <p class="font-bold">{{ strtoupper($anime->languages->first()->code) }}</p>
                        <p class="text-sm text-gray-500">{{ trans_choice('{0} Language|{1} +:x More Language|[2,*] +:x More Languages', $anime->languages->count() - 1, ['x' => $anime->languages->count() - 1]) }}</p>
                    </a>
                </div>
            </section>

            @if (!empty($anime->synopsis))
                <section class="pt-5 pb-8 px-4 border-t-2">
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

            <section id="ratingsAndReviews" class="pt-5 pb-8 px-4 border-t-2">
                <x-section-nav>
                    <x-slot name="title">
                        {{ __('Ratings & Reviews') }}
                    </x-slot>
                </x-section-nav>

                <div class="flex flex-row justify-between">
                    <div class="text-center">
                        <p class="font-bold text-6xl">{{ number_format($anime->rating_average, 1) }}</p>
                        <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                    </div>

                    @auth
                        <div class="text-right">
                            <livewire:anime.star-rating :anime="$anime" :rating="Auth::user()->animeRating()->firstWhere('anime_id', $anime->id)?->rating" :star-size="'lg'" />
                            <p class="text-sm text-gray-500">{{ __('Not enough ratings') }}</p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="pt-5 pb-8 px-4 border-t-2">
                <x-section-nav>
                    <x-slot name="title">
                        {{ __('Information') }}
                    </x-slot>
                </x-section-nav>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-x-4 gap-y-4">
                    <x-information-list id="type" title="{{ __('Type') }}" icon="{{ asset('images/symbols/tv_and_mediabox.svg') }}">
                        <x-slot name="information">
                            {{ $anime->media_type->name }}
                        </x-slot>

                        <x-slot name="footer">
                            {{ $anime->media_type->description }}
                        </x-slot>
                    </x-information-list>

                    <x-information-list id="source" title="{{ __('Source') }}" icon="{{ asset('images/symbols/target.svg') }}">
                        <x-slot name="information">
                            {{ $anime->source->name }}
                        </x-slot>

                        <x-slot name="footer">
                            {{ $anime->source->description }}
                        </x-slot>
                    </x-information-list>

                    <x-information-list id="genres" title="{{ __('Genres') }}" icon="{{ asset('images/symbols/theatermasks.svg') }}">
                        <x-slot name="information">
                            {{ $anime->genres?->pluck('name')->join(',  ', ' and ') ?: '-' }}
                        </x-slot>
                    </x-information-list>

                    @if (in_array($anime->media_type->name, ['Unknown', 'TV', 'ONA']))
                        <x-information-list id="episodes" title="{{ __('Episodes') }}" icon="{{ asset('images/symbols/film.svg') }}">
                            <x-slot name="information">
                                {{ $anime->episode_count }}
                            </x-slot>

                            <x-slot name="footer">
                                <p class="text-sm">{{ trans_choice('[0,1] Across one season.|[2,*] Across :count seasons.', $anime->season_count, ['count' => $anime->season_count]) }}</p>
                            </x-slot>
                        </x-information-list>
                    @endif

                    <x-information-list id="duration" title="{{ __('Duration') }}" icon="{{ asset('images/symbols/hourglass.svg') }}">
                        <x-slot name="information">
                            {{ $anime->duration_string ?? '-' }}
                        </x-slot>

                        <x-slot name="footer">
                            <p class="text-sm">{{ __('With a total of :count.', ['count' => $anime->duration_total]) }}</p>
                        </x-slot>
                    </x-information-list>

                    <x-information-list id="broadcast" title="{{ __('Broadcast') }}" icon="{{ asset('images/symbols/calendar_badge_clock.svg') }}">
                        <x-slot name="information">
                            {{ $anime->broadcast }}
                        </x-slot>

                        @if (!empty($anime->last_aired))
                            <x-slot name="footer">
                                {{ __('The broadcasting of this series has ended.') }}
                            </x-slot>
                        @elseif (empty($anime->broadcast))
                            {{ __('No broadcast data available at the moment.') }}
                        @else
                            <div class="flex flex-col align-center mt-1">
                                <p class="font-black text-2xl">
                                    {{ $anime->time_until_broadcast }}
                                </p>
                            </div>
                        @endif
                    </x-information-list>

                    <x-information-list id="aired" title="{{ __('Aired') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                        @if (!empty($anime->first_aired))
                            @if (empty($anime->last_aired))
                                <x-slot name="information">
                                    🚀 {{ $anime->first_aired->toFormattedDateString() }}
                                </x-slot>

                                <x-slot name="footer">
                                    {{ __($anime->status->description) }}
                                </x-slot>
                            @else
                                <div class="flex flex-col">
                                        <p class="font-semibold text-2xl">🚀 {{ $anime->first_aired->toFormattedDateString() }}</p>

                                        @svg('dotted_line', 'fill-current', ['width' => '100%'])

                                        <p class="font-semibold text-2xl text-right">{{ $anime->last_aired?->toFormattedDateString() }} 🏁</p>
                                </div>
                            @endif
                        @else
                            <x-slot name="information">
                                -
                            </x-slot>
                            <x-slot name="footer">
                                {{ __('Airing dates are unknown.') }}
                            </x-slot>
                        @endif
                    </x-information-list>

                    <x-information-list id="tvRating" title="{{ __('Rating') }}" icon="{{ asset('images/symbols/tv_rating.svg') }}">
                        <x-slot name="information">
                            {{ $anime->tv_rating->name }}
                        </x-slot>

                        <x-slot name="footer">
                            <p class="text-sm">{{ $anime->tv_rating->description }}.</p>
                        </x-slot>
                    </x-information-list>

                    <x-information-list id="languages" title="{{ __('Languages') }}" icon="{{ asset('images/symbols/globe.svg') }}">
                        <x-slot name="information">
                            {{ $anime->languages->pluck('name')->join(',  ', ' and ') ?: '-' }}
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

            @if (!empty($seasons))
                <section class="pt-5 pb-8 px-4 border-t-2">
                    <x-section-nav>
                        <x-slot name="title">
                            {{ __('Seasons') }}
                        </x-slot>

                        <x-slot name="action">
                            <x-simple-link href="{{ route('anime.seasons', $anime) }}">{{ __('See All') }}</x-simple-link>
                        </x-slot>
                    </x-section-nav>

                    <div class="grid grid-flow-col-dense gap-4 overflow-x-scroll no-scrollbar">
                        @foreach($seasons as $season)
                            <x-lockups.poster-lockup :season="$season" />
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="bg-orange-50">
                @if (!empty($moreByStudio))
                    <section id="moreByStudio" class="pt-5 pb-8 px-4 border-t-2">
                        <x-section-nav>
                            <x-slot name="title">
                                {{ __('More By :x', ['x' => $studio->name]) }}
                            </x-slot>

                            <x-slot name="action">
                                <x-simple-link href="{{ route('studios.details', $studio) }}">{{ __('See All') }}</x-simple-link>
                            </x-slot>
                        </x-section-nav>

                        <div class="grid grid-flow-col-dense gap-4 overflow-x-scroll no-scrollbar">
                            @foreach($moreByStudio as $moreByStudioAnime)
                                <x-lockups.small-lockup :anime="$moreByStudioAnime" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (!empty($animeRelations))
                    <section id="related" class="pt-5 pb-8 px-4 border-t-2">
                        <x-section-nav>
                            <x-slot name="title">
                                {{ __('Related') }}
                            </x-slot>

                            <x-slot name="action">
                                <x-simple-link href="{{ route('anime.related-shows', $anime) }}">{{ __('See All') }}</x-simple-link>
                            </x-slot>
                        </x-section-nav>

                        <div class="grid grid-flow-col-dense gap-4 overflow-x-scroll no-scrollbar">
                            @foreach($animeRelations as $relatedAnime)
                                <x-lockups.small-lockup :anime="$relatedAnime->related" :relation="$relatedAnime->relation" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (!empty($anime->copyright))
                    <section class="p-4 border-t-[1px]">
                        <p class="text-sm text-gray-400">{{ $anime->copyright }}</p>
                    </section>
                @endif
            </div>

            <x-dialog-modal maxWidth="md" wire:model="showPopup">
                <x-slot name="title">
                    {{ $popupData['title'] }}
                </x-slot>
                <x-slot name="content">
                    <p>{{ $popupData['message'] }}</p>
                </x-slot>
                <x-slot name="footer">
                    <x-button wire:click="$toggle('showPopup')">{{ __('Ok') }}</x-button>
                </x-slot>
            </x-dialog-modal>
        </div>
    </div>
</main>
