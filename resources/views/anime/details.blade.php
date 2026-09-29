<x-base-layout>
    <x-slot:title>
        {!! $pageTitle !!}
    </x-slot:title>

    <x-slot:description>
        {{ $metaDescription }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $pageTitle }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $socialDescription }}" />
        <meta property="og:image" content="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:video" content="{{ $anime->video_url ?? '' }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->started_at?->toIso8601String() }}" />
        @foreach ($anime->tags() as $tag)
            <meta property="video:tag" content="{{ $tag->name }}" />
        @endforeach
        <meta property="twitter:title" content="{{ $pageTitle }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $socialDescription }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $anime->synopsis }}" />
        @if ($anime->is_nsfw)
            <meta name="rating" content="adult" />
        @endif
        <link rel="canonical" href="{{ route('anime.details', $anime) }}">
        <x-misc.schema :data="$schema" />
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}
    </x-slot:appArgument>

    <main x-data>
        <div class="pb-6">
            <div class="relative overflow-hidden max-h-[80vh]">
                <div class="relative flex flex-nowrap md:h-full xl:safe-area-inset">
                    <x-picture
                        class="w-full aspect-video max-h-[80vh] overflow-hidden"
                        style="background-color: {{ ($anime->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $anime->getFirstMedia(\App\Enums\MediaCollection::Poster))?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                    >
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $bannerUrl }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">
                    </x-picture>

                    <x-background-extension :source="$bannerUrl" />

                    @if (!empty($anime->video_url))
                        <div class="absolute top-0 bottom-0 left-0 right-0 xl:safe-area-inset">
                            <div class="flex flex-col justify-center items-center h-full md:pb-40 lg:pb-0">
                                <button
                                    class="inline-flex items-center pt-4 pr-4 pb-4 pl-4 bg-blur backdrop-blur border border-transparent rounded-full font-semibold text-xs uppercase tracking-widest shadow-md hover:bg-tint-800 hover:btn-text-tinted active:bg-tint active:btn-text-tinted focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                                    x-on:click="$dispatch('open-modal', { id: 'trailer-video' })"
                                >
                                    @svg('play_fill', 'fill-current', ['width' => '34'])
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="md:absolute md:bottom-0 md:left-0 md:right-0 lg:pl-4 lg:pr-4 xl:safe-area-inset">
                    <div class="relative flex flex-nowrap pt-4 pb-8 pl-4 pr-4 md:mx-auto md:mb-8 md:p-2 md:max-w-lg md:bg-blur md:backdrop-filter md:backdrop-blur md:rounded-lg">
                        <div class="absolute top-0 right-0 left-0 h-full rounded-lg md:bg-blur md:backdrop-filter md:backdrop-blur"></div>

                        <x-picture
                            :border="true"
                            class="w-28 h-40 mr-2 rounded-lg overflow-hidden"
                            style="min-width: 7rem; max-height: 10rem; background-color: {{ $anime->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                        >
                            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">
                        </x-picture>

                        <div class="relative flex flex-col gap-2 justify-between w-full">
                            <div>
                                <p class="font-semibold text-lg leading-tight break-all">{{ $anime->title }}</p>

                                <p class="text-sm leading-tight">{{ $anime->information_summary }}</p>

                                <div class="flex w-full justify-between mt-2 gap-1 sm:gap-4">
                                    <p class="flex-grow pt-1 pr-1 pb-1 pl-1 text-white text-center text-xs font-semibold whitespace-nowrap rounded-md" style="background-color: {{ $anime->status->color }};">{{ $anime->status->name }}</p>

                                    <p class="flex-grow pt-1 pr-1 pb-1 pl-1 bg-white text-black text-center text-xs font-semibold whitespace-nowrap rounded-md"> {{ trans_choice('{0} Rank -|[1,*] Rank #:x', $anime?->mediaStat?->rank_total ?? 0, ['x' => $anime?->mediaStat?->rank_total ?? 0]) }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1 justify-between">
                                <div class="flex gap-2">
                                    <x-library-button :model="$anime" />

                                    <x-nova-link :href="route('anime.edit', $anime)">
                                        @svg('pencil', 'fill-current', ['width' => '44'])
                                    </x-nova-link>
                                </div>

                                <div
                                    class="flex gap-2"
                                    x-data="titleActions({{ Js::from(['type' => $anime->getMorphClass(), 'id' => $anime->id, 'tracking' => $isTracking, 'favorited' => $isFavorited, 'reminded' => $isReminded]) }})"
                                    x-on:library-updated.window="syncLibrary($event.detail)"
                                    x-on:title-favorited.window="syncFavorite($event.detail)"
                                    x-on:title-reminded.window="syncReminder($event.detail)"
                                    x-on:user-actions-failed.window="busy = false"
                                >
                                    <x-circle-button
                                        x-on:click="remind()"
                                        x-bind:disabled="busy"
                                        x-show="tracking"
                                        :style="$isTracking ? null : 'display: none;'"
                                    >
                                        @svg('bell_fill', 'fill-current', ['width' => '44', 'x-show' => 'reminded'] + ($isReminded ? [] : ['style' => 'display: none;']))
                                        @svg('bell', 'fill-current', ['width' => '44', 'x-show' => '!reminded'] + ($isReminded ? ['style' => 'display: none;'] : []))
                                    </x-circle-button>

                                    <x-circle-button
                                        x-on:click="favorite()"
                                        x-bind:disabled="busy"
                                        x-show="tracking"
                                        :style="$isTracking ? null : 'display: none;'"
                                    >
                                        @svg('heart_fill', 'fill-current', ['width' => '44', 'x-show' => 'favorited'] + ($isFavorited ? [] : ['style' => 'display: none;']))
                                        @svg('heart', 'fill-current', ['width' => '44', 'x-show' => '!favorited'] + ($isFavorited ? ['style' => 'display: none;'] : []))
                                    </x-circle-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <section id="badges" class="flex flex-row flex-nowrap whitespace-nowrap justify-between items-center text-center pt-4 pb-5 pl-4 pr-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                    <div id="ratingBadge" class="flex-grow pr-12">
                        <a class="flex flex-col items-center no-external-icon" href="#ratingsAndReviews">
                            <p class="font-bold text-tint">
                                {{ number_format($anime->mediaStat->rating_average, 1) }}
                            </p>

                            <x-star-rating-display :rating="$anime->mediaStat->rating_average" star-size="sm" />

                            <p class="text-sm text-secondary">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x reviews', (int) $anime?->mediaStat?->rating_count, ['x' => number_shorten((int) $anime?->mediaStat?->rating_count, 0, true)]) }}</p>
                        </a>
                    </div>

                    @if ($anime->started_at && $anime->air_season)
                        <div id="seasonBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center" href="{{ route('anime.seasons.year.season', [$anime->started_at->year, $anime->air_season->key]) }}" wire:navigate>
                                <p class="font-bold">{{ $anime->air_season->description }}</p>
                                <p class="text-tint">
                                    {{ $anime->air_season->symbol() }}
                                </p>
                                <p class="text-sm text-secondary">{{ $anime->started_at->year }}</p>
                            </a>
                        </div>
                    @elseif ($anime->air_season)
                        <div id="seasonBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center no-external-icon" href="#aired">
                                <p class="font-bold">{{ $anime->air_season->description }}</p>
                                <p class="text-tint">
                                    {{ $anime->air_season->symbol() }}
                                </p>
                                <p class="text-sm text-secondary">{{ __('Season') }}</p>
                            </a>
                        </div>
                    @endif

                    <div id="rankingBadge" class="flex-grow px-12 border-l border-primary">
                        <a class="flex flex-col items-center" href="{{ route('charts.top', App\Enums\ChartKind::Anime) }}" wire:navigate>
                            <p class="font-bold">{{ trans_choice('{0} -|[1,*] #:x', $anime?->mediaStat?->rank_total ?? 0, ['x' => $anime?->mediaStat?->rank_total ?? 0]) }}</p>
                            <p class="text-tint">
                                @svg('chart_bar_fill', 'fill-current', ['width' => '20'])
                            </p>
                            <p class="text-sm text-secondary">{{ __('Chart') }}</p>
                        </a>
                    </div>

                    <div id="tvRatingBadge" class="flex-grow px-12 border-l border-primary">
                        <a class="flex flex-col items-center" href="{{ route('anime.parentalguide', $anime) }}" wire:navigate>
                            <p class="font-bold">{{ $anime->tvRating->name }}</p>
                            <p class="text-tint">
                                @svg('tv_fill', 'fill-current', ['width' => '20'])
                            </p>
                            <p class="text-sm text-secondary">{{ __('Rated') }}</p>
                        </a>
                    </div>

                    @if (!empty($studio))
                        <div id="studioBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center" href="{{ route('studios.details', $studio) }}" wire:navigate>
                                <p class="font-bold">{{ $studio->name }}</p>
                                <p class="text-tint">
                                    @svg('building_2_fill', 'fill-current', ['width' => '20'])
                                </p>
                                <p class="text-sm text-secondary">{{ __('Studio') }}</p>
                            </a>
                        </div>
                    @endif

                    @if (!empty($anime->countryOfOrigin))
                        <div id="countryBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center no-external-icon" href="#country">
                                <p class="font-bold">{{ strtoupper($anime->countryOfOrigin->code) }}</p>
                                <p class="text-tint">
                                    @svg('globe', 'fill-current', ['width' => '20'])
                                </p>
                                <p class="text-sm text-secondary">{{ __('Country') }}</p>
                            </a>
                        </div>
                    @endif

                    @if ($primaryLanguage = $anime->primaryLanguage())
                        <div id="languageBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center no-external-icon" href="#languages">
                                <p class="font-bold">{{ strtoupper($primaryLanguage->code) }}</p>
                                <p class="text-tint">
                                    @svg('character_bubble_fill', 'fill-current', ['width' => '20'])
                                </p>
                                <p class="text-sm text-secondary">{{ trans_choice('{0} Language|{1} +:x More Language|[2,*] +:x More Languages', $anime->supported_languages_count - 1, ['x' => $anime->supported_languages_count - 1]) }}</p>
                            </a>
                        </div>
                    @endif
                </section>

                @if ($hasUpcomingBroadcast)
                    <section id="countdown" class="pb-8 xl:safe-area-inset">
                        <x-countdown-bar
                            class="ml-4 mr-4"
                            :caption="__('Next Episode')"
                            :schedule="$anime->broadcast_string"
                            :timestamp="$anime->broadcast_date->timestamp"
                            anchor="#broadcast"
                        />
                    </section>
                @endif

                @if (!empty($anime->synopsis))
                    <section class="pb-8 xl:safe-area-inset">
                        <x-section-nav class="flex flex-nowrap justify-between mb-5 pt-4">
                            <x-slot:title>
                                {{ __('Synopsis') }}
                            </x-slot:title>
                        </x-section-nav>

                        <x-truncated-text class="max-w-7xl ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($anime->synopsis)) !!}
                            </x-slot:text>
                        </x-truncated-text>
                    </section>
                @endif

                <section id="ratingsAndReviews" class="pb-8 xl:safe-area-inset">
                    <x-section-nav class="pt-4">
                        <x-slot:title>
                            {{ __('Ratings & Reviews') }}
                        </x-slot:title>

                        <x-slot:action>
                            <x-section-nav-link href="{{ route('anime.reviews', $anime) }}">{{ __('See All') }}</x-section-nav-link>
                        </x-slot:action>
                    </x-section-nav>

                    <div class="flex flex-row flex-wrap justify-between gap-4 pl-4 pr-4">
                        <div class="flex flex-col justify-end text-center">
                            <p class="font-bold text-6xl">{{ number_format($anime->mediaStat->rating_average, 1) }}</p>
                            <p class="font-bold text-sm text-secondary">{{ __('out of') }} 5</p>
                        </div>

                        <div class="flex flex-col justify-end items-center text-center">
                            @svg('star_fill', 'fill-current', ['width' => 32])
                            <p class="font-bold text-2xl">{{ number_format($anime->mediaStat->positivePercentage) }}%</p>
                            <p class="text-sm text-secondary">{{ $anime->mediaStat->sentiment }}</p>
                        </div>

                        <div class="flex flex-col justify-end items-center text-center">
                            @svg('heart_fill', 'fill-current', ['width' => 32])
                            <p class="font-bold text-2xl">{{ number_format($anime->mediaStat->favorite_share * 100) }}%</p>
                            <p class="text-sm text-secondary">{{ $anime->mediaStat->favorite_sentiment }}</p>
                        </div>

                        <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                            <x-star-rating-bar :media-stat="$anime->mediaStat" />

                            <p class="text-sm text-secondary">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $anime?->mediaStat?->rating_count ?? 0, ['x' => number_format($anime?->mediaStat?->rating_count ?? 0)]) }}</p>
                        </div>
                    </div>
                </section>

                <section id="writeAReview" class="pb-8">
                    <div class="xl:safe-area-inset">
                        <x-hr class="ml-4 mr-4 pb-5" />
                    </div>

                    <div class="flex flex-row flex-wrap gap-4 pl-4 pr-4 xl:safe-area-inset-scroll">
                        <div class="flex justify-between items-center">
                            <p class="">{{ __('Click to Rate:') }}</p>

                            <livewire:components.rating-input :model-id="$anime->id" :model-type="$anime->getMorphClass()" :rating="$userRating?->first()?->rating" :star-size="'md'" :review-box-id="$reviewBoxID" />
                        </div>

                        <div class="flex justify-between">
                            <x-simple-button class="flex gap-1" x-on:click="Livewire.dispatch('show-review-box', { id: {{ Js::from($reviewBoxID) }} })">
                                @svg('pencil', 'fill-current', ['width' => 18])
                                {{ __('Write a Review') }}
                            </x-simple-button>
                        </div>

                        <div></div>
                    </div>

                    <div class="mt-5">
                        <livewire:sections.reviews :model="$anime" :review-box-id="$reviewBoxID" />
                    </div>
                </section>

                <section class="pb-8 xl:safe-area-inset">
                    <x-section-nav class="pt-4">
                        <x-slot:title>
                            {{ __('Information') }}
                        </x-slot:title>
                    </x-section-nav>

                    <div class="grid gap-4 pl-4 pr-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                        <x-information-list id="type" title="{{ __('Type') }}" icon="{{ asset('images/symbols/tv_and_mediabox.svg') }}">
                            <x-slot:information>
                                {{ $anime->mediaType->name }}
                            </x-slot:information>

                            <x-slot:footer>
                                <p class="text-sm">{{ $anime->mediaType->description }}</p>
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
                                {{ $anime->genres?->pluck('name')->join(', ', ' and ') ?: '-' }}
                            </x-slot:information>
                        </x-information-list>

                        <x-information-list id="themes" title="{{ __('Themes') }}" icon="{{ asset('images/symbols/crown.svg') }}">
                            <x-slot:information>
                                {{ $anime->themes?->pluck('name')->join(', ', ' and ') ?: '-' }}
                            </x-slot:information>
                        </x-information-list>

                        @if (in_array($anime->mediaType->name, ['Unknown', 'TV', 'ONA']))
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
                                <p class="text-sm">{{ __('With a total of :count.', ['count' => $anime->duration_total_string]) }}</p>
                            </x-slot:footer>
                        </x-information-list>

                        <x-information-list id="broadcast" title="{{ __('Broadcast') }}" icon="{{ asset('images/symbols/calendar_badge_clock.svg') }}">
                            <x-slot:information>
                                {{ $anime->broadcast_string }}
                            </x-slot:information>

                            @if ($anime->status_id === 4)
                                <x-slot:footer>
                                    <p class="text-sm">{{ __('The broadcasting of this series has ended.') }}</p>
                                </x-slot:footer>
                            @elseif (empty($anime->broadcast_string))
                                <x-slot:footer>
                                    <p class="text-sm">{{ __('No broadcast data available at the moment.') }}</p>
                                </x-slot:footer>
                            @elseif (!empty($anime->broadcast_date))
                                <x-schedule-week :release-date="$anime->broadcast_date" />

                                <x-slot:footer>
                                    <x-local-time :timestamp="$anime->broadcast_date->timestamp" />
                                </x-slot:footer>
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
                                {{ $anime->tvRating->name }}
                            </x-slot:information>

                            <x-slot:footer>
                                <p class="text-sm">{{ $anime->tvRating->description }}.</p>
                            </x-slot:footer>
                        </x-information-list>

                        <x-information-list id="country" title="{{ __('Country') }}" icon="{{ asset('images/symbols/globe.svg') }}">
                            <x-slot:information>
                                {{ $anime->countryOfOrigin?->name ?: '-' }}
                            </x-slot:information>
                        </x-information-list>

                        <x-languages-card :model="$anime" :groups="[
                            __('Audio') => $anime->audioLanguages,
                            __('Subtitles') => $anime->subtitleLanguages,
                        ]" />

