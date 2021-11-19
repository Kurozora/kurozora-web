<main>
    <x-slot name="title">
        {{ __('Episode :x', ['x' => $episode->number_total]) }} | {!! $episode->title !!}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Episode :x', ['x' => $episode->number_total]) }} | {{ $episode->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $episode->synopsis }}" />
        <meta property="og:image" content="{{ $episode->banner_image_url ?? asset('images/static/placeholders/episode_banner.webp') }}" />
        <meta property="og:type" content="video.episode" />
        <meta property="video:duration" content="{{ $episode->duration }}" />
        <meta property="video:release_date" content="{{ $episode->first_aired }}" />
        <meta property="video:series" content="{{ $anime->title }}" />
    </x-slot>

    <x-slot name="appArgument">
        episodes/{{ $episode->id }}
    </x-slot>

    <div class="grid grid-rows-[repeat(2,minmax(0,min-content))] h-full lg:grid-rows-none lg:grid-cols-2 2xl:grid-cols-3 lg:mb-0">
        <div class="relative">
            <div class="flex flex-no-wrap md:relative md:h-full">
                <picture class="relative w-full overflow-hidden">
                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $episode->banner_image_url ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $episode->title }} Banner" title="{{ $episode->title }}" style="aspect-ratio: 16/9;">
                </picture>
            </div>

            <div class="md:absolute md:bottom-0 md:left-0 md:right-0 lg:px-4">
                <div class="flex flex-no-wrap pt-5 pb-8 px-4 md:mx-auto md:mb-8 md:p-6 md:max-w-lg md:bg-white md:bg-opacity-50 md:backdrop-filter md:backdrop-blur md:rounded-lg">
                    <picture class="relative min-w-[100px] max-w-[100px] min-h-[150px] max-h-[150px] mr-2 rounded-lg overflow-hidden">
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $season->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $season->title }} Poster" title="{{ $season->title }}">
                        <div class="absolute top-0 left-0 h-full w-full ring-1 ring-gray-100 ring-opacity-25 ring-inset rounded-lg"></div>
                    </picture>

                    <div class="flex flex-col gap-2 justify-between w-3/4">
                        <div>
                            <p class="font-semibold text-lg leading-tight break-all">{{ $episode->title }}</p>
                        </div>

                        <div class="flex flex-wrap gap-1 justify-between h-10">
                            <livewire:episode.watch-button :episode="$episode" wire:key="{{ md5($episode->id) }}" />
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
                            {{ number_format($episode->rating_average, 1) }}
                            <livewire:anime.star-rating :rating="$episode->rating_average" :star-size="'sm'" :disabled="true" />
                        </p>
                        <p class="text-sm text-gray-500">{{ __('Not enough ratings') }}</p>
                    </a>
                </div>

                <div id="seasonBadge" class="flex-grow px-12 border-l-2">
                    <a href="{{ route('anime.seasons', $anime) }}">
                        <p class="font-bold">#{{ $season->number }}</p>
                        <p class="text-sm text-gray-500">{{ __('Season') }}</p>
                    </a>
                </div>

                <div id="animeBadge" class="flex-grow px-12 border-l-2">
                    <a href="{{ route('anime.details', $anime) }}">
                        <p class="font-bold line-clamp-1">{{ substr($anime->title, 0, 25) }}</p>
                        <p class="text-sm text-gray-500">{{ __('Anime') }}</p>
                    </a>
                </div>
            </section>

            @if (!empty($episode->synopsis))
                <section class="pt-5 pb-8 px-4 border-t-2">
                    <x-section-nav class="flex flex-no-wrap justify-between mb-5">
                        <x-slot name="title">
                            {{ __('Synopsis') }}
                        </x-slot>
                    </x-section-nav>

                    <x-truncated-text>
                        <x-slot name="text">
                            {!! nl2br($episode->synopsis) !!}
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
                        <p class="font-bold text-6xl">{{ number_format($episode->rating_average, 1) }}</p>
                        <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                    </div>

                    @auth
                        <div class="text-right">
                            <livewire:anime.star-rating :rating="$episode->rating_average" :star-size="'lg'" :disabled="true" />
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
                    <x-information-list id="number" title="{{ __('Number') }}" icon="{{ asset('images/symbols/number.svg') }}">
                        <x-slot name="information">
                            {{ $episode->number_total }}
                        </x-slot>

                        <x-slot name="footer">
                            <p class="text-sm">{{ __('#:x in the current season.', ['x' => $episode->number]) }}</p>
                        </x-slot>
                    </x-information-list>

                    <x-information-list id="duration" title="{{ __('Duration') }}" icon="{{ asset('images/symbols/hourglass.svg') }}">
                        <x-slot name="information">
                            {{ $episode->duration_string ?? '-' }}
                        </x-slot>
                    </x-information-list>

                    <x-information-list id="aired" title="{{ __('Aired') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                        @if (!empty($episode->first_aired))
                            <x-slot name="information">
                                🚀 {{ $episode->first_aired->toFormattedDateString() }}
                            </x-slot>

                            <x-slot name="footer">
                                @if ($episode->first_aired->isFuture())
                                    {{ __('The episode will air on the announced date.') }}
                                @else
                                    {{ __('The episode has finished airing.') }}
                                @endif
                            </x-slot>
                        @else
                            <x-slot name="information">
                                -
                            </x-slot>
                            <x-slot name="footer">
                                {{ __('Airing date is unknown.') }}
                            </x-slot>
                        @endif
                    </x-information-list>
                </div>
            </section>

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
