<main>
    <x-slot:title>
        {!! __('Ratings & Reviews') !!} | {!! $manga->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all :x reviews & ratings only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $manga->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Ratings & Reviews') }} | {{ $manga->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all :x reviews & ratings on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $manga->title]) }}" />
        <meta property="og:image" content="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/manga_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $manga->duration }}" />
        <meta property="video:release_date" content="{{ $manga->started_at?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('manga.reviews', $manga) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        manga/{{ $manga->id }}/reviews
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Ratings & Reviews', ['x' => $manga->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
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

                    <livewire:manga.star-rating :manga="$manga" :rating="$this->userRating?->rating" :star-size="'md'" />
                </div>

                <div class="flex justify-between">
                    <x-simple-button class="flex gap-1" wire:click="showReviewBox">
                        @svg('pencil', 'fill-current', ['width' => 18])
                        {{ __('Write a Review') }}
                    </x-simple-button>
                </div>

                <div></div>
            </div>
        </section>

        <x-rows.review-lockup :reviews="$this->mediaRatings" :is-row="false" />

        <section class="mt-4">
            {{ $this->mediaRatings->links() }}
        </section>

        <x-dialog-modal maxWidth="md" model="showPopup">
            @if ($showReviewBox)
                <x-slot:title>
                    {{ __('Write a Review') }}
                </x-slot:title>

                <x-slot:content>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center">
                            <p class="">{{ __('Click to Rate:') }}</p>

                            <livewire:manga.star-rating :manga="$manga" :rating="$this->userRating?->rating" :star-size="'md'" />
                        </div>

                        <x-textarea class="block w-full h-48 mt-1 resize-none" placeholder="{{ __('What’s on your mind?') }}" wire:model.defer="reviewText"></x-textarea>
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <x-button wire:click="submitReview">{{ __('Submit') }}</x-button>
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
</main>