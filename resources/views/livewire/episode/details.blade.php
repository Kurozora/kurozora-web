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
        <meta property="og:image" content="{{ $episode->banner_image_url ?? $season->poster_image_url ?? asset('images/static/placeholders/episode_banner.webp') }}" />
        <meta property="og:type" content="video.episode" />
        <meta property="video:duration" content="{{ $episode->duration }}" />
        <meta property="video:release_date" content="{{ $episode->first_aired }}" />
        <meta property="video:series" content="{{ $anime->title }}" />
        <meta property="twitter:title" content="{{ $episode->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $episode->synopsis }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ $episode->banner_image_url ?? $season->poster_image_url ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $episode->synopsis }}" />
        <link rel="canonical" href="{{ route('episodes.details', $episode) }}">
        <x-misc.schema>
            "@type":"TVEpisode",
            "url":"/episode/{{ $episode->id }}/",
            "name": "{{ $episode->title }}",
            "alternateName": "{{ $anime->original_title }}",
            "image": "{{ $episode->banner_image_url ?? $season->poster_image_url ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
            "description": "{{ $episode->synopsis }}",
            "aggregateRating": {
                "@type":"AggregateRating",
                "itemReviewed": {
                    "@type": "TVEpisode",
                    "image": [
                        "{{ $episode->banner_image_url ?? $season->poster_image_url ?? asset('images/static/promotional/social_preview_icon_only.webp') }}"
                    ],
                    "name": "{{ $episode->title }}"
                },
                "ratingCount": {{ $episode->stats?->rating_count ?? 1 }},
                "bestRating": 5,
                "worstRating": 0,
                "ratingValue": {{ $episode->stats?->rating_average ?? 2.5 }}
            },
            "contentRating": "{{ $anime->tv_rating->name }}",
            "genre": {!! $anime->genres()->pluck('name') !!},
            "datePublished": "{{ $episode->first_aired?->format('Y-m-d') }}",
            "keywords": "anime,episode{{ (',' . $anime->keywords) ?? '' }}",
            "creator":[
                {
                    "@type":"Organization",
                    "url":"/studio/{{ $anime->studios?->firstWhere('is_studio', '=', true)?->id ?? $anime->studios->first()?->id }}/"
                }
            ]
            @if(!empty($episode->video_url) || !empty($anime->video_url))
                ,"trailer": {
                    "@type":"VideoObject",
                    "name":"{{ $episode->title }}",
                    "description":"Official Trailer",
                    "embedUrl": "{{ $episode->video_url ?? $anime->video_url }}",
                    "thumbnailUrl": "{{ $episode->banner_image_url ?? $anime->poster_image_url ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
                    "uploadDate": "{{ $episode->first_aired?->format('Y-m-d') }}"
                }
            @endif
        </x-misc.schema>

        <link rel="stylesheet" href="{{ url(mix('css/watch.css')) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        episodes/{{ $episode->id }}
    </x-slot:appArgument>

    <x-slot:scripts>
        <script src="{{ url(mix('js/watch.js')) }}"></script>
    </x-slot:scripts>

    <div
        class="relative w-full"
        x-bind:class="{'max-w-7xl': !theaterMode, '': theaterMode}"
        x-data="{
{{--            showVideo: $persist(@entangle('showVideo')).as('_x_showVideo_{{ $anime->slug }}'),--}}
            preferredVideoSource: $persist(@entangle('preferredVideoSource')),
            theaterMode: $persist(false)
        }"
    >
        <div
            x-bind:class="{'lg:p-4': !theaterMode, '': theaterMode}"
            x-bind:style="theaterMode && {'height': '56.2vw', 'max-height': 'calc(100vh - 169px)'}"
        >
            <div
                class="relative w-full h-full overflow-hidden z-0"
                :class="{'lg:rounded-3xl lg:shadow-xl': !theaterMode}"
                style="background-color: {{ $episode->banner_image?->custom_properties['background_color'] ?? '#000000' }};"
            >
                <div class="relative w-full h-full overflow-hidden z-10">
                    @if (!empty($this->video))
                        {!! $this->video->getEmbed() !!}
                    @else
                        <x-picture>
                            <img class="w-full h-full aspect-video object-cover lazyload" data-sizes="auto" data-src="{{ $episode->banner_image_url ?? $season->poster_image_url ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $episode->title }} Banner" title="{{ $episode->title }}">
                        </x-picture>
                    @endif
                </div>
            </div>
        </div>

        <div class="pt-4 2xl:col-span-2">
            <section class="flex flex-row flex-wrap justify-between gap-1 pb-8 px-4 sm:flex-nowrap">
                <div class="flex justify-between gap-1 w-full">
                    <div class="flex flex-nowrap">
                        <picture class="relative min-w-[100px] max-w-[100px] min-h-[150px] max-h-[150px] mr-2 rounded-lg overflow-hidden">
                            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $season->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $season->title }} Poster" title="{{ $season->title }}">
                            <div class="absolute top-0 left-0 h-full w-full ring-1 ring-gray-100 ring-opacity-25 ring-inset rounded-lg"></div>
                        </picture>

                        <div class="flex flex-col gap-1">
                            <p class="font-semibold text-lg leading-tight break-all">{{ $episode->title }}</p>
                            <p class="">6.1K Watched</p>
                            <p class="">612312 Views <span>{{ $episode->first_aired->toFormattedDateString() }}</span></p>
                        </div>
                    </div>

                    <div class="flex flex-nowrap justify-end gap-1 h-10">
                        {{-- Watch --}}
                        <livewire:episode.watch-button :episode="$episode" wire:key="{{ md5($episode->id) }}" />
                    </div>
                </div>

                <div class="w-full sm:w-auto">
                    <div class="flex flex-nowrap justify-end gap-1 h-10">
                        @if (!empty($episode->videos()->count()))
                            <x-dropdown align="right" width="48">
                                <x-slot:trigger>
                                    <x-circle-button
                                        title="{{ __('Source') }}"
                                    >
                                        @svg('list_and_film', 'fill-current', ['width' => '28'])
                                    </x-circle-button>
                                </x-slot:trigger>

                                <x-slot:content>
                                    @foreach ($episode->videos as $video)
                                        <button
                                            :class="{'bg-white text-gray-400 hover:bg-gray-50 focus:bg-gray-200': preferredVideoSource !== '{{ $video->source->key }}', 'bg-orange-500 text-white': preferredVideoSource === '{{ $video->source->key }}'}"
                                            class="block w-full px-4 py-2 text-xs text-center font-semibold"
                                            wire:click="selectPreferredSource('{{ $video->source->key }}')"
                                        >
                                            {{ $video->source->description }}
                                        </button>
                                    @endforeach
                                </x-slot:content>
                            </x-dropdown>
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
                                <button class="block w-full px-4 py-2 bg-white text-gray-400 text-xs text-center font-semibold hover:bg-gray-50 focus:bg-gray-200">
                                    {{ __('Share') }}
                                </button>

                                <button class="block w-full px-4 py-2 bg-white text-red-500 text-xs text-center font-semibold hover:bg-gray-50 focus:bg-gray-200">
                                    {{ __('Report') }}
                                </button>
                            </x-slot:content>
                        </x-dropdown>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="pt-4 2xl:col-span-2">
        <section id="badges" class="flex flex-row flex-nowrap whitespace-nowrap justify-between text-center pt-5 pb-8 px-4 overflow-x-scroll no-scrollbar">
            <div id="ratingBadge" class="flex-grow pr-12">
                <a href="#ratingsAndReviews">
                    <p class="inline-flex font-bold text-orange-500">
                        {{ number_format($episode->stats?->rating_average, 1) }}
                        <livewire:anime.star-rating :rating="$episode->stats?->rating_average" :star-size="'sm'" :disabled="true" />
                    </p>
                    <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x reviews', (int) $episode->stats?->rating_count, ['x' => number_shorten((int) $episode->stats?->rating_count, 0, true)]) }}</p>
                </a>
            </div>

            <div id="seasonBadge" class="flex-grow px-12 border-l-2">
                <a href="{{ route('anime.seasons', $anime) }}">
                    <p class="font-bold">#{{ $season->number }}</p>
                    <p class="text-sm text-gray-500">{{ __('Season') }}</p>
                </a>
            </div>

            @if($episode->previous_episode_id)
                <div id="episodeBadge" class="flex-grow px-12 border-l-2">
                    <a href="{{ route('episodes.details', $episode->previous_episode_id) }}">
                        <p class="font-bold">{{ __('Episode :x', ['x' => $episode->previous_episode->number_total]) }}</p>
                        <p class="text-sm text-gray-500">{{ __('Previous') }}</p>
                    </a>
                </div>
            @endif

            @if($episode->next_episode_id)
                <div id="episodeBadge" class="flex-grow px-12 border-l-2">
                    <a href="{{ route('episodes.details', $episode->next_episode_id) }}">
                        <p class="font-bold">{{ __('Episode :x', ['x' => $episode->next_episode->number_total]) }}</p>
                        <p class="text-sm text-gray-500">{{ __('Up Next') }}</p>
                    </a>
                </div>
            @endif

            <div id="animeBadge" class="flex-grow px-12 border-l-2">
                <a href="{{ route('anime.details', $anime) }}">
                    <p class="font-bold line-clamp-1">{{ substr($anime->title, 0, 25) }}</p>
                    <p class="text-sm text-gray-500">{{ __('Anime') }}</p>
                </a>
            </div>
        </section>

        @if (!empty($episode->synopsis))
            <section class="pt-5 pb-8 px-4 border-t-2">
                <x-section-nav class="flex flex-nowrap justify-between mb-5">
                    <x-slot:title>
                        {{ __('Synopsis') }}
                    </x-slot:title>
                </x-section-nav>

                <x-truncated-text>
                    <x-slot:text>
                        {!! nl2br($episode->synopsis) !!}
                    </x-slot:text>
                </x-truncated-text>
            </section>
        @endif

        <section id="ratingsAndReviews" class="pt-5 pb-8 px-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Ratings & Reviews') }}
                </x-slot:title>
            </x-section-nav>

            <div class="flex flex-row justify-between">
                <div class="text-center">
                    <p class="font-bold text-6xl">{{ number_format($episode->stats?->rating_average, 1) }}</p>
                    <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                </div>

                @auth
                    <div class="text-right">
                        <livewire:anime.star-rating :rating="$episode->stats?->rating_average" :star-size="'lg'" :disabled="true" />
                        <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x reviews', (int) $episode->stats?->rating_count, ['x' => (int) $episode->stats?->rating_count]) }}</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="pt-5 pb-8 px-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Information') }}
                </x-slot:title>
            </x-section-nav>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-x-4 gap-y-4">
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
                    @if (!empty($episode->first_aired))
                        <x-slot:information>
                            🚀 {{ $episode->first_aired->toFormattedDateString() }}
                        </x-slot:information>

                        <x-slot:footer>
                            @if ($episode->first_aired->isFuture())
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

        <x-dialog-modal wire:model="showPopup">
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
    </div>
</main>
