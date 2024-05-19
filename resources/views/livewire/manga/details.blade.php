<main>
    <x-slot:themeColor>
        {{ ($manga->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $manga->getFirstMedia(\App\Enums\MediaCollection::Poster))?->custom_properties['background_color'] ?? null }}
    </x-slot:themeColor>

    <x-slot:title>
        {!! $manga->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ $manga->synopsis }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $manga->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $manga->synopsis ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="book" />
        <meta property="book:release_date" content="{{ $manga->started_at?->toIso8601String() }}" />
        @foreach($manga->tags() as $tag)
            <meta property="book:tag" content="{{ $tag->name }}" />
        @endforeach
        <meta property="twitter:title" content="{{ $manga->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $manga->synopsis }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $manga->synopsis }}" />
        <link rel="canonical" href="{{ route('manga.details', $manga) }}">
        <x-misc.schema>
            "@type":"Book",
            "url":"/manga/{{ $manga->slug }}/",
            "name": "{{ $manga->title }}",
            "alternateName": "{{ $manga->original_title }}",
            "image": "{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
            "description": "{{ str_replace('"', "'", $manga->synopsis) }}",
            "aggregateRating": {
                "@type":"AggregateRating",
                "itemReviewed": {
                    "@type": "TVSeries",
                    "image": [
                        "{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}"
                    ],
                    "name": "{{ $manga->title }}"
                },
                "ratingCount": {{ $manga->mediaStat->rating_count ?? 1 }},
                "bestRating": 5,
                "worstRating": 0,
                "ratingValue": {{ $manga->mediaStat->rating_average ?? 2.5 }}
            },
            "contentRating": "{{ $manga->tv_rating->name }}",
            "genre": {!! $manga->genres()->pluck('name') !!},
            "datePublished": "{{ $manga->started_at?->format('Y-m-d') }}",
            "keywords": "manga{{ (',' . $manga->keywords) ?? '' }}"
            @if (!empty($this->studio))
                ,"creator":[
                    {
                        "@type":"Organization",
                        "url":"/studio/{{ $this->studio->id }}/"
                    }
                ]
            @endif
        </x-misc.schema>
    </x-slot:meta>

    <x-slot:appArgument>
        manga/{{ $manga->id }}
    </x-slot:appArgument>

    <div class="grid grid-rows-[repeat(2,minmax(0,min-content))] h-full xl:grid-rows-none xl:grid-cols-2 2xl:grid-cols-3 xl:mb-0" wire:init="loadPage">
        <div class="relative">
            <div class="relative flex flex-nowrap aspect-video md:relative md:h-full xl:aspect-auto">
                <x-picture
                    class="w-full overflow-hidden"
                    style="background-color: {{ ($manga->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $manga->getFirstMedia(\App\Enums\MediaCollection::Poster))?->custom_properties['background_color'] ?? '#FFFFFF' }};"
                    wire:ignore
                >
                    <img class="w-full h-full aspect-video object-cover lazyload" data-sizes="auto" data-src="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $manga->title }} Banner" title="{{ $manga->title }}">
                </x-picture>

                @if (!empty($manga->video_url))
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
                <div class="flex flex-nowrap pt-5 pb-8 pl-4 pr-4 md:mx-auto md:mb-8 md:p-2 md:max-w-lg md:bg-white md:bg-opacity-50 md:backdrop-filter md:backdrop-blur md:rounded-lg">
                    <svg
                        class="relative shrink-0 w-28 h-40 mr-2 overflow-hidden"
                        style="min-width: 7rem; max-height: 10rem; background-color: {{ $manga->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#FFFFFF' }};"
                        wire:ignore
                    >
                        <foreignObject width="112" height="160" mask="url(#svg-mask-book-cover)">
                            <img class="h-full w-full object-cover lazyload" data-sizes="auto" data-src="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $manga->title }} Poster" title="{{ $manga->title }}" />
                        </foreignObject>

                        <g opacity="0.40">
                            <use fill-opacity="0.03" fill="url(#svg-pattern-book-cover-1)" fill-rule="evenodd" xlink:href="#svg-rect-book-cover" />
                            <use fill-opacity="1" fill="url(#svg-linearGradient-book-cover-1)" fill-rule="evenodd" style="mix-blend-mode: lighten;" xlink:href="#svg-rect-book-cover" />
                            <use fill-opacity="1" fill="black" filter="url(#svg-filter-book-cover-1)" xlink:href="#svg-rect-book-cover" />
                        </g>
                    </svg>

                    <div class="flex flex-col gap-2 justify-between w-full">
                        <div>
                            <p class="font-semibold text-lg leading-tight break-all">{{ $manga->title }}</p>

                            <p class="text-sm leading-tight">{{ $manga->information_summary }}</p>

                            <div class="flex w-full justify-between mt-2 gap-1 sm:gap-4">
                                <p class="flex-grow pt-1 pr-1 pb-1 pl-1 text-white text-center text-xs font-semibold whitespace-nowrap rounded-md" style="background-color: {{ $manga->status->color }};">{{ $manga->status->name }}</p>

                                <p class="flex-grow pt-1 pr-1 pb-1 pl-1 bg-gray-100 text-center text-xs font-semibold whitespace-nowrap rounded-md"> {{ trans_choice('{0} Rank -|[1,*] Rank #:x', $manga->mediaStat->rank_total ?? 0, ['x' => $manga->mediaStat->rank_total]) }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1 justify-between">
                            <div class="flex gap-2">
                                <livewire:components.library-button :model="$manga" wire:key="{{ uniqid($manga->id, true) }}" />

                                <x-nova-link :resource="\App\Nova\Manga::class" :model="$manga">
                                    @svg('pencil', 'fill-current', ['width' => '44'])
                                </x-nova-link>
                            </div>

                            <div class="flex gap-2">
                                @if ($isTracking)
{{--                                    <x-circle-button color="yellow" wire:click="remindManga">--}}
{{--                                        @if ($isReminded)--}}
{{--                                            @svg('bell_fill', 'fill-current', ['width' => '44'])--}}
{{--                                        @else--}}
{{--                                            @svg('bell', 'fill-current', ['width' => '44'])--}}
{{--                                        @endif--}}
{{--                                    </x-circle-button>--}}

                                    <x-circle-button color="red" wire:click="favoriteManga">
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
                            {{ number_format($manga->mediaStat->rating_average, 1) }}
                        </p>

                        <livewire:components.star-rating :rating="$manga->mediaStat->rating_average" :star-size="'sm'" :disabled="true" />

                        <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x reviews', (int) $manga->mediaStat->rating_count, ['x' => number_shorten((int) $manga->mediaStat->rating_count, 0, true)]) }}</p>
                    </a>
                </div>

                @if ($manga->publication_season)
                    <div id="seasonBadge" class="flex-grow px-12 border-l-2">
                        <a class="flex flex-col items-center" href="#published">
                            <p class="font-bold">{{ $manga->publication_season->description }}</p>
                            <p class="text-orange-500">
                                {{ $manga->publication_season->symbol() }}
                            </p>
                            <p class="text-sm text-gray-500">{{ __('Season') }}</p>
                        </a>
                    </div>
                @endif

                <div id="rankingBadge" class="flex-grow px-12 border-l-2">
                    <a class="flex flex-col items-center" href="{{ route('charts.top', App\Enums\ChartKind::Manga) }}">
                        <p class="font-bold">{{ trans_choice('{0} -|[1,*] #:x', $manga->mediaStat->rank_total ?? 0, ['x' => $manga->mediaStat->rank_total]) }}</p>
                        <p class="text-orange-500">
                            @svg('chart_bar_fill', 'fill-current', ['width' => '20'])
                        </p>
                        <p class="text-sm text-gray-500">{{ __('Chart') }}</p>
                    </a>
                </div>

                <div id="tvRatingBadge" class="flex-grow px-12 border-l-2">
                    <a class="flex flex-col items-center" href="#tvRating">
                        <p class="font-bold">{{ $manga->tv_rating->name }}</p>
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
                        <p class="font-bold">{{ strtoupper($manga->languages->first()->code) }}</p>
                        <p class="text-orange-500">
                            @svg('character_bubble_fill', 'fill-current', ['width' => '20'])
                        </p>
                        <p class="text-sm text-gray-500">{{ trans_choice('{0} Language|{1} +:x More Language|[2,*] +:x More Languages', $manga->languages->count() - 1, ['x' => $manga->languages->count() - 1]) }}</p>
                    </a>
                </div>
            </section>

            @if (!empty($manga->synopsis))
                <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                    <x-section-nav class="flex flex-nowrap justify-between mb-5">
                        <x-slot:title>
                            {{ __('Synopsis') }}
                        </x-slot:title>
                    </x-section-nav>

                    <x-truncated-text>
                        <x-slot:text>
                            {!! nl2br(e($manga->synopsis)) !!}
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
                        <x-section-nav-link class="whitespace-nowrap" href="{{ route('manga.reviews', $manga) }}">{{ __('See All') }}</x-section-nav-link>
                    </x-slot:action>
                </x-section-nav>

                <div class="flex flex-row flex-wrap justify-between gap-4">
                    <div class="flex flex-col justify-end text-center">
                        <p class="font-bold text-6xl">{{ number_format($manga->mediaStat->rating_average, 1) }}</p>
                        <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                    </div>

                    <div class="flex flex-col justify-end items-center text-center">
                        @svg('star_fill', 'fill-current', ['width' => 32])
                        <p class="font-bold text-2xl">{{ number_format($manga->mediaStat->highestRatingPercentage) }}%</p>
                        <p class="text-sm text-gray-500">{{ $manga->mediaStat->sentiment }}</p>
                    </div>

                    <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                        <x-star-rating-bar :media-stat="$manga->mediaStat" />

                        <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $manga->mediaStat->rating_count, ['x' => number_format($manga->mediaStat->rating_count)]) }}</p>
                    </div>
                </div>
            </section>

            <section id="writeAReview" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                <div class="flex flex-row flex-wrap gap-4">
                    <div class="flex justify-between items-center">
                        <p class="">{{ __('Click to Rate:') }}</p>

                        <livewire:components.star-rating :model-id="$manga->id" :model-type="$manga->getMorphClass()" :rating="$userRating->first()?->rating" :star-size="'md'" />
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
                    <livewire:sections.reviews :model="$manga" />
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
                            {{ $manga->media_type->name }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ $manga->media_type->description }}</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="source" title="{{ __('Source') }}" icon="{{ asset('images/symbols/target.svg') }}">
                        <x-slot:information>
                            {{ $manga->source->name }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ $manga->source->description }}</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="genres" title="{{ __('Genres') }}" icon="{{ asset('images/symbols/theatermasks.svg') }}">
                        <x-slot:information>
                            {{ $manga->genres?->pluck('name')->join(', ', ' and ') ?: '-' }}
                        </x-slot:information>
                    </x-information-list>

                    <x-information-list id="themes" title="{{ __('Themes') }}" icon="{{ asset('images/symbols/crown.svg') }}">
                        <x-slot:information>
                            {{ $manga->themes?->pluck('name')->join(', ', ' and ') ?: '-' }}
                        </x-slot:information>
                    </x-information-list>

                    @if (in_array($manga->media_type->name, ['Unknown', 'TV', 'ONA']))
                        <x-information-list id="episodes" title="{{ __('Episodes') }}" icon="{{ asset('images/symbols/film.svg') }}">
                            <x-slot:information>
                                {{ $manga->episode_count }}
                            </x-slot:information>

                            <x-slot:footer>
                                <p class="text-sm">{{ trans_choice('[0,1] Across one season.|[2,*] Across :count seasons.', $manga->season_count, ['count' => $manga->season_count]) }}</p>
                            </x-slot:footer>
                        </x-information-list>
                    @endif

                    <x-information-list id="duration" title="{{ __('Duration') }}" icon="{{ asset('images/symbols/hourglass.svg') }}">
                        <x-slot:information>
                            {{ $manga->duration_string ?? '-' }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ __('With a total of :count.', ['count' => $manga->duration_total]) }}</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="publication" title="{{ __('Publication') }}" icon="{{ asset('images/symbols/calendar_badge_clock.svg') }}">
                        <x-slot:information>
                            {{ $manga->publication_string }}
                        </x-slot:information>

                        @if ($manga->status_id === 4)
                            <x-slot:footer>
                                {{ __('The publishing of this series has ended.') }}
                            </x-slot:footer>
                        @elseif (empty($manga->publication_date))
                            {{ __('No publication data available at the moment.') }}
                        @elseif ($manga->status_id === 3)
                            <div class="flex flex-col align-center mt-1">
                                <p class="font-black text-2xl">
                                    {{ $manga->time_until_publication }}
                                </p>
                            </div>
                        @endif
                    </x-information-list>

                    <x-information-list id="published" title="{{ __('Published') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                        @if (!empty($manga->started_at))
                            @if (empty($manga->ended_at))
                                <x-slot:information>
                                    🚀 {{ $manga->started_at->toFormattedDateString() }}
                                </x-slot:information>

                                <x-slot:footer>
                                    {{ __($manga->status->description) }}
                                </x-slot:footer>
                            @else
                                <div class="flex flex-col">
                                    <p class="font-semibold text-2xl">🚀 {{ $manga->started_at->toFormattedDateString() }}</p>

                                    @svg('dotted_line', 'fill-current', ['width' => '100%'])

                                    <p class="font-semibold text-2xl text-right">{{ $manga->ended_at?->toFormattedDateString() }} 🏁</p>
                                </div>
                            @endif
                        @else
                            <x-slot:information>
                                -
                            </x-slot:information>
                            <x-slot:footer>
                                {{ __('Publication dates are unknown.') }}
                            </x-slot:footer>
                        @endif
                    </x-information-list>

                    <x-information-list id="tvRating" title="{{ __('Rating') }}" icon="{{ asset('images/symbols/tv_rating.svg') }}">
                        <x-slot:information>
                            {{ $manga->tv_rating->name }}
                        </x-slot:information>

                        <x-slot:footer>
                            <p class="text-sm">{{ $manga->tv_rating->description }}.</p>
                        </x-slot:footer>
                    </x-information-list>

                    <x-information-list id="languages" title="{{ __('Languages') }}" icon="{{ asset('images/symbols/globe.svg') }}">
                        <x-slot:information>
                            {{ $manga->languages->pluck('name')->join(', ', ' and ') ?: '-' }}
                        </x-slot:information>
                    </x-information-list>

