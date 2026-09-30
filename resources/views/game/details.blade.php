<x-base-layout>
    <x-slot:title>
        {!! __(':x — Game, Cast & Reviews', ['x' => $game->title]) !!}
    </x-slot:title>

    <x-slot:description>
        {{ $metaDescription }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x — Game, Cast & Reviews', ['x' => $game->title]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $game->synopsis ?? __('A community for anime fans with an extensive library of anime, manga, music, games, movies, specials, OVA, and ONA. Only on :x, the largest, free online anime, manga, game & music database in the world. Track, share and discover anime with friends.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:video" content="{{ $game->video_url ?? '' }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $game->duration }}" />
        <meta property="video:release_date" content="{{ $game->published_at?->toIso8601String() }}" />
        @foreach ($game->tags() as $tag)
            <meta property="video:tag" content="{{ $tag->name }}" />
        @endforeach
        <meta property="twitter:title" content="{{ __(':x — Game, Cast & Reviews', ['x' => $game->title]) }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $game->synopsis }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $game->synopsis }}" />
        @if ($game->is_nsfw)
            <meta name="rating" content="adult" />
        @endif
        <link rel="canonical" href="{{ route('games.details', $game) }}">
        <x-misc.schema :data="$schema" />
    </x-slot:meta>

    <x-slot:appArgument>
        games/{{ $game->id }}
    </x-slot:appArgument>

    <main x-data>
        <div class="pb-6">
            <div class="relative overflow-hidden max-h-[80vh]">
                <div class="relative flex flex-nowrap md:h-full xl:safe-area-inset">
                    <x-picture
                        class="w-full aspect-video max-h-[80vh] overflow-hidden"
                        style="background-color: {{ ($game->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $game->getFirstMedia(\App\Enums\MediaCollection::Poster))?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                    >
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $bannerUrl }}" alt="{{ $game->title }} Banner" title="{{ $game->title }}">
                    </x-picture>

                    <x-background-extension :source="$bannerUrl" />

                    @if (!empty($game->video_url))
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
                            border-roundness="rounded-3xl"
                            class="w-28 h-28 mr-2 rounded-3xl overflow-hidden"
                            style="min-width: 7rem; max-height: 10rem; background-color: {{ $game->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                        >
                            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/game_poster.webp') }}" alt="{{ $game->title }} Poster" title="{{ $game->title }}">
                        </x-picture>

                        <div class="relative flex flex-col gap-2 justify-between w-full">
                            <div>
                                <p class="font-semibold text-lg leading-tight break-all">{{ $game->title }}</p>

                                <p class="text-sm leading-tight">{{ $game->information_summary }}</p>

                                <div class="flex w-full justify-between mt-2 gap-1 sm:gap-4">
                                    <p class="flex-grow pt-1 pr-1 pb-1 pl-1 text-white text-center text-xs font-semibold whitespace-nowrap rounded-md" style="background-color: {{ $game->status->color }};">{{ $game->status->name }}</p>

                                    <p class="flex-grow pt-1 pr-1 pb-1 pl-1 bg-white text-black text-center text-xs font-semibold whitespace-nowrap rounded-md"> {{ trans_choice('{0} Rank -|[1,*] Rank #:x', $game?->mediaStat?->rank_total ?? 0, ['x' => $game?->mediaStat?->rank_total ?? 0]) }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1 justify-between">
                                <div class="flex gap-2">
                                    <x-library-button :model="$game" />

                                    <x-nova-link :href="route('games.edit', $game)">
                                        @svg('pencil', 'fill-current', ['width' => '44'])
                                    </x-nova-link>
                                </div>

                                <div
                                    class="flex gap-2"
                                    x-data="titleActions({{ Js::from(['type' => $game->getMorphClass(), 'id' => $game->id, 'tracking' => $isTracking, 'favorited' => $isFavorited, 'reminded' => false]) }})"
                                    x-on:library-updated.window="syncLibrary($event.detail)"
                                    x-on:title-favorited.window="syncFavorite($event.detail)"
{{--                                    x-on:title-reminded.window="syncReminder($event.detail)"--}}
                                    x-on:user-actions-failed.window="busy = false"
                                >
{{--                                    <x-circle-button--}}
{{--                                        x-on:click="remind()"--}}
{{--                                        x-bind:disabled="busy"--}}
{{--                                        x-show="tracking"--}}
{{--                                        :style="$isTracking ? null : 'display: none;'"--}}
{{--                                    >--}}
{{--                                        @svg('bell_fill', 'fill-current', ['width' => '44', 'x-show' => 'reminded'] + ($isReminded ? [] : ['style' => 'display: none;']))--}}
{{--                                        @svg('bell', 'fill-current', ['width' => '44', 'x-show' => '!reminded'] + ($isReminded ? ['style' => 'display: none;'] : []))--}}
{{--                                    </x-circle-button>--}}

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
                                {{ number_format($game->mediaStat->rating_average, 1) }}
                            </p>

                            <x-star-rating-display :rating="$game->mediaStat->rating_average" star-size="sm" />

                            <p class="text-sm text-secondary">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x reviews', (int) $game?->mediaStat?->rating_count, ['x' => number_shorten((int) $game?->mediaStat?->rating_count, 0, true)]) }}</p>
                        </a>
                    </div>

                    @if ($game->published_at && $game->publication_season)
                        <div id="seasonBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center" href="{{ route('games.seasons.year.season', [$game->published_at->year, $game->publication_season->key]) }}" wire:navigate>
                                <p class="font-bold">{{ $game->publication_season->description }}</p>
                                <p class="text-tint">
                                    {{ $game->publication_season->symbol() }}
                                </p>
                                <p class="text-sm text-secondary">{{ $game->published_at->year }}</p>
                            </a>
                        </div>
                    @elseif ($game->publication_season)
                        <div id="seasonBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center no-external-icon" href="#published">
                                <p class="font-bold">{{ $game->publication_season->description }}</p>
                                <p class="text-tint">
                                    {{ $game->publication_season->symbol() }}
                                </p>
                                <p class="text-sm text-secondary">{{ __('Season') }}</p>
                            </a>
                        </div>
                    @endif

                    <div id="rankingBadge" class="flex-grow px-12 border-l border-primary">
                        <a class="flex flex-col items-center" href="{{ route('charts.top', App\Enums\ChartKind::Games) }}" wire:navigate>
                            <p class="font-bold">{{ trans_choice('{0} -|[1,*] #:x', $game?->mediaStat?->rank_total ?? 0, ['x' => $game?->mediaStat?->rank_total ?? 0]) }}</p>
                            <p class="text-tint">
                                @svg('chart_bar_fill', 'fill-current', ['width' => '20'])
                            </p>
                            <p class="text-sm text-secondary">{{ __('Chart') }}</p>
                        </a>
                    </div>

                    <div id="tvRatingBadge" class="flex-grow px-12 border-l border-primary">
                        <a class="flex flex-col items-center" href="{{ route('games.parentalguide', $game) }}" wire:navigate>
                            <p class="font-bold">{{ $game->tvRating->name }}</p>
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

                    @if (!empty($game->countryOfOrigin))
                        <div id="countryBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center no-external-icon" href="#country">
                                <p class="font-bold">{{ strtoupper($game->countryOfOrigin->code) }}</p>
                                <p class="text-tint">
                                    @svg('globe', 'fill-current', ['width' => '20'])
                                </p>
                                <p class="text-sm text-secondary">{{ __('Country') }}</p>
                            </a>
                        </div>
                    @endif

                    @if ($primaryLanguage = $game->primaryLanguage())
                        <div id="languageBadge" class="flex-grow px-12 border-l border-primary">
                            <a class="flex flex-col items-center no-external-icon" href="#languages">
                                <p class="font-bold">{{ strtoupper($primaryLanguage->code) }}</p>
                                <p class="text-tint">
                                    @svg('character_bubble_fill', 'fill-current', ['width' => '20'])
                                </p>
                                <p class="text-sm text-secondary">{{ trans_choice('{0} Language|{1} +:x More Language|[2,*] +:x More Languages', $game->supported_languages_count - 1, ['x' => $game->supported_languages_count - 1]) }}</p>
                            </a>
                        </div>
                    @endif
                </section>

                @if (!empty($game->started_at) && $game->started_at->isFuture())
                    <section id="countdown" class="pb-8 xl:safe-area-inset">
                        <x-countdown-bar
                            class="ml-4 mr-4"
                            :caption="__('Release')"
                            :schedule="$game->started_at->toFormattedDateString()"
                            :timestamp="$game->started_at->timestamp"
                            anchor="#publication"
                        />
                    </section>
                @endif

                @if (!empty($game->synopsis))
                    <section class="pb-8 xl:safe-area-inset">
                        <x-section-nav class="flex flex-nowrap justify-between mb-5 pt-4">
                            <x-slot:title>
                                {{ __('Synopsis') }}
                            </x-slot:title>
                        </x-section-nav>

                        <x-truncated-text class="max-w-7xl ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($game->synopsis)) !!}
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
                            <x-section-nav-link href="{{ route('games.reviews', $game) }}">{{ __('See All') }}</x-section-nav-link>
                        </x-slot:action>
                    </x-section-nav>

                    <div class="flex flex-row flex-wrap justify-between gap-4 pl-4 pr-4">
                        <div class="flex flex-col justify-end text-center">
                            <p class="font-bold text-6xl">{{ number_format($game->mediaStat->rating_average, 1) }}</p>
                            <p class="font-bold text-sm text-secondary">{{ __('out of') }} 5</p>
                        </div>

                        <div class="flex flex-col justify-end items-center text-center">
                            @svg('star_fill', 'fill-current', ['width' => 32])
                            <p class="font-bold text-2xl">{{ number_format($game->mediaStat->positivePercentage) }}%</p>
                            <p class="text-sm text-secondary">{{ $game->mediaStat->sentiment }}</p>
                        </div>

                        <div class="flex flex-col justify-end items-center text-center">
                            @svg('heart_fill', 'fill-current', ['width' => 32])
                            <p class="font-bold text-2xl">{{ number_format($game->mediaStat->favorite_share * 100) }}%</p>
                            <p class="text-sm text-secondary">{{ $game->mediaStat->favorite_sentiment }}</p>
                        </div>

                        <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                            <x-star-rating-bar :media-stat="$game->mediaStat" />

                            <p class="text-sm text-secondary">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $game?->mediaStat?->rating_count ?? 0, ['x' => number_format($game?->mediaStat?->rating_count ?? 0)]) }}</p>
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

                            <livewire:components.rating-input :model-id="$game->id" :model-type="$game->getMorphClass()" :rating="$userRating->first()?->rating" :star-size="'md'" :review-box-id="$reviewBoxID" />
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
                        <x-sections.reviews :model="$game" :review-box-id="$reviewBoxID" />
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
                                {{ $game->mediaType->name }}
                            </x-slot:information>

                            <x-slot:footer>
                                <p class="text-sm">{{ $game->mediaType->description }}</p>
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

                        @if (in_array($game->mediaType->name, ['Unknown', 'TV', 'ONA']))
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
                                <p class="text-sm">{{ __('With a total of :count.', ['count' => $game->duration_total_string]) }}</p>
                            </x-slot:footer>
                        </x-information-list>

                        <x-information-list id="publication" title="{{ __('Publication') }}" icon="{{ asset('images/symbols/calendar_badge_clock.svg') }}">
                            <x-slot:information>
                                {{ $game->publication_string }}
                            </x-slot:information>

                            @if ($game->status_id === 15)
                                <x-slot:footer>
                                    <p class="text-sm">{{ __('The publication of this series has ended.') }}</p>
                                </x-slot:footer>
                            @elseif (empty($game->publication_date))
                                <x-slot:footer>
                                    <p class="text-sm">{{ __('No publication data available at the moment.') }}</p>
                                </x-slot:footer>
                            @else
                                <x-schedule-week :release-date="$game->publication_date" />

                                <x-slot:footer>
                                    <x-local-time :timestamp="$game->publication_date->timestamp" />
                                </x-slot:footer>
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
                                {{ $game->tvRating->name }}
                            </x-slot:information>

                            <x-slot:footer>
                                <p class="text-sm">{{ $game->tvRating->description }}.</p>
                            </x-slot:footer>
                        </x-information-list>

                        <x-information-list id="country" title="{{ __('Country') }}" icon="{{ asset('images/symbols/globe.svg') }}">
                            <x-slot:information>
                                {{ $game->countryOfOrigin?->name ?: '-' }}
                            </x-slot:information>
                        </x-information-list>

                        <x-languages-card :model="$game" :groups="[
                            __('Audio') => $game->audioLanguages,
                            __('Subtitles') => $game->subtitleLanguages,
                            __('Interface') => $game->interfaceLanguages,
                        ]" />
                    </div>
                </section>

                <x-cast-section :kind="\App\Enums\UserLibraryKind::Game" :model="$game" />

                <x-staff-section :kind="\App\Enums\UserLibraryKind::Game" :model="$game" />

                <x-songs-section :kind="\App\Enums\UserLibraryKind::Game" :model="$game" />

                <x-studios-section :kind="\App\Enums\UserLibraryKind::Game" :model="$game" />

                <div class="bg-tinted">
                    @if (!empty($studio))
                        <x-more-by-studio-section :kind="\App\Enums\UserLibraryKind::Game" :model="$game" :studio="$studio" />
                    @endif

                    <x-relations-section :kind="\App\Enums\UserLibraryKind::Game" :related-kind="\App\Enums\UserLibraryKind::Game" :model="$game" />

                    <x-relations-section :kind="\App\Enums\UserLibraryKind::Game" :related-kind="\App\Enums\UserLibraryKind::Anime" :model="$game" />

                    <x-relations-section :kind="\App\Enums\UserLibraryKind::Game" :related-kind="\App\Enums\UserLibraryKind::Manga" :model="$game" />

                    @if (!empty($game->copyright))
                        <section class="border-t border-primary xl:safe-area-inset">
                            <div class="pt-4 pr-4 pb-4 pl-4">
                                <p class="text-sm text-secondary">{!! nl2br(e($game->copyright)) !!}</p>
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </div>

        <x-reviews.report-modal />

        <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$game->id" :model-type="$game->getMorphClass()" :user-rating="$userRating?->first()" />

        @if (!empty($game->video_url))
            <x-dialog-modal id="trailer-video" maxWidth="md">
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
                        src="https://www.youtube-nocookie.com/embed/{{ str($game->video_url)->after('?v=') }}?autoplay=0&iv_load_policy=3&disablekb=1&color=red&rel=0&cc_load_policy=0&start=0&end=0&origin={{ config('app.url') }}&modestbranding=1&playsinline=1&loop=1&playlist={{ str($game->video_url)->after('?v=') }}"
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
                        <p>{{ __('Are you sure you want to add ":title" to your :libraryStatus list?', ['title' => $game->title, 'libraryStatus' => request()->query('add_to_library')]) }}</p>
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <div
                        class="flex justify-end gap-2"
                        x-data="addToLibraryPrompt({{ Js::from(['type' => $game->getMorphClass(), 'id' => $game->id, 'status' => $addToLibraryStatus->value]) }})"
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
