<main>
    <x-slot:title>
        {!! __('Watch :x episode :y', ['x' => $anime->title, 'y' => $episode->number_total]) !!} | {!! $episode->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Watch English subbed anime episodes for free.') }}
        {{ $episode->synopsis }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x episode :y', ['x' => $anime->title, 'y' => $episode->number_total]) }} | {{ $episode->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $episode->synopsis ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/episode_banner.webp') }}" />
        <meta property="og:type" content="video.episode" />
        <meta property="og:video:type" content="text/html">
        <meta property="video:series" content="{{ $anime->title }}" />
        <meta property="og:video:url" content="{{ route('embed.episodes', $episode) }}">
        <meta property="og:video:height" content="1080">
        <meta property="og:video:width" content="1920">
        <meta property="video:duration" content="{{ $episode->duration }}" />
        <meta property="video:release_date" content="{{ $episode->started_at?->toIso8601String() }}" />
        <meta property="twitter:title" content="{{ $episode->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $episode->synopsis }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $episode->synopsis }}" />
        <link rel="canonical" href="{{ route('episodes.details', $episode) }}">
        <x-misc.schema>
            "@type":"TVEpisode",
            "url":"/episode/{{ $episode->id }}/",
            "name": "{{ $episode->title }}",
            "alternateName": "{{ $anime->original_title }}",
            "image": "{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
            "description": "{{ $episode->synopsis }}",
            "aggregateRating": {
                "@type":"AggregateRating",
                "itemReviewed": {
                    "@type": "TVEpisode",
                    "image": [
                        "{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}"
                    ],
                    "name": "{{ $episode->title }}"
                },
                "ratingCount": {{ $episode->mediaStat->rating_count ?? 1 }},
                "bestRating": 5,
                "worstRating": 0,
                "ratingValue": {{ $episode->mediaStat->rating_average ?? 2.5 }}
            },
            "contentRating": "{{ $anime->tv_rating->name }}",
            "genre": {!! $anime->genres->pluck('name') !!},
            "datePublished": "{{ $episode->started_at?->format('Y-m-d') }}",
            "keywords": "anime,episode{{ (',' . $anime->keywords) ?? '' }}",
            "creator":[
                {
                    "@type":"Organization",
                    "url":"/studio/{{ $anime->studios?->firstWhere('is_studio', '=', true)?->id ?? $anime->studios?->first()?->id }}/"
                }
            ]
            @if (!empty($episode->videos->first()?->getUrl()) || !empty($anime->videos->first()?->getUrl()))
                ,"trailer": {
                    "@type":"VideoObject",
                    "name":"{{ $episode->title }}",
                    "description":"Official Trailer",
                    "embedUrl": "{{ $episode->videos->first()->getUrl() ?? $anime->videos->first()->getUrl() }}",
                    "thumbnailUrl": "{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
                    "uploadDate": "{{ $episode->started_at?->format('Y-m-d') }}"
                }
            @endif
        </x-misc.schema>

        <link rel="alternate" type="application/json+oembed" href="{{ route('oembed', ['format' => 'json', 'url' => route('episodes.details', $episode)]) }}">
        <link rel="alternate" type="application/json+oembed" href="{{ route('oembed', ['format' => 'xml', 'url' => route('episodes.details', $episode)]) }}">
    </x-slot:meta>

    <x-slot:styles>
        <link rel="preload" href="{{ url(mix('css/watch.css')) }}" as="style">
        <link rel="stylesheet" href="{{ url(mix('css/watch.css')) }}">
        <link rel="preload" href="{{ url(mix('css/chat.css')) }}" as="style">
        <link rel="stylesheet" href="{{ url(mix('css/chat.css')) }}">
    </x-slot:styles>

    <x-slot:appArgument>
        episodes/{{ $episode->id }}
    </x-slot:appArgument>

    <x-slot:scripts>
        <script src="{{ url(mix('js/watch.js')) }}"></script>
        <script src="{{ url(mix('js/chat.js')) }}"></script>
    </x-slot:scripts>

    <div
        class="relative w-full"
        x-bind:class="{'lg:pt-4 lg:pl-4 lg:pr-4': !theaterMode, '': theaterMode}"
        x-data="{
            preferredVideoSource: $persist(@entangle('preferredVideoSource').live),
            theaterMode: $persist(false),
            showChat: $persist(false)
        }"
    >
        <div
            class="flex flex-col lg:flex-row"
            x-bind:class="{'lg:gap-6': !theaterMode, '': theaterMode}"
        >
            {{-- Video --}}
            <section
                class="w-full"
                x-bind:class="{
                    'max-w-7xl': !theaterMode,
                    '': theaterMode || !showChat,
                    'lg:w-3/4': theaterMode && showChat
                }"
            >
                <article
                    x-bind:style="theaterMode && {'height': '56.2vw', 'max-height': 'calc(100vh - 169px)'}"
                >
                    <div
                        class="relative w-full h-full overflow-hidden z-0"
                        :class="{'lg:rounded-3xl lg:natural-shadow': !theaterMode, '': theaterMode}"
                        style="background-color: {{ $episode->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                    >
                        <div class="relative w-full h-full overflow-hidden z-10">
                            @if ($this->video)
                                {!! $this->video->getEmbed(['currentTime' => $this->timestamp]) !!}
                            @else
                                <x-picture
                                    class="h-full"
                                    style="background-color: {{ $episode->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                                >
                                    <img class="w-full h-full aspect-video object-cover lazyload" data-sizes="auto" data-src="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $episode->title }} Banner" title="{{ $episode->title }}">
                                </x-picture>
                            @endif
                        </div>
                    </div>
                </article>
            </section>

            {{-- Chat Box --}}
            <section
                class="border border-primary overflow-hidden aspect-video"
                x-bind:class="{
                    'lg:w-1/2 lg:rounded-3xl lg:shadow-md': !theaterMode,
                    'lg:w-1/4': theaterMode
                }"
                x-bind:style="theaterMode && {'height': '56.2vw', 'max-height': 'calc(100vh - 169px)'}"
                x-show="theaterMode ? showChat : true"
            >
                <livewire:components.chat-box :model="$episode" />
            </section>
        </div>

        <div
            class="flex flex-col justify-between gap-6 pt-6 lg:flex-row"
            x-bind:class="{'': !theaterMode, 'lg:pt-4 lg:pl-4 lg:pr-4': theaterMode}"
        >
            <div
                class="flex flex-col gap-6 lg:w-3/4"
                x-bind:class="{'max-w-7xl': !theaterMode, '': theaterMode}"
            >
                {{-- Bio lockup --}}
                <section class="flex flex-row flex-wrap justify-between gap-1 pl-4 pr-4 sm:flex-nowrap lg:pr-0">
                    <div class="flex justify-between gap-1 w-full">
                        <div class="flex flex-nowrap">
                            <picture
                                class="relative shrink-0 w-28 h-40 mr-2 rounded-lg overflow-hidden"
                                style="background-color: {{ $season->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                            >
                                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $season->title }} Poster" title="{{ $season->title }}">

                                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                            </picture>

                            <div class="flex flex-col gap-1">
                                <p class="font-semibold text-lg leading-tight break-all">{{ $episode->title }}</p>
{{--                                <p class="">6.1K Watched</p>--}}
                                <p class="">{{ $episode->view_count ? __(':x views', ['x' => number_format($episode->view_count)]) . ' ' : '' }}<span>{{ $episode->started_at?->toFormattedDateString() }}</span></p>
                            </div>
                        </div>

                        <div class="flex flex-nowrap justify-end gap-1 h-10">
                            {{-- Watch --}}
                            <livewire:episode.watch-button :episode="$episode" wire:key="{{ uniqid($episode->id, true) }}" />

                            @if ($isTracking)
                                {{-- Reminders --}}
                                <x-circle-button wire:click="remindAnime">
                                    @if ($isReminded)
                                        @svg('bell_fill', 'fill-current', ['width' => '44'])
                                    @else
                                        @svg('bell', 'fill-current', ['width' => '44'])
                                    @endif
                                </x-circle-button>
                            @endif
                        </div>
                    </div>

                    <div class="w-full sm:w-auto">
                        <div class="flex flex-nowrap justify-end gap-1 h-10">
                            {{-- Chat Toggle --}}
                            <x-circle-button
                                x-on:click="showChat = !showChat"
                                x-bind:title="showChat ? '{{ __('Close Chat') }}' : '{{ __('Open Chat') }}'"
                                x-show="theaterMode"
                            >
                                <template x-if="!showChat">
                                    @svg('bubble_left_and_bubble_right', 'fill-current', ['width' => '28'])
                                </template>

                                <template x-if="showChat">
                                    @svg('bubble_left_and_bubble_right_fill', 'fill-current', ['width' => '28'])
                                </template>
                            </x-circle-button>

                            {{-- Video Source --}}
                            <livewire:components.episode.video-sources :model="$episode" />

                            {{-- Theater Mode --}}
                            <x-circle-button
                                x-on:click="theaterMode = !theaterMode"
                                x-bind:title="theaterMode ? '{{ __('Theater Mode') }}' : '{{ __('Default Mode') }}'"
                            >
                                <template x-if="theaterMode">
                                    @svg('rectangle_inset_topleft_filled', 'fill-current', ['width' => '28'])
                                </template>

                                <template x-if="!theaterMode">
                                    @svg('rectangle_fill', 'fill-current', ['width' => '28'])
                                </template>
                            </x-circle-button>

                            {{-- Nova --}}
                            <x-nova-link :href="route('episodes.edit', $episode)">
                                @svg('pencil', 'fill-current', ['width' => '44'])
                            </x-nova-link>

                            {{-- More Options --}}
                            <x-dropdown align="right" width="48">
                                <x-slot:trigger>
                                    <x-circle-button
                                        title="{{ __('More Settings') }}"
                                    >
                                        @svg('ellipsis', 'fill-current', ['width' => '28'])
                                    </x-circle-button>
                                </x-slot:trigger>

                                <x-slot:content>
                                    <button
                                        class="block w-full pl-4 pr-4 pt-2 pb-2 bg-secondary text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                        wire:click="$toggle('showSharePopup')"
                                    >
                                        {{ __('Share') }}
                                    </button>

