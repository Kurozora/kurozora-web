<main>
    <x-slot:themeColor>
        {{ ($anime->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $anime->getFirstMedia(\App\Enums\MediaCollection::Poster))?->custom_properties['background_color'] ?? null }}
    </x-slot:themeColor>

    <x-slot:title>
        {!! $anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ $anime->synopsis }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $anime->synopsis ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:video" content="{{ $anime->video_url ?? '' }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->started_at?->toIso8601String() }}" />
        @foreach($anime->tags() as $tag)
            <meta property="video:tag" content="{{ $tag->name }}" />
        @endforeach
        <meta property="twitter:title" content="{{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $anime->synopsis }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $anime->synopsis }}" />
        <link rel="canonical" href="{{ route('anime.details', $anime) }}">
        <x-misc.schema>
            "@type":"TVSeries",
            "url":"/anime/{{ $anime->slug }}/",
            "name": "{{ $anime->title }}",
            "alternateName": "{{ $anime->original_title }}",
            "image": "{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
            "description": "{{ str_replace('"', "'", $anime->synopsis) }}",
            "aggregateRating": {
                "@type":"AggregateRating",
                "itemReviewed": {
                    "@type": "TVSeries",
                    "image": [
                        "{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}"
                    ],
                    "name": "{{ $anime->title }}"
                },
                "ratingCount": {{ $anime->mediaStat->rating_count ?? 1 }},
                "bestRating": 5,
                "worstRating": 0,
                "ratingValue": {{ $anime->mediaStat->rating_average ?? 2.5 }}
            },
            "contentRating": "{{ $anime->tv_rating->name }}",
            "genre": {!! $anime->genres()->pluck('name') !!},
            "datePublished": "{{ $anime->started_at?->format('Y-m-d') }}",
            "keywords": "anime{{ (',' . $anime->keywords) ?? '' }}"
            @if (!empty($this->studio))
                ,"creator":[
                    {
                        "@type":"Organization",
                        "url":"/studio/{{ $this->studio->id }}/"
                    }
                ]
            @endif
            @if(!empty($anime->video_url))
                ,"trailer": {
                    "@type":"VideoObject",
                    "name":"{{ $anime->title }}",
                    "embedUrl": "{{ $anime->video_url }}",
                    "description":"Official Trailer",
                    "thumbnailUrl": "{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
                    "uploadDate": "{{ $anime->started_at?->format('Y-m-d') }}"
                }
            @endif
        </x-misc.schema>
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}
    </x-slot:appArgument>

    <div class="grid grid-rows-[repeat(2,minmax(0,min-content))] h-full xl:grid-rows-none xl:grid-cols-2 2xl:grid-cols-3 xl:mb-0">
        <div class="relative">
            <div class="relative flex flex-nowrap aspect-video md:relative md:h-full xl:aspect-auto">
                <x-picture class="w-full overflow-hidden">
                    <img class="w-full h-full aspect-video object-cover lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">
                </x-picture>

                @if (!empty($anime->video_url))
                    <div class="absolute top-0 bottom-0 left-0 right-0">
                        <div class="flex flex-col justify-center items-center h-full md:pb-40 lg:pb-0">
                            <button
                                class="inline-flex items-center pt-5 pr-5 pb-5 pl-5 bg-white/60 backdrop-blur border border-transparent rounded-full font-semibold text-xs text-gray-500 uppercase tracking-widest shadow-md hover:opacity-75 active:opacity-50 focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                                wire:click="showVideo"
                            >
                                @svg('play_fill', 'fill-current', ['width' => '34'])
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="md:absolute md:bottom-0 md:left-0 md:right-0 lg:px-4">
                <div class="flex flex-nowrap pt-5 pb-8 pl-4 pr-4 md:mx-auto md:mb-8 md:p-6 md:max-w-lg md:bg-white md:bg-opacity-50 md:backdrop-filter md:backdrop-blur md:rounded-lg">
                    <x-picture :border="true" class="min-w-[100px] max-w-[100px] min-h-[150px] max-h-[150px] mr-2 rounded-lg overflow-hidden">
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">
                    </x-picture>

                    <div class="flex flex-col gap-2 justify-between w-3/4">
                        <div>
                            <p class="font-semibold text-lg leading-tight break-all">{{ $anime->title }}</p>

                            <p class="text-sm leading-tight">{{ $anime->information_summary }}</p>

                            <div class="flex w-full justify-between mt-2 gap-1 sm:gap-4">
                                <p class="flex-grow pt-1 pr-1 pb-1 pl-1 text-white text-center text-xs font-semibold whitespace-nowrap rounded-md sm:text-sm" style="background-color: {{ $anime->status->color }};">{{ $anime->status->name }}</p>

                                <p class="flex-grow pt-1 pr-1 pb-1 pl-1 bg-gray-100 text-center text-xs font-semibold whitespace-nowrap rounded-md sm:text-sm"> {{ trans_choice('{0} Rank -|[1,*] Rank #:x', $anime->mediaStat->rank_total ?? 0, ['x' => $anime->mediaStat->rank_total]) }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1 justify-between">
                            <div class="flex gap-2">
                                <livewire:anime.library-button :anime="$anime" wire:key="{{ uniqid($anime->id, true) }}" />

                               <x-nova-link :resource="\App\Nova\Anime::class" :model="$anime">
                                   @svg('pencil', 'fill-current', ['width' => '44'])
                               </x-nova-link>
                            </div>

                            <div class="flex gap-2">
                                @if($isTracking)
                                    <x-circle-button color="yellow" wire:click="remindAnime">
                                        @if($isReminded)
                                            @svg('bell_fill', 'fill-current', ['width' => '44'])
                                        @else
                                            @svg('bell', 'fill-current', ['width' => '44'])
                                        @endif
                                    </x-circle-button>

                                    <x-circle-button color="red" wire:click="favoriteAnime">
                                        @if($isFavorited)
                                            @svg('heart_fill', 'fill-current', ['width' => '44'])
                                        @else
                                            @svg('heart', 'fill-current', ['width' => '44'])
                                        @endif
                                    </x-circle-button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 2xl:col-span-2 xl:max-h-[calc(100vh-64px)] overflow-x-hidden overflow-y-scroll no-scrollbar">
            <section id="badges" class="flex flex-row flex-nowrap whitespace-nowrap justify-between items-center text-center pb-5 pl-4 pr-4 overflow-x-scroll no-scrollbar">
                <div id="ratingBadge" class="flex-grow pr-12">
                    <a href="#ratingsAndReviews">
                        <p class="font-bold text-orange-500">
                            {{ number_format($anime->mediaStat->rating_average, 1) }}
                        </p>

                        <livewire:anime.star-rating :rating="$anime->mediaStat->rating_average" :star-size="'sm'" :disabled="true" />

                        <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x reviews', (int) $anime->mediaStat->rating_count, ['x' => number_shorten((int) $anime->mediaStat->rating_count, 0, true)]) }}</p>
                    </a>
                </div>

                @if ($anime->air_season)
                    <div id="seasonBadge" class="flex-grow px-12 border-l-2">
                        <a class="flex flex-col items-center" href="#aired">
                            <p class="font-bold">{{ $anime->air_season->description }}</p>
                            <p class="text-orange-500">
                                {{ $anime->air_season->symbol() }}
                            </p>
                            <p class="text-sm text-gray-500">{{ __('Season') }}</p>
                        </a>
                    </div>
                @endif

                <div id="rankingBadge" class="flex-grow px-12 border-l-2">
                    <a class="flex flex-col items-center" href="#genres">
                        <p class="font-bold">-</p>
                        <p class="text-orange-500">
                            @svg('chart_bar_fill', 'fill-current', ['width' => '20'])
                        </p>
                        <p class="text-sm text-gray-500">{{ __('Chart') }}</p>
                    </a>
                </div>

                <div id="tvRatingBadge" class="flex-grow px-12 border-l-2">
                    <a class="flex flex-col items-center" href="#tvRating">
                        <p class="font-bold">{{ $anime->tv_rating->name }}</p>
                        <p class="text-orange-500">
                            @svg('tv_fill', 'fill-current', ['width' => '20'])
                        </p>
                        <p class="text-sm text-gray-500">{{ __('Rated') }}</p>
                    </a>
                </div>

                @if (!empty($this->studio))
                    <div id="studioBadge" class="flex-grow px-12 border-l-2">
                        <a class="flex flex-col items-center" href="{{ route('studios.details', $this->studio) }}">
                            <p class="font-bold">{{ $this->studio->name }}</p>
                            <p class="text-orange-500">
                                @svg('building_2_fill', 'fill-current', ['width' => '20'])
                            </p>
                            <p class="text-sm text-gray-500">{{ __('Studio') }}</p>
                        </a>
                    </div>
                @endif

                <div id="languageBadge" class="flex-grow px-12 border-l-2">
                    <a class="flex flex-col items-center" href="#languages">
                        <p class="font-bold">{{ strtoupper($anime->languages->first()->code) }}</p>
                        <p class="text-orange-500">
                            @svg('character_bubble_fill', 'fill-current', ['width' => '20'])
                        </p>
                        <p class="text-sm text-gray-500">{{ trans_choice('{0} Language|{1} +:x More Language|[2,*] +:x More Languages', $anime->languages->count() - 1, ['x' => $anime->languages->count() - 1]) }}</p>
                    </a>
                </div>
            </section>

            @if (!empty($anime->synopsis))
                <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                    <x-section-nav class="flex flex-nowrap justify-between mb-5">
                        <x-slot:title>
                            {{ __('Synopsis') }}
                        </x-slot:title>
                    </x-section-nav>

                    <x-truncated-text>
                        <x-slot:text>
                            {!! nl2br(e($anime->synopsis)) !!}
                        </x-slot:text>
                    </x-truncated-text>
                </section>
            @endif

            <section id="ratingsAndReviews" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                <x-section-nav>
                    <x-slot:title>
                        {{ __('Ratings & Reviews') }}
                    </x-slot:title>
                </x-section-nav>

                <div class="flex flex-row flex-wrap justify-between gap-4">
                    <div class="flex flex-col justify-end text-center">
                        <p class="font-bold text-6xl">{{ number_format($anime->mediaStat->rating_average, 1) }}</p>
                        <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                    </div>

                    <div class="flex flex-col justify-end items-center text-center">
                        @svg('star_fill', 'fill-current', ['width' => 32])
                        <p class="font-bold text-2xl">{{ number_format($anime->mediaStat->highestRatingPercentage) }}%</p>
                        <p class="text-sm text-gray-500">{{ $anime->mediaStat->sentiment }}</p>
                    </div>

                    <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                        <x-star-rating-bar :media-stat="$anime->mediaStat" />

                        <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $anime->mediaStat->rating_count, ['x' => number_format($anime->mediaStat->rating_count)]) }}</p>
                    </div>
                </div>
            </section>

            @auth
                <section id="writeAReview" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                    <div class="flex flex-row flex-wrap justify-between gap-4">
                        <div class="flex justify-between items-center">
                            <p class="">{{ __('Click to Rate:') }}</p>

                            <livewire:anime.star-rating :anime="$anime" :rating="$anime->ratings()->firstWhere('user_id', auth()->user()->id)?->rating" :star-size="'md'" />
                        </div>

                        <div class="flex justify-between">
                            <a class="flex gap-1" href="#">
                                @svg('pencil', 'fill-current', ['width' => 18])
                                {{ __('Write a Review') }}
                            </a>
                        </div>

                        <div></div>
                    </div>
                </section>
            @endauth

            <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                <x-section-nav>
                    <x-slot:title>
                        {{ __('Information') }}
                    </x-slot:title>
                </x-section-nav>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                    <x-information-list id="type" title="{{ __('Type') }}" icon="{{ asset('images/symbols/tv_and_mediabox.svg') }}">
                        <x-slot:information>
                            {{ $anime->media_type->name }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ $anime->media_type->description }}</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="source" title="{{ __('Source') }}" icon="{{ asset('images/symbols/target.svg') }}">
                        <x-slot:information>
                            {{ $anime->source->name }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ $anime->source->description }}</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="genres" title="{{ __('Genres') }}" icon="{{ asset('images/symbols/theatermasks.svg') }}">
                        <x-slot:information>
                            {{ $anime->genres?->pluck('name')->join(',  ', ' and ') ?: '-' }}
                        </x-slot:information>
                    </x-information-list>

                    <x-information-list id="themes" title="{{ __('Themes') }}" icon="{{ asset('images/symbols/crown.svg') }}">
                        <x-slot:information>
                            {{ $anime->themes?->pluck('name')->join(',  ', ' and ') ?: '-' }}
                        </x-slot:information>
                    </x-information-list>

                    @if (in_array($anime->media_type->name, ['Unknown', 'TV', 'ONA']))
                        <x-information-list id="episodes" title="{{ __('Episodes') }}" icon="{{ asset('images/symbols/film.svg') }}">
                            <x-slot:information>
                                {{ $anime->episode_count }}
                            </x-slot:information>

                            <x-slot:footer>
                                <p class="text-sm">{{ trans_choice('[0,1] Across one season.|[2,*] Across :count seasons.', $anime->season_count, ['count' => $anime->season_count]) }}</p>
                            </x-slot:footer>
                        </x-information-list>
                    @endif

                    <x-information-list id="duration" title="{{ __('Duration') }}" icon="{{ asset('images/symbols/hourglass.svg') }}">
                        <x-slot:information>
                            {{ $anime->duration_string ?? '-' }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ __('With a total of :count.', ['count' => $anime->duration_total]) }}</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="broadcast" title="{{ __('Broadcast') }}" icon="{{ asset('images/symbols/calendar_badge_clock.svg') }}">
                        <x-slot:information>
                            {{ $anime->broadcast }}
                        </x-slot:information>

                        @if ($anime->status_id === 4)
                            <x-slot:footer>
                                {{ __('The broadcasting of this series has ended.') }}
                            </x-slot:footer>
                        @elseif (empty($anime->broadcast))
                            {{ __('No broadcast data available at the moment.') }}
                        @elseif ($anime->status_id === 3)
                            <div class="flex flex-col align-center mt-1">
                                <p class="font-black text-2xl">
                                    {{ $anime->time_until_broadcast }}
                                </p>
                            </div>
                        @endif
                    </x-information-list>

                    <x-information-list id="aired" title="{{ __('Aired') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                        @if (!empty($anime->started_at))
                            @if (empty($anime->ended_at))
                                <x-slot:information>
                                    🚀 {{ $anime->started_at->toFormattedDateString() }}
                                </x-slot:information>

                                <x-slot:footer>
                                    {{ __($anime->status->description) }}
                                </x-slot:footer>
                            @else
                                <div class="flex flex-col">
                                    <p class="font-semibold text-2xl">🚀 {{ $anime->started_at->toFormattedDateString() }}</p>

                                    @svg('dotted_line', 'fill-current', ['width' => '100%'])

                                    <p class="font-semibold text-2xl text-right">{{ $anime->ended_at?->toFormattedDateString() }} 🏁</p>
                                </div>
                            @endif
                        @else
                            <x-slot:information>
                                -
                            </x-slot:information>
                            <x-slot:footer>
                                {{ __('Airing dates are unknown.') }}
                            </x-slot:footer>
                        @endif
                    </x-information-list>

                    <x-information-list id="tvRating" title="{{ __('Rating') }}" icon="{{ asset('images/symbols/tv_rating.svg') }}">
                        <x-slot:information>
                            {{ $anime->tv_rating->name }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ $anime->tv_rating->description }}.</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="languages" title="{{ __('Languages') }}" icon="{{ asset('images/symbols/globe.svg') }}">
                        <x-slot:information>
                            {{ $anime->languages->pluck('name')->join(',  ', ' and ') ?: '-' }}
                        </x-slot:information>
                    </x-information-list>

