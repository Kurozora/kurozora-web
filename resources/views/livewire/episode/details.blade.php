<main>
    <x-slot:title>
        {!! __('Watch :x episode :y', ['x' => $this->anime->title, 'y' => $episode->number_total]) !!} | {!! $episode->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Watch English subbed anime episodes for free.') }}
        {{ $episode->synopsis }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x episode :y', ['x' => $this->anime->title, 'y' => $episode->number_total]) }} | {{ $episode->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $episode->synopsis ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $this->season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/episode_banner.webp') }}" />
        <meta property="og:type" content="video.episode" />
        <meta property="og:video:type" content="text/html">
        <meta property="video:series" content="{{ $this->anime->title }}" />
        <meta property="og:video:url" content="{{ route('embed.episodes', $episode) }}">
        <meta property="og:video:height" content="1080">
        <meta property="og:video:width" content="1920">
        <meta property="video:duration" content="{{ $episode->duration }}" />
        <meta property="video:release_date" content="{{ $episode->started_at?->toIso8601String() }}" />
        <meta property="twitter:title" content="{{ $episode->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $episode->synopsis }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $this->season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $episode->synopsis }}" />
        <link rel="canonical" href="{{ route('episodes.details', $episode) }}">
        <x-misc.schema>
            "@type":"TVEpisode",
            "url":"/episode/{{ $episode->id }}/",
            "name": "{{ $episode->title }}",
            "alternateName": "{{ $this->anime->original_title }}",
            "image": "{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $this->season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
            "description": "{{ $episode->synopsis }}",
            "aggregateRating": {
                "@type":"AggregateRating",
                "itemReviewed": {
                    "@type": "TVEpisode",
                    "image": [
                        "{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $this->season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}"
                    ],
                    "name": "{{ $episode->title }}"
                },
                "ratingCount": {{ $episode->mediaStat->rating_count ?? 1 }},
                "bestRating": 5,
                "worstRating": 0,
                "ratingValue": {{ $episode->mediaStat->rating_average ?? 2.5 }}
            },
            "contentRating": "{{ $this->anime->tv_rating->name }}",
            "genre": {!! $this->anime->genres->pluck('name') !!},
            "datePublished": "{{ $episode->started_at?->format('Y-m-d') }}",
            "keywords": "anime,episode{{ (',' . $this->anime->keywords) ?? '' }}",
            "creator":[
                {
                    "@type":"Organization",
                    "url":"/studio/{{ $this->anime->studios?->firstWhere('is_studio', '=', true)?->id ?? $this->anime->studios?->first()?->id }}/"
                }
            ]
            @if(!empty($episode->videos->first()?->getUrl()) || !empty($this->anime->videos->first()?->getUrl()))
                ,"trailer": {
                    "@type":"VideoObject",
                    "name":"{{ $episode->title }}",
                    "description":"Official Trailer",
                    "embedUrl": "{{ $episode->videos->first()->getUrl() ?? $this->anime->videos->first()->getUrl() }}",
                    "thumbnailUrl": "{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $this->anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
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
        wire:init="loadPage"
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
                        :class="{'lg:rounded-3xl lg:shadow-xl': !theaterMode, '': theaterMode}"
                        style="background-color: {{ $episode->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? '#000000' }};"
                    >
                        <div class="relative w-full h-full overflow-hidden z-10">
                            @if ($this->video)
                                {!! $this->video->getEmbed(['currentTime' => $this->timestamp]) !!}
                            @else
                                <x-picture class="h-full">
                                    <img class="w-full h-full aspect-video object-cover lazyload" data-sizes="auto" data-src="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $this->season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $episode->title }} Banner" title="{{ $episode->title }}">
                                </x-picture>
                            @endif
                        </div>
                    </div>
                </article>
            </section>

            {{-- Chat Box --}}
            <section
                class="border overflow-hidden aspect-video"
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
                <section class="flex flex-row flex-wrap justify-between gap-1 pl-4 pr-4 sm:flex-nowrap lg:pl-0 lg:pr-0">
                    <div class="flex justify-between gap-1 w-full">
                        <div class="flex flex-nowrap">
                            <picture class="relative min-w-[100px] max-w-[100px] min-h-[150px] max-h-[150px] mr-2 rounded-lg overflow-hidden">
                                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $this->season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $this->season->title }} Poster" title="{{ $this->season->title }}">
                                <div class="absolute top-0 left-0 h-full w-full ring-1 ring-gray-100 ring-opacity-25 ring-inset rounded-lg"></div>
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
                                <x-circle-button color="yellow" wire:click="remindAnime">
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

                            @if ($readyToLoad)
                                {{-- Video Source --}}
                                <livewire:components.episode.video-sources :model="$episode" />
                            @endif

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
                            <x-nova-link :resource="\App\Nova\Episode::class" :model="$episode">
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
                                        class="block w-full pl-4 pr-4 pt-2 pb-2 bg-white text-gray-400 text-xs text-center font-semibold hover:bg-gray-50 focus:bg-gray-200"
                                        wire:click="$toggle('showSharePopup')"
                                    >
                                        {{ __('Share') }}
                                    </button>