{{--                                    <button class="block w-full pl-4 pr-4 pt-2 pb-2 bg-secondary text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary">--}}
{{--                                        {{ __('Report') }}--}}
{{--                                    </button>--}}
                                </x-slot:content>
                            </x-dropdown>
                        </div>
                    </div>
                </section>

                <section id="badges" class="flex flex-row flex-nowrap whitespace-nowrap justify-between text-center pl-4 pr-4 overflow-x-scroll no-scrollbar">
                    <div id="ratingBadge" class="flex-grow pr-12">
                        <a href="#ratingsAndReviews">
                            <p class="font-bold text-tint">
                                {{ number_format($episode->mediaStat->rating_average, 1) }}
                            </p>

                            <livewire:components.star-rating :rating="$episode->mediaStat->rating_average" :star-size="'sm'" :disabled="true" />

                            <p class="text-sm text-secondary">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x reviews', (int) $episode->mediaStat->rating_count, ['x' => number_shorten((int) $episode->mediaStat->rating_count, 0, true)]) }}</p>
                        </a>
                    </div>

                    <div id="seasonBadge" class="flex-grow px-12 border-l border-primary">
                        <a class="flex flex-col items-center" href="{{ route('anime.seasons', $anime) }}" wire:navigate>
                            <p class="font-bold">{{ __('#:x', ['x' => $season->number]) }}</p>
                            <p class="text-tint">
                                @svg('tv_fill', 'fill-current', ['width' => '20'])
                            </p>
                            <p class="text-sm text-secondary">{{ __('Season') }}</p>
                        </a>
                    </div>

                    <div id="rankingBadge" class="flex-grow px-12 border-l border-primary">
                        <a class="flex flex-col items-center" href="{{ route('charts.top', App\Enums\ChartKind::Episodes) }}" wire:navigate>
                            <p class="font-bold">{{ trans_choice('{0} -|[1,*] #:x', $episode->mediaStat->rank_total ?? 0, ['x' => $episode->mediaStat->rank_total]) }}</p>
                            <p class="text-tint">
                                @svg('chart_bar_fill', 'fill-current', ['width' => '20'])
                            </p>
                            <p class="text-sm text-secondary">{{ __('Chart') }}</p>
                        </a>
                    </div>

                    @if ($previousEpisode)
                        <div id="previousEpisodeBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center" href="{{ route('episodes.details', $previousEpisode) }}" wire:navigate>
                                <p class="font-bold">{{ __('Episode :x', ['x' => $previousEpisode->number_total]) }}</p>
                                <p class="text-tint">
                                    @svg('arrowshape_turn_up_backward_tv_fill', 'fill-current', ['width' => '20'])
                                </p>
                                <p class="text-sm text-secondary">{{ __('Previous') }}</p>
                            </a>
                        </div>
                    @endif

                    @if ($nextEpisode)
                        <div id="nextEpisodeBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center" href="{{ route('episodes.details', $nextEpisode) }}" wire:navigate>
                                <p class="font-bold">{{ __('Episode :x', ['x' => $nextEpisode->number_total]) }}</p>
                                <p class="text-tint">
                                    @svg('arrowshape_turn_up_forward_tv_fill', 'fill-current', ['width' => '20'])
                                </p>
                                <p class="text-sm text-secondary">{{ __('Next') }}</p>
                            </a>
                        </div>
                    @endif

                    <div id="animeBadge" class="flex-grow px-12 border-l border-primary">
                        <a class="flex flex-col items-center" href="{{ route('anime.details', $anime) }}" wire:navigate>
                            <p class="font-bold line-clamp-1">{{ substr($anime->title, 0, 25) }}</p>
                            <p class="text-tint">
                                @svg('tv_fill', 'fill-current', ['width' => '20'])
                            </p>
                            <p class="text-sm text-secondary">{{ __('Anime') }}</p>
                        </a>
                    </div>
                </section>

                @if (!empty($episode->synopsis))
                    <section class="pb-8">
                        <x-hr class="ml-4 mr-4 pb-5" />

                        <x-section-nav class="flex flex-nowrap justify-between mb-5">
                            <x-slot:title>
                                {{ __('Synopsis') }}
                            </x-slot:title>
                        </x-section-nav>

                        <x-truncated-text class="ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($episode->synopsis)) !!}
                            </x-slot:text>
                        </x-truncated-text>
                    </section>
                @endif

                <section id="ratingsAndReviews" class="pb-8">
                    <x-hr class="ml-4 mr-4 pb-5" />

                    <x-section-nav>
                        <x-slot:title>
                            {{ __('Ratings & Reviews') }}
                        </x-slot:title>

                        <x-slot:action>
                            <x-section-nav-link href="{{ route('episodes.reviews', $episode) }}">{{ __('See All') }}</x-section-nav-link>
                        </x-slot:action>
                    </x-section-nav>

                    <div class="flex flex-row flex-wrap justify-between gap-4 pl-4 pr-4">
                        <div class="flex flex-col justify-end text-center">
                            <p class="font-bold text-6xl">{{ number_format($episode->mediaStat->rating_average, 1) }}</p>
                            <p class="font-bold text-sm text-secondary">{{ __('out of') }} 5</p>
                        </div>

                        <div class="flex flex-col justify-end items-center text-center">
                            @svg('star_fill', 'fill-current', ['width' => 32])
                            <p class="font-bold text-2xl">{{ number_format($episode->mediaStat->highestRatingPercentage) }}%</p>
                            <p class="text-sm text-secondary">{{ $episode->mediaStat->sentiment }}</p>
                        </div>

                        <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                            <x-star-rating-bar :media-stat="$episode->mediaStat" />

                            <p class="text-sm text-secondary">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $episode->mediaStat->rating_count, ['x' => number_format($episode->mediaStat->rating_count)]) }}</p>
                        </div>
                    </div>
                </section>

                <section id="writeAReview" class="pb-8">
                    <x-hr class="ml-4 mr-4 pb-5" />

                    <div class="flex flex-row flex-wrap gap-4 pl-4 pr-4">
                        <div class="flex justify-between items-center">
                            <p class="">{{ __('Click to Rate:') }}</p>

                            <livewire:components.star-rating :model-id="$episode->id" :model-type="$episode->getMorphClass()" :rating="$userRating->first()?->rating" :star-size="'md'" />
                        </div>

                        <div class="flex justify-between">
                            <x-simple-button class="flex gap-1" wire:click="$dispatch('show-review-box', { 'id': '{{ $this->reviewBoxID }}' })">
                                @svg('pencil', 'fill-current', ['width' => 18])
                                {{ __('Write a Review') }}
                            </x-simple-button>
                        </div>

                        <div></div>
                    </div>

                    <div class="mt-5">
                        <livewire:sections.reviews :model="$episode" />
                    </div>
                </section>

                <section class="pb-8">
                    <x-hr class="ml-4 mr-4 pb-5" />

                    <x-section-nav>
                        <x-slot:title>
                            {{ __('Information') }}
                        </x-slot:title>
                    </x-section-nav>

                    <div class="grid grid-cols-2 gap-4 pl-4 pr-4 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                        <x-information-list id="number" title="{{ __('Number') }}" icon="{{ asset('images/symbols/number.svg') }}">
                            <x-slot:information>
                                {{ $episode->number_total }}
                            </x-slot:information>

                            <x-slot:footer>
                                <p class="text-sm">{{ __('#:x in the current season.', ['x' => $episode->number]) }}</p>
                            </x-slot:footer>
                        </x-information-list>

                        <x-information-list id="duration" title="{{ __('Duration') }}" icon="{{ asset('images/symbols/hourglass.svg') }}">
                            <x-slot:information>
                                {{ $episode->duration_string ?? '-' }}
                            </x-slot:information>
                        </x-information-list>

                        <x-information-list id="aired" title="{{ __('Aired') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                            @if (!empty($episode->started_at))
                                <x-slot:information>
                                    🚀 {{ $episode->started_at->toFormattedDateString() }}
                                </x-slot:information>

                                <x-slot:footer>
                                    @if ($episode->started_at->isFuture())
                                        {{ __('The episode will air on the announced date.') }}
                                    @else
                                        {{ __('The episode has finished airing.') }}
                                    @endif
                                </x-slot:footer>
                            @else
                                <x-slot:information>
                                    -
                                </x-slot:information>
                                <x-slot:footer>
                                    {{ __('Airing date is unknown.') }}
                                </x-slot:footer>
                            @endif
                        </x-information-list>
                    </div>
                </section>
            </div>

            <div class="flex flex-col gap-4 pb-4 lg:w-1/4">
                @if ($nextEpisode)
                    <livewire:components.episode.up-next :next-episode="$nextEpisode" />
                @endif

                <livewire:components.episode.suggested-episodes :title="$this->episode->title" :next-episode-id="$this->episode->next_episode_id" />
            </div>
        </div>
    </div>

    <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$episode->id" :model-type="$episode->getMorphClass()" :user-rating="$userRating?->first()" />

    @if ($showSharePopup)
        <x-share-modal
            model="showSharePopup"
            :link="route('episodes.details', $this->episode)"
            :embed-link="route('embed.episodes', $this->episode)"
            :title="$this->episode->title"
            :image-url="$this->episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner())"
            :type="'episode'"
        />
    @endif
</main>
