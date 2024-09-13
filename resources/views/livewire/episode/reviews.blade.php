<main>
    <x-slot:title>
        {!! __('Ratings & Reviews') !!} | {!! $episode->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all :x reviews & ratings only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $episode->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Ratings & Reviews') }} | {{ $episode->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all :x reviews & ratings on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $episode->title]) }}" />
        <meta property="og:image" content="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/placeholders/episode_banner.webp') }}" />
        <meta property="og:type" content="video.episode" />
        <meta property="video:duration" content="{{ $episode->duration }}" />
        <meta property="video:release_date" content="{{ $episode->started_at?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('episodes.reviews', $episode) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        episodes/{{ $episode->id }}/reviews
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Ratings & Reviews', ['x' => $episode->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <section id="ratingsAndReviews" class="pb-8">
            <div class="flex flex-row flex-wrap justify-between gap-4">
                <div class="flex flex-col justify-end text-center">
                    <p class="font-bold text-6xl">{{ number_format($this->mediaStat->rating_average, 1) }}</p>
                    <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                </div>

                <div class="flex flex-col justify-end items-center text-center">
                    @svg('star_fill', 'fill-current', ['width' => 32])
                    <p class="font-bold text-2xl">{{ number_format($this->mediaStat->highestRatingPercentage) }}%</p>
                    <p class="text-sm text-gray-500">{{ $this->mediaStat->sentiment }}</p>
                </div>

                <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                    <x-star-rating-bar :media-stat="$this->mediaStat" />

                    <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $this->mediaStat->rating_count, ['x' => number_format($this->mediaStat->rating_count)]) }}</p>
                </div>
            </div>
        </section>

        <section id="writeAReview" class="mb-5 pt-5 border-t-2">
            <div class="flex flex-row flex-wrap gap-4">
                <div class="flex justify-between items-center">
                    <p class="">{{ __('Click to Rate:') }}</p>

                    <livewire:components.star-rating :model-id="$episode->id" :model-type="$episode->getMorphClass()" :rating="$this->userRating?->rating" :star-size="'md'" />
                </div>

                <div class="flex justify-between">
                    <x-simple-button class="flex gap-1" wire:click="$dispatch('show-review-box', { 'id': '{{ $this->reviewBoxID }}' })">
                        @svg('pencil', 'fill-current', ['width' => 18])
                        {{ __('Write a Review') }}
                    </x-simple-button>
                </div>

                <div></div>
            </div>
        </section>

        @if ($this->mediaRatings->count())
            <x-rows.review-lockup :reviews="$this->mediaRatings" :is-row="false" />

            <section class="mt-4">
                {{ $this->mediaRatings->links() }}
            </section>
        @elseif (!$readyToLoad)
            <section>
                <div class="flex gap-4 justify-between flex-wrap">
                    @foreach (range(1,25) as $range)
                        <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>

    <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$episode->id" :model-type="$episode->getMorphClass()" :user-rating="$this->userRating" />
</main>