{{--                                    <button class="block w-full pl-4 pr-4 pt-2 pb-2 bg-white text-red-500 text-xs text-center font-semibold hover:bg-gray-50 focus:bg-gray-200">--}}
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
                            <p class="inline-flex font-bold text-orange-500">
                                {{ number_format($episode->mediaStat->rating_average ?? 0, 1) }}
                                <livewire:components.star-rating :rating="$episode->mediaStat->rating_average" :star-size="'sm'" :disabled="true" />
                            </p>
                            <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x reviews', (int) $episode->mediaStat->rating_count, ['x' => number_shorten((int) $episode->mediaStat->rating_count, 0, true)]) }}</p>
                        </a>
                    </div>

                    <div id="seasonBadge" class="flex-grow px-12 border-l-2">
                        <a href="{{ route('anime.seasons', $this->anime) }}">
                            <p class="font-bold">#{{ $this->season->number }}</p>
                            <p class="text-sm text-gray-500">{{ __('Season') }}</p>
                        </a>
                    </div>

                    @if($episode->previous_episode_id)
                        <div id="previousEpisodeBadge" class="flex-grow px-12 border-l-2">
                            <a href="{{ route('episodes.details', $episode->previous_episode_id) }}">
                                <p class="font-bold">{{ __('Episode :x', ['x' => $episode->previous_episode->number_total]) }}</p>
                                <p class="text-sm text-gray-500">{{ __('Previous') }}</p>
                            </a>
                        </div>
                    @endif

                    @if($episode->next_episode_id)
                        <div id="nextEpisodeBadge" class="flex-grow px-12 border-l-2">
                            <a href="{{ route('episodes.details', $episode->next_episode_id) }}">
                                <p class="font-bold">{{ __('Episode :x', ['x' => $episode->next_episode->number_total]) }}</p>
                                <p class="text-sm text-gray-500">{{ __('Next') }}</p>
                            </a>
                        </div>
                    @endif

                    <div id="animeBadge" class="flex-grow px-12 border-l-2">
                        <a href="{{ route('anime.details', $this->anime) }}">
                            <p class="font-bold line-clamp-1">{{ substr($this->anime->title, 0, 25) }}</p>
                            <p class="text-sm text-gray-500">{{ __('Anime') }}</p>
                        </a>
                    </div>
                </section>

                @if (!empty($episode->synopsis))
                    <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                        <x-section-nav class="flex flex-nowrap justify-between mb-5">
                            <x-slot:title>
                                {{ __('Synopsis') }}
                            </x-slot:title>
                        </x-section-nav>

                        <x-truncated-text>
                            <x-slot:text>
                                {!! nl2br(e($episode->synopsis)) !!}
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
                            <x-section-nav-link class="whitespace-nowrap" href="{{ route('episodes.reviews', $episode) }}">{{ __('See All') }}</x-section-nav-link>
                        </x-slot:action>
                    </x-section-nav>

                    <div class="flex flex-row flex-wrap justify-between gap-4">
                        <div class="flex flex-col justify-end text-center">
                            <p class="font-bold text-6xl">{{ number_format($episode->mediaStat->rating_average, 1) }}</p>
                            <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                        </div>

                        <div class="flex flex-col justify-end items-center text-center">
                            @svg('star_fill', 'fill-current', ['width' => 32])
                            <p class="font-bold text-2xl">{{ number_format($episode->mediaStat->highestRatingPercentage) }}%</p>
                            <p class="text-sm text-gray-500">{{ $episode->mediaStat->sentiment }}</p>
                        </div>

                        <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                            <x-star-rating-bar :media-stat="$episode->mediaStat" />

                            <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $episode->mediaStat->rating_count, ['x' => number_format($episode->mediaStat->rating_count)]) }}</p>
                        </div>
                    </div>
                </section>

                <section id="writeAReview" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                    <div class="flex flex-row flex-wrap gap-4">
                        <div class="flex justify-between items-center">
                            <p class="">{{ __('Click to Rate:') }}</p>

                            <livewire:components.star-rating :model-id="$episode->id" :model-type="$episode->getMorphClass()" :rating="$userRating->first()?->rating" :star-size="'md'" />
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
                        <livewire:sections.reviews :model="$episode" />
                    </div>
                </section>

                <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                    <x-section-nav>
                        <x-slot:title>
                            {{ __('Information') }}
                        </x-slot:title>
                    </x-section-nav>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
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
                @if ($readyToLoad)
                    <livewire:components.episode.up-next :episode="$this->episode" />

                    <livewire:components.episode.suggested-episodes :title="$this->episode->title" :next-episode-id="$this->episode->next_episode_id" />
                @endif
            </div>
        </div>
    </div>

    <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$episode->id" :model-type="$episode->getMorphClass()" :user-rating="$userRating?->first()" />

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

    <x-share-modal
        model="showSharePopup"
        :link="route('episodes.details', $this->episode)"
        :embed-link="route('embed.episodes', $this->episode)"
        :title="$this->episode->title"
        :image-url="$this->episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $this->anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner())"
        :type="'episode'"
    />
</main>
