<x-base-layout>
    <x-slot:title>
        {!! __('Ratings & Reviews') !!} | {!! $name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all :x reviews & ratings only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Ratings & Reviews') }} | {{ $name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all :x reviews & ratings on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $ogImage }}" />
        <meta property="og:type" content="{{ $ogType }}" />
        @switch ($kind)
            @case ('anime')
            @case ('game')
            @case ('episode')
                <meta property="video:duration" content="{{ $reviewable->duration }}" />
                <meta property="video:release_date" content="{{ $reviewable->started_at?->toIso8601String() }}" />
                @break
            @case ('manga')
                <meta property="book:release_date" content="{{ $reviewable->started_at?->toIso8601String() }}" />
                @foreach ($reviewable->tags() as $tag)
                    <meta property="book:tag" content="{{ $tag->name }}" />
                @endforeach
                @break
            @case ('character')
            @case ('person')
            @case ('studio')
                <meta property="og:profile:username" content="{{ $name }}" />
                @break
            @case ('song')
                <meta property="og:url" content="{{ route('embed.songs', $reviewable) }}">
                @break
        @endswitch
        <link rel="canonical" href="{{ $canonicalUrl }}">
    </x-slot:meta>

    <x-slot:appArgument>
        {{ $appArgument }}
    </x-slot:appArgument>

    <main x-data>
        <div class="pb-6">
            <x-back-link
                :url="$backUrl"
                :label="$name"
                :title="__(':x’s Ratings & Reviews', ['x' => $name])"
            />

            <div data-paginated="reviews" data-paginated-refresh-on="review-submitted review-deleted review-elevated">
                <section id="ratingsAndReviews" class="pb-8 xl:safe-area-inset">
                    <div class="flex flex-row flex-wrap justify-between gap-4 pl-4 pr-4">
                        <div class="flex flex-col justify-end text-center">
                            <p class="font-bold text-6xl">{{ number_format($mediaStat?->rating_average ?? 0, 1) }}</p>
                            <p class="font-bold text-sm text-secondary">{{ __('out of') }} 5</p>
                        </div>

                        <div class="flex flex-col justify-end items-center text-center">
                            @svg('star_fill', 'fill-current', ['width' => 32])
                            <p class="font-bold text-2xl">{{ number_format($mediaStat?->positivePercentage ?? 0) }}%</p>
                            <p class="text-sm text-secondary">{{ $mediaStat?->sentiment }}</p>
                        </div>

                        @if ($kind !== 'episode')
                            <div class="flex flex-col justify-end items-center text-center">
                                @svg('heart_fill', 'fill-current', ['width' => 32])
                                <p class="font-bold text-2xl">{{ number_format(($mediaStat?->favorite_share ?? 0) * 100) }}%</p>
                                <p class="text-sm text-secondary">{{ $mediaStat?->favorite_sentiment }}</p>
                            </div>
                        @endif

                        <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                            <x-star-rating-bar :media-stat="$mediaStat" />

                            <p class="text-sm text-secondary">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $mediaStat?->rating_count ?? 0, ['x' => number_format($mediaStat?->rating_count ?? 0)]) }}</p>
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

                            <div wire:ignore>
                                <livewire:components.rating-input :model-id="$reviewable->id" :model-type="$reviewable->getMorphClass()" :rating="$userRating?->rating" :star-size="'md'" :review-box-id="$reviewBoxID" />
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <x-simple-button class="flex gap-1" x-on:click="Livewire.dispatch('show-review-box', { id: {{ Js::from($reviewBoxID) }} })">
                                @svg('pencil', 'fill-current', ['width' => 18])
                                {{ __('Write a Review') }}
                            </x-simple-button>
                        </div>

                        <div></div>
                    </div>
                </section>

                @if ($editorial)
                    <section class="pb-8 xl:safe-area-inset">
                        <div class="pl-4 pr-4">
                            <x-lockups.editorial-lockup :editorial="$editorial" />
                        </div>
                    </section>
                @endif

                @if ($reviews->count())
                    <section class="xl:safe-area-inset">
                        <x-rows.review-lockup :reviews="$reviews" :is-row="false" :review-box-id="$reviewBoxID" />

                        <div class="mt-4 pl-4 pr-4">
                            {{ $reviews->links() }}
                        </div>
                    </section>
                @endif
            </div>
        </div>

        <x-reviews.report-modal />

        <div wire:ignore>
            <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$reviewable->id" :model-type="$reviewable->getMorphClass()" :user-rating="$userRating" />
        </div>
    </main>
</x-base-layout>