{{--                    <x-information-list title="{{ __('Studio') }}" icon="{{ asset('images/symbols/building_2.svg') }}">--}}
{{--                        <x-slot:information>--}}
{{--                            {{ $manga->studios()->first()->name ?? '-' }}--}}
{{--                        </x-slot:information>--}}
{{--                    </x-information-list>--}}
{{----}}
{{--                    <x-information-list title="{{ __('Network') }}" icon="{{ asset('images/symbols/dot_radiowaves_left_and_right.svg') }}">--}}
{{--                        <x-slot:information>--}}
{{--                            {{ $manga->studios()->first()->name ?? '-' }}--}}
{{--                        </x-slot:information>--}}
{{--                    </x-information-list>--}}
                </div>
            </section>

            @if ($readyToLoad)
                <livewire:components.manga-cast-section :manga="$manga" />

                <livewire:components.manga-staff-section :manga="$manga" />

                <livewire:components.manga-studios-section :manga="$manga" />

                <div class="bg-orange-50">
                    @if (!empty($this->studio))
                        <livewire:components.manga-more-by-studio-section :manga="$manga" :studio="$this->studio" />
                    @endif

                    <livewire:components.manga.manga-relations-section :manga="$manga" />

                    <livewire:components.manga.anime-relations-section :manga="$manga" />

                    <livewire:components.manga.game-relations-section :manga="$manga" />

                    @if (!empty($manga->copyright))
                        <section class="pt-4 pr-4 pb-4 pl-4 border-t">
                            <p class="text-sm text-gray-400">{!! nl2br(e($manga->copyright)) !!}</p>
                        </section>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$manga->id" :model-type="$manga->getMorphClass()" :user-rating="$userRating?->first()" />

    <x-dialog-modal maxWidth="md" model="showPopup">
        <x-slot:title>
            {{ $popupData['title'] }}
        </x-slot:title>

        <x-slot:content>
            <p>{{ $popupData['message'] }}</p>
        </x-slot:content>

        <x-slot:footer>
            <x-button wire:click="$toggle('showPopup')">{{ __('Ok') }}</x-button>
        </x-slot:footer>
    </x-dialog-modal>
</main>
