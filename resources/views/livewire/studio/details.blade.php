<main>
    <x-slot:title>
        {!! $studio->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ $studio->synopsis }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $studio->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $studio->synopsis ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $studio->name }}" />
        <link rel="canonical" href="{{ route('studios.details', $studio) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        studios/{{ $studio->id }}
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6" wire:init="loadPage">
        <section class="pt-5 pb-8">
            <div class="relative pb-2">
                <div class="flex flex-col flex-wrap text-center items-center">
                    <picture
                        class="relative aspect-square rounded-full overflow-hidden"
                        style="height: 128px; background-color: {{ $studio->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? '#000000' }};"
                    >
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/studio_profile.webp') }}" alt="{{ $studio->name }} Profile" title="{{ $studio->name }}">

                        <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>
                    </picture>

                    <div class="flex gap-2">
                        <p class="text-3xl font-bold">{{ $studio->name }}</p>

                        <x-nova-link :resource="\App\Nova\Studio::class" :model="$studio">
                            @svg('pencil', 'fill-current', ['width' => '44'])
                        </x-nova-link>
                    </div>
                    @if (!empty($studio->founded))
                        <p class="text-lg">{{ __('Founded on :x', ['x' => $studio->founded->toFormattedDateString()]) }}</p>
                    @endif
                </div>
            </div>
        </section>

        @if ($studio->about)
            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot:title>
                        {{ __('About') }}
                    </x-slot:title>
                </x-section-nav>

                <x-truncated-text>
                    <x-slot:text>
                        {!! nl2br(e($studio->about)) !!}
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
                    <x-section-nav-link class="whitespace-nowrap" href="{{ route('studios.reviews', $studio) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex flex-row flex-wrap justify-between gap-4">
                <div class="flex flex-col justify-end text-center">
                    <p class="font-bold text-6xl">{{ number_format($studio->mediaStat->rating_average, 1) }}</p>
                    <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                </div>

                <div class="flex flex-col justify-end items-center text-center">
                    @svg('star_fill', 'fill-current', ['width' => 32])
                    <p class="font-bold text-2xl">{{ number_format($studio->mediaStat->highestRatingPercentage) }}%</p>
                    <p class="text-sm text-gray-500">{{ $studio->mediaStat->sentiment }}</p>
                </div>

                <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                    <x-star-rating-bar :media-stat="$studio->mediaStat" />

                    <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $studio->mediaStat->rating_count, ['x' => number_format($studio->mediaStat->rating_count)]) }}</p>
                </div>
            </div>
        </section>

        <section id="writeAReview" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
            <div class="flex flex-row flex-wrap gap-4">
                <div class="flex justify-between items-center">
                    <p class="">{{ __('Click to Rate:') }}</p>

                    <livewire:components.star-rating :model-id="$studio->id" :model-type="$studio->getMorphClass()" :rating="$userRating->first()?->rating" :star-size="'md'" />
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
                <livewire:sections.reviews :model="$studio" />
            </div>
        </section>

        <section class="pt-5 pb-8 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Information') }}
                </x-slot:title>
            </x-section-nav>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                <x-information-list id="founded" title="{{ __('Founded') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                    <x-slot:information>
                        {{ $studio->founded?->toFormattedDateString() ?? '-' }}
                    </x-slot:information>

                    <x-slot:footer>
                        @if (!empty($studio->founded))
                            {{ __('The studio was founded :x years ago.', ['x' => $studio->founded?->age]) }}
                        @endif
                    </x-slot:footer>
                </x-information-list>

                <x-information-list id="headquarters" title="{{ __('Headquarters') }}" icon="{{ asset('images/symbols/building_2.svg') }}">
                    <x-slot:information>
                        {{ $studio->address ?? '-' }}
                    </x-slot:information>
                </x-information-list>

                <x-information-list id="Website" title="{{ __('Website') }}" icon="{{ asset('images/symbols/safari.svg') }}">
                    <x-slot:information>
                        @if (!empty($studio->website_urls))
                            <ul class="list-disc">
                                @foreach ($studio->website_urls as $website_url)
                                    <li>
                                        <x-link href="{{ $website_url }}" target="_blank">
                                            {{ str_ireplace('www.', '', parse_url($website_url, PHP_URL_HOST)) ?? $website_url }}
                                        </x-link>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            -
                        @endif
                    </x-slot:information>
                </x-information-list>
            </div>
        </section>

        @if ($readyToLoad)
            <livewire:components.studio.media-section :studio="$studio" :type="\App\Models\Anime::class" />

            <livewire:components.studio.media-section :studio="$studio" :type="\App\Models\Manga::class" />

            <livewire:components.studio.media-section :studio="$studio" :type="\App\Models\Game::class" />
        @else
            <x-skeletons.small-lockup />
            <x-skeletons.small-lockup />
            <x-skeletons.small-lockup />
        @endif
    </div>

    <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$studio->id" :model-type="$studio->getMorphClass()" :user-rating="$userRating?->first()" />
</main>
