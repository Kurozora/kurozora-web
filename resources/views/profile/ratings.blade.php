<x-base-layout>
    <x-slot:title>
        {{ __(':x’s Ratings & Reviews', ['x' => $user->username]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse :x’s ratings and reviews on :y. Join the :y community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $user->username, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s Ratings & Reviews', ['x' => $user->username]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse :x’s ratings and reviews on :y. Join the :y community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $user->username, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('profile.ratings', $user) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        users/{{ $user->id }}/ratings
    </x-slot:appArgument>

    <main>
        <div class="pt-4 pb-6" data-paginated="ratings">
            <section class="mb-4 xl:safe-area-inset">
                <form method="get" action="{{ route('profile.ratings', $user) }}" data-search-bar>
                    <div class="flex gap-1 pl-4 pr-4">
                        <div class="flex flex-wrap items-center w-full">
                            <h1 class="text-2xl font-bold">{{ __(':x’s Ratings & Reviews', ['x' => $user->username]) }}</h1>
                        </div>

                        <div class="flex flex-1 gap-1 justify-end items-center w-full">
                            <x-spinner :wire-loading-enabled="false" class="hidden" data-loading />

                            <x-search-order-dropdown :criteria="$criteria" />

                            <x-search-filter-dropdown :criteria="$criteria" />
                        </div>
                    </div>

                    <x-search-type-row :criteria="$criteria" />
                </form>
            </section>

            @if ($mediaRatings->count())
                <section class="xl:safe-area-inset">
                    <x-rows.media-rating-lockup :media-ratings="$mediaRatings" :is-row="false" />

                    <div class="mt-4 pl-4 pr-4">
                        {{ $mediaRatings->links() }}
                    </div>
                </section>
            @elseif ($criteria->isFiltering() || $criteria->typeValue !== null)
                <x-empty-state image="empty_anime_library.webp" alt="No reviews" :heading="__('No Reviews')" :description="__('No reviews found with the selected criteria.')" />
            @else
                <x-empty-state image="empty_anime_library.webp" alt="No reviews" :heading="__('No Reviews')" :description="__(':x has not reviewed any titles yet.', ['x' => $user->username])" />
            @endif
        </div>
    </main>
</x-base-layout>
