<main>
    <x-slot:themeColor>
        {{ ($game->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $game->getFirstMedia(\App\Enums\MediaCollection::Poster))?->custom_properties['background_color'] ?? null }}
    </x-slot:themeColor>

    <x-slot:title>
        {!! $game->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ $game->synopsis }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $game->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $game->synopsis ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:video" content="{{ $game->video_url ?? '' }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $game->duration }}" />
        <meta property="video:release_date" content="{{ $game->published_at?->toIso8601String() }}" />
        @foreach($game->tags() as $tag)
            <meta property="video:tag" content="{{ $tag->name }}" />
        @endforeach
        <meta property="twitter:title" content="{{ $game->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $game->synopsis }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $game->synopsis }}" />
        <link rel="canonical" href="{{ route('games.details', $game) }}">
        <x-misc.schema>
            "@type":"VideoGame",
            "url":"/games/{{ $game->slug }}/",
            "name": "{{ $game->title }}",
            "alternateName": "{{ $game->original_title }}",
            "image": "{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
            "description": "{{ str_replace('"', "'", $game->synopsis) }}",
            "aggregateRating": {
                "@type":"AggregateRating",
                "itemReviewed": {
                    "@type": "VideoGame",
                    "image": [
                        "{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}"
                    ],
                    "name": "{{ $game->title }}"
                },
                "ratingCount": {{ $game->mediaStat->rating_count ?? 1 }},
                "bestRating": 5,
                "worstRating": 0,
                "ratingValue": {{ $game->mediaStat->rating_average ?? 2.5 }}
            },
            "contentRating": "{{ $game->tv_rating->name }}",
            "genre": {!! $game->genres()->pluck('name') !!},
            "datePublished": "{{ $game->published_at?->format('Y-m-d') }}",
            "keywords": "game{{ (',' . $game->keywords) ?? '' }}"
            @if (!empty($this->studio))
                ,"creator":[
                    {
                        "@type":"Organization",
                        "url":"/studio/{{ $this->studio->id }}/"
                    }
                ]
            @endif
            @if (!empty($game->video_url))
                ,"trailer": {
                    "@type":"VideoObject",
                    "name":"{{ $game->title }}",
                    "embedUrl": "{{ $game->video_url }}",
                    "description":"Official Trailer",
                    "thumbnailUrl": "{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
                    "uploadDate": "{{ $game->published_at?->format('Y-m-d') }}"
                }
            @endif
        </x-misc.schema>
    </x-slot:meta>

    <x-slot:appArgument>
        games/{{ $game->id }}
    </x-slot:appArgument>

    <div class="grid grid-rows-[repeat(2,minmax(0,min-content))] h-full xl:grid-rows-none xl:grid-cols-2 2xl:grid-cols-3 xl:mb-0" wire:init="loadPage">
        <div class="relative">
            <div class="relative flex flex-nowrap aspect-video md:relative md:h-full xl:aspect-auto">
                <x-picture
                    class="w-full overflow-hidden"
                    style="background-color: {{ ($game->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $game->getFirstMedia(\App\Enums\MediaCollection::Poster))?->custom_properties['background_color'] ?? '#FFFFFF' }};"
                    wire:ignore
                >
                    <img class="w-full h-full aspect-video object-cover lazyload" data-sizes="auto" data-src="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/game_banner.webp') }}" alt="{{ $game->title }} Banner" title="{{ $game->title }}">
                </x-picture>

                @if (!empty($game->video_url))
                    <div class="absolute top-0 bottom-0 left-0 right-0">
                        <div class="flex flex-col justify-center items-center h-full md:pb-40 lg:pb-0">
                            <button
                                class="inline-flex items-center pt-5 pr-5 pb-5 pl-5 bg-white/60 backdrop-blur border border-transparent rounded-full font-semibold text-xs text-gray-500 uppercase tracking-widest shadow-md hover:opacity-75 active:opacity-50 focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                                wire:click="showTrailerVideo"
                            >
                                @svg('play_fill', 'fill-current', ['width' => '34'])
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="md:absolute md:bottom-0 md:left-0 md:right-0 lg:px-4">
                <div class="flex flex-nowrap pt-5 pb-8 pl-4 pr-4 md:mx-auto md:mb-8 md:p-2 md:max-w-lg md:bg-white md:bg-opacity-50 md:backdrop-filter md:backdrop-blur md:rounded-lg">
                    <x-picture
                        :border="true"
                        border-roundness="rounded-3xl"
                        class="w-28 h-28 mr-2 rounded-3xl overflow-hidden"
                        style="min-width: 7rem; max-height: 10rem; background-color: {{ $game->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#FFFFFF' }};"
                        wire:ignore
                    >
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/game_poster.webp') }}" alt="{{ $game->title }} Poster" title="{{ $game->title }}">
                    </x-picture>

                    <div class="flex flex-col gap-2 justify-between w-full">
                        <div>
                            <p class="font-semibold text-lg leading-tight break-all">{{ $game->title }}</p>

                            <p class="text-sm leading-tight">{{ $game->information_summary }}</p>

                            <div class="flex w-full justify-between mt-2 gap-1 sm:gap-4">
                                <p class="flex-grow pt-1 pr-1 pb-1 pl-1 text-white text-center text-xs font-semibold whitespace-nowrap rounded-md" style="background-color: {{ $game->status->color }};">{{ $game->status->name }}</p>

                                <p class="flex-grow pt-1 pr-1 pb-1 pl-1 bg-gray-100 text-center text-xs font-semibold whitespace-nowrap rounded-md"> {{ trans_choice('{0} Rank -|[1,*] Rank #:x', $game->mediaStat->rank_total ?? 0, ['x' => $game->mediaStat->rank_total]) }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1 justify-between">
                            <div class="flex gap-2">
                                <livewire:components.library-button :model="$game" wire:key="{{ uniqid($game->id, true) }}" />

                                <x-nova-link :resource="\App\Nova\Game::class" :model="$game">
                                    @svg('pencil', 'fill-current', ['width' => '44'])
                                </x-nova-link>
                            </div>

                            <div class="flex gap-2">
                                @if ($isTracking)
{{--                                    <x-circle-button color="yellow" wire:click="remindGame">--}}
{{--                                        @if ($isReminded)--}}
{{--                                            @svg('bell_fill', 'fill-current', ['width' => '44'])--}}
{{--                                        @else--}}
{{--                                            @svg('bell', 'fill-current', ['width' => '44'])--}}
{{--                                        @endif--}}
{{--                                    </x-circle-button>--}}

                                    <x-circle-button color="red" wire:click="favoriteGame">
                                        @if ($isFavorited)
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

        <div class="pt-4 2xl:col-span-2 xl:max-h-[calc(100vh-48px)] overflow-x-hidden overflow-y-scroll no-scrollbar">
            <section id="badges" class="flex flex-row flex-nowrap whitespace-nowrap justify-between items-center text-center pb-5 pl-4 pr-4 overflow-x-scroll no-scrollbar">
                <div id="ratingBadge" class="flex-grow pr-12">
                    <a href="#ratingsAndReviews">
                        <p class="font-bold text-orange-500">
                            {{ number_format($game->mediaStat->rating_average, 1) }}
                        </p>

                        <livewire:components.star-rating  :rating="$game->mediaStat->rating_average" :star-size="'sm'" :disabled="true" />

                        <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x reviews', (int) $game->mediaStat->rating_count, ['x' => number_shorten((int) $game->mediaStat->rating_count, 0, true)]) }}</p>
                    </a>
                </div>

                @if ($game->publish_season)
                    <div id="seasonBadge" class="flex-grow px-12 border-l-2">
                        <a class="flex flex-col items-center" href="#published">
                            <p class="font-bold">{{ $game->publish_season->description }}</p>
                            <p class="text-orange-500">
                                {{ $game->publish_season->symbol() }}
                            </p>
                            <p class="text-sm text-gray-500">{{ __('Season') }}</p>
                        </a>
                    </div>
                @endif

                <div id="rankingBadge" class="flex-grow px-12 border-l-2">
                    <a class="flex flex-col items-center" href="{{ route('charts.top', App\Enums\ChartKind::Games) }}">
                        <p class="font-bold">{{ trans_choice('{0} -|[1,*] #:x', $game->mediaStat->rank_total ?? 0, ['x' => $game->mediaStat->rank_total]) }}</p>
                        <p class="text-orange-500">
                            @svg('chart_bar_fill', 'fill-current', ['width' => '20'])
                        </p>
                        <p class="text-sm text-gray-500">{{ __('Chart') }}</p>
                    </a>
                </div>

                <div id="tvRatingBadge" class="flex-grow px-12 border-l-2">
                    <a class="flex flex-col items-center" href="#tvRating">
                        <p class="font-bold">{{ $game->tv_rating->name }}</p>
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
                        <p class="font-bold">{{ strtoupper($game->languages->first()->code) }}</p>
                        <p class="text-orange-500">
                            @svg('character_bubble_fill', 'fill-current', ['width' => '20'])
                        </p>
                        <p class="text-sm text-gray-500">{{ trans_choice('{0} Language|{1} +:x More Language|[2,*] +:x More Languages', $game->languages->count() - 1, ['x' => $game->languages->count() - 1]) }}</p>
                    </a>
                </div>
            </section>

            @if (!empty($game->synopsis))
                <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                    <x-section-nav class="flex flex-nowrap justify-between mb-5">
                        <x-slot:title>
                            {{ __('Synopsis') }}
                        </x-slot:title>
                    </x-section-nav>

                    <x-truncated-text>
                        <x-slot:text>
                            {!! nl2br(e($game->synopsis)) !!}
                        </x-slot:text>
                    </x-truncated-text>
                </section>
            @endif

            <section id="ratingsAndReviews" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                <x-section-nav>
                    <x-slot:title>
                        {{ __('Ratings & Reviews') }}
                    </x-slot:title>

                    <x-slot:action>
                        <x-section-nav-link class="whitespace-nowrap" href="{{ route('games.reviews', $game) }}">{{ __('See All') }}</x-section-nav-link>
                    </x-slot:action>
                </x-section-nav>

                <div class="flex flex-row flex-wrap justify-between gap-4">
                    <div class="flex flex-col justify-end text-center">
                        <p class="font-bold text-6xl">{{ number_format($game->mediaStat->rating_average, 1) }}</p>
                        <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                    </div>

                    <div class="flex flex-col justify-end items-center text-center">
                        @svg('star_fill', 'fill-current', ['width' => 32])
                        <p class="font-bold text-2xl">{{ number_format($game->mediaStat->highestRatingPercentage) }}%</p>
                        <p class="text-sm text-gray-500">{{ $game->mediaStat->sentiment }}</p>
                    </div>

                    <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                        <x-star-rating-bar :media-stat="$game->mediaStat" />

                        <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $game->mediaStat->rating_count, ['x' => number_format($game->mediaStat->rating_count)]) }}</p>
                    </div>
                </div>
            </section>

            <section id="writeAReview" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                <div class="flex flex-row flex-wrap gap-4">
                    <div class="flex justify-between items-center">
                        <p class="">{{ __('Click to Rate:') }}</p>

                        <livewire:components.star-rating :model-id="$game->id" :model-type="$game->getMorphClass()" :rating="$userRating->first()?->rating" :star-size="'md'" />
                    </div>

                    <div class="flex justify-between">
                        <x-simple-button class="flex gap-1" wire:click="$dispatch('show-review-box', { 'id': '{{  $this->reviewBoxID }}' })">
                            @svg('pencil', 'fill-current', ['width' => 18])
                            {{ __('Write a Review') }}
                        </x-simple-button>
                    </div>

                    <div></div>
                </div>

                <div class="mt-5">
                    <livewire:sections.reviews :model="$game" />
                </div>
            </section>

            <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                <x-section-nav>
                    <x-slot:title>
                        {{ __('Information') }}
                    </x-slot:title>
                </x-section-nav>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                    <x-information-list id="type" title="{{ __('Type') }}" icon="{{ asset('images/symbols/tv_and_mediabox.svg') }}">
                        <x-slot:information>
                            {{ $game->media_type->name }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ $game->media_type->description }}</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="source" title="{{ __('Source') }}" icon="{{ asset('images/symbols/target.svg') }}">
                        <x-slot:information>
                            {{ $game->source->name }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ $game->source->description }}</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="genres" title="{{ __('Genres') }}" icon="{{ asset('images/symbols/theatermasks.svg') }}">
                        <x-slot:information>
                            {{ $game->genres?->pluck('name')->join(', ', ' and ') ?: '-' }}
                        </x-slot:information>
                    </x-information-list>

                    <x-information-list id="themes" title="{{ __('Themes') }}" icon="{{ asset('images/symbols/crown.svg') }}">
                        <x-slot:information>
                            {{ $game->themes?->pluck('name')->join(', ', ' and ') ?: '-' }}
                        </x-slot:information>
                    </x-information-list>

                    @if (in_array($game->media_type->name, ['Unknown', 'TV', 'ONA']))
                        <x-information-list id="episodes" title="{{ __('Episodes') }}" icon="{{ asset('images/symbols/film.svg') }}">
                            <x-slot:information>
                                {{ $game->episode_count }}
                            </x-slot:information>

                            <x-slot:footer>
                                <p class="text-sm">{{ trans_choice('[0,1] Across one season.|[2,*] Across :count seasons.', $game->season_count, ['count' => $game->season_count]) }}</p>
                            </x-slot:footer>
                        </x-information-list>
                    @endif

                    <x-information-list id="duration" title="{{ __('Duration') }}" icon="{{ asset('images/symbols/hourglass.svg') }}">
                        <x-slot:information>
                            {{ $game->duration_string ?? '-' }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ __('With a total of :count.', ['count' => $game->duration_total]) }}</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="publication" title="{{ __('Publication') }}" icon="{{ asset('images/symbols/calendar_badge_clock.svg') }}">
                        <x-slot:information>
                            {{ $game->publication_string }}
                        </x-slot:information>

                        @if ($game->status_id === 15)
                            <x-slot:footer>
                                {{ __('The publication of this series has ended.') }}
                            </x-slot:footer>
                        @elseif (empty($game->publication_date))
                            {{ __('No publication data available at the moment.') }}
                        @elseif ($game->status_id === 14)
                            <div class="flex flex-col align-center mt-1">
                                <p class="font-black text-2xl">
                                    {{ $game->time_until_publication }}
                                </p>
                            </div>
                        @endif
                    </x-information-list>

                    <x-information-list id="published" title="{{ __('Published') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                        @if (!empty($game->published_at))
                            <x-slot:information>
                                🚀 {{ $game->published_at->toFormattedDateString() }}
                            </x-slot:information>

                            <x-slot:footer>
                                {{ __($game->status->description) }}
                            </x-slot:footer>
                        @else
                            <x-slot:information>
                                -
                            </x-slot:information>
                            <x-slot:footer>
                                {{ __('Publishing dates are unknown.') }}
                            </x-slot:footer>
                        @endif
                    </x-information-list>

                    <x-information-list id="tvRating" title="{{ __('Rating') }}" icon="{{ asset('images/symbols/tv_rating.svg') }}">
                        <x-slot:information>
                            {{ $game->tv_rating->name }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ $game->tv_rating->description }}.</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="languages" title="{{ __('Languages') }}" icon="{{ asset('images/symbols/globe.svg') }}">
                        <x-slot:information>
                            {{ $game->languages->pluck('name')->join(', ', ' and ') ?: '-' }}
                        </x-slot:information>
                    </x-information-list>
                </div>
            </section>

            @if ($readyToLoad)
                <livewire:components.game-cast-section :game="$game" />

                <livewire:components.game-staff-section :game="$game" />

                <livewire:components.game-songs-section :game="$game" />

                <livewire:components.game-studios-section :game="$game" />

                <div class="bg-orange-50">
                    @if (!empty($this->studio))
                        <livewire:components.game-more-by-studio-section :game="$game" :studio="$this->studio" />
                    @endif

                    <livewire:components.game.game-relations-section :game="$game" />

                    <livewire:components.game.anime-relations-section :game="$game" />

                    <livewire:components.game.manga-relations-section :game="$game" />

                    @if (!empty($game->copyright))
                        <section class="pt-4 pr-4 pb-4 pl-4 border-t">
                            <p class="text-sm text-gray-400">{!! nl2br(e($game->copyright)) !!}</p>
                        </section>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$game->id" :model-type="$game->getMorphClass()" :user-rating="$userRating?->first()" />

    <x-dialog-modal maxWidth="md" model="showPopup">
        @if ($showVideo)
            <x-slot:title>
                {{ $game->title . ' Official Trailer' }}
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
                    data-src="https://www.youtube-nocookie.com/embed/{{ str($game->video_url)->after('?v=') }}?autoplay=0&iv_load_policy=3&disablekb=1&color=red&rel=0&cc_load_policy=0&start=0&end=0&origin={{ config('app.url') }}&modestbranding=1&playsinline=1&loop=1&playlist={{ str($game->video_url)->after('?v=') }}"
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
</main>