{{--                        <x-information-list title="{{ __('Studio') }}" icon="{{ asset('images/symbols/building_2.svg') }}">--}}
{{--                            <x:information>--}}
{{--                                {{ $anime->studios()->first()->name ?? '-' }}--}}
{{--                            </x:information>--}}
{{--                        </x-information-list>--}}

{{--                        <x-information-list title="{{ __('Network') }}" icon="{{ asset('images/symbols/dot_radiowaves_left_and_right.svg') }}">--}}
{{--                            <x:information>--}}
{{--                                {{ $anime->studios()->first()->name ?? '-' }}--}}
{{--                            </x:information>--}}
{{--                        </x-information-list>--}}
                    </div>
                </section>

                <x-anime-seasons-section :anime="$anime" />

                <x-cast-section :kind="\App\Enums\UserLibraryKind::Anime" :model="$anime" />

                <x-staff-section :kind="\App\Enums\UserLibraryKind::Anime" :model="$anime" />

                <x-songs-section :kind="\App\Enums\UserLibraryKind::Anime" :model="$anime" />

                <x-studios-section :kind="\App\Enums\UserLibraryKind::Anime" :model="$anime" />

                <div class="bg-tinted">
                    @if (!empty($studio))
                        <x-more-by-studio-section :kind="\App\Enums\UserLibraryKind::Anime" :model="$anime" :studio="$studio" />
                    @endif

                    <x-relations-section :kind="\App\Enums\UserLibraryKind::Anime" :related-kind="\App\Enums\UserLibraryKind::Anime" :model="$anime" />

                    <x-relations-section :kind="\App\Enums\UserLibraryKind::Anime" :related-kind="\App\Enums\UserLibraryKind::Manga" :model="$anime" />

                    <x-relations-section :kind="\App\Enums\UserLibraryKind::Anime" :related-kind="\App\Enums\UserLibraryKind::Game" :model="$anime" />

                    @if (!empty($anime->copyright))
                        <section class="border-t border-primary xl:safe-area-inset">
                            <div class="pt-4 pr-4 pb-4 pl-4">
                                <p class="text-sm text-secondary">{!! nl2br(e($anime->copyright)) !!}</p>
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </div>

        <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$anime->id" :model-type="$anime->getMorphClass()" :user-rating="$userRating->first()" />

        @if (!empty($anime->video_url))
            <x-dialog-modal id="trailer-video" maxWidth="md">
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
                        src="https://www.youtube-nocookie.com/embed/{{ str($anime->video_url)->after('?v=') }}?autoplay=0&iv_load_policy=3&disablekb=1&color=red&rel=0&cc_load_policy=0&start=0&end=0&origin={{ config('app.url') }}&modestbranding=1&playsinline=1&loop=1&playlist={{ str($anime->video_url)->after('?v=') }}"
                    >
                    </iframe>
                </x-slot:content>

                <x-slot:footer>
                    <x-button x-on:click="$dispatch('close')">{{ __('Close') }}</x-button>
                </x-slot:footer>
            </x-dialog-modal>
        @endif

        @if ($addToLibraryStatus !== null)
            <x-dialog-modal id="add-to-library" maxWidth="md" :open="true">
                <x-slot:title>
                    {{ __('Confirm library addition') }}
                </x-slot:title>

                <x-slot:content>
                    <div class="pt-4 pb-4 pl-4 pr-4">
                        <p>{{ __('Are you sure you want to add ":title" to your :libraryStatus list?', ['title' => $anime->title, 'libraryStatus' => request()->query('add_to_library')]) }}</p>
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <div
                        class="flex justify-end gap-2"
                        x-data="addToLibraryPrompt({{ Js::from(['type' => $anime->getMorphClass(), 'id' => $anime->id, 'status' => $addToLibraryStatus->value]) }})"
                        x-on:library-updated.window="sync($event.detail)"
                        x-on:user-actions-failed.window="busy = false"
                    >
                        <x-outlined-button x-on:click="dismiss()" x-bind:disabled="busy">
                            {{ __('Cancel') }}
                        </x-outlined-button>

                        <x-button x-on:click="add()" x-bind:disabled="busy">
                            {{ __('Add') }}
                        </x-button>
                    </div>
                </x-slot:footer>
            </x-dialog-modal>
        @endif
    </main>
</x-base-layout>