{{--                    <x-information-list title="{{ __('Studio') }}" icon="{{ asset('images/symbols/building_2.svg') }}">--}}
{{--                        <x:information>--}}
{{--                            {{ $anime->studios()->first()->name ?? '-' }}--}}
{{--                        </x:information>--}}
{{--                    </x-information-list>--}}

{{--                    <x-information-list title="{{ __('Network') }}" icon="{{ asset('images/symbols/dot_radiowaves_left_and_right.svg') }}">--}}
{{--                        <x:information>--}}
{{--                            {{ $anime->studios()->first()->name ?? '-' }}--}}
{{--                        </x:information>--}}
{{--                    </x-information-list>--}}
                </div>
            </section>

            <livewire:components.anime-seasons-section :anime="$anime" />

            <livewire:components.anime-cast-section :anime="$anime" />

            <livewire:components.anime-staff-section :anime="$anime" />

            <livewire:components.anime-songs-section :anime="$anime" />

            <livewire:components.anime-studios-section :anime="$anime" />

            <div class="bg-orange-50">
                @if(!empty($this->studio))
                    <livewire:components.anime-more-by-studio-section :studio="$this->studio" />
                @endif

                <livewire:components.anime.anime-relations-section :anime="$anime" />

                <livewire:components.anime.manga-relations-section :anime="$anime" />

                <livewire:components.anime.game-relations-section :anime="$anime" />

                @if (!empty($anime->copyright))
                    <section class="pt-4 pr-4 pb-4 pl-4 border-t">
                        <p class="text-sm text-gray-400">{{ $anime->copyright }}</p>
                    </section>
                @endif
            </div>

            <x-dialog-modal maxWidth="md" model="showPopup">
                @if($showVideo)
                    <x-slot:title>
                        {{ $anime->title . ' Official Trailer' }}
                    </x-slot:title>
                    <x-slot:content>
                        <iframe
                            class="w-full aspect-video lazyload"
                            type="text/html"
                            allowfullscreen="allowfullscreen"
                            mozallowfullscreen="mozallowfullscreen"
                            msallowfullscreen="msallowfullscreen"
                            oallowfullscreen="oallowfullscreen"
                            webkitallowfullscreen="webkitallowfullscreen"
                            allow="fullscreen;"
                            data-size="auto"
                            data-src="https://www.youtube-nocookie.com/embed/{{ str($anime->video_url)->after('?v=') }}?autoplay=0&iv_load_policy=3&disablekb=1&color=red&rel=0&cc_load_policy=0&start=0&end=0&origin={{ config('app.url') }}&modestbranding=1&playsinline=1&loop=1&playlist={{ str($anime->video_url)->after('?v=') }}"
                        >
                        </iframe>
                    </x-slot:content>
                    <x-slot:footer>
                        <x-button wire:click="$toggle('showPopup')">{{ __('Close') }}</x-button>
                    </x-slot:footer>
                @else
                    <x-slot:title>
                        {{ $popupData['title'] }}
                    </x-slot:title>

                    <x-slot:content>
                        <p>{{ $popupData['message'] }}</p>
                    </x-slot:content>

                    <x-slot:footer>
                        <x-button wire:click="$toggle('showPopup')">{{ __('Ok') }}</x-button>
                    </x-slot:footer>
                @endif
            </x-dialog-modal>
        </div>
    </div>
</main>
