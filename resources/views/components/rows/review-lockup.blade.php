@props(['reviews' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true, 'voteOverrides' => [], 'reviewBoxId' => null])

@php
    // The rows of a paginated set live on its own collection.
    $allReviews = $reviews instanceof \Illuminate\Contracts\Pagination\CursorPaginator
        || $reviews instanceof \Illuminate\Contracts\Pagination\Paginator
            ? $reviews->getCollection()
            : collect($reviews);

    // The horizontal row shows too few reviews to be worth collapsing.
    $collapsesLowEffort = !$isRow;
    $shownReviews = $collapsesLowEffort ? $allReviews->reject->is_low_effort : $allReviews;
    $lowEffortReviews = $collapsesLowEffort ? $allReviews->filter->is_low_effort : collect();
@endphp

<x-rows.container lockup="review" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($shownReviews as $review)
        <x-lockups.review-lockup :review="$review" :is-row="$isRow" :vote-overrides="$voteOverrides" :review-box-id="$reviewBoxId" />
    @endforeach

    @if ($lowEffortReviews->isNotEmpty())
        <div class="w-full" x-data="{ isExpanded: false }">
            <button
                type="button"
                class="flex items-center gap-1 pl-2 pr-2 pt-1 pb-1 text-sm text-secondary rounded-md bg-secondary"
                x-on:click="isExpanded = !isExpanded"
            >
                <span x-show="!isExpanded">{{ __('Show short reviews') }}</span>
                <span x-show="isExpanded" x-cloak>{{ __('Hide short reviews') }}</span>
            </button>

            <x-rows.container lockup="review" :is-row="false" class="mt-4" x-show="isExpanded" x-cloak>
                @foreach ($lowEffortReviews as $review)
                    <x-lockups.review-lockup :review="$review" :is-row="$isRow" :vote-overrides="$voteOverrides" :review-box-id="$reviewBoxId" />
                @endforeach
            </x-rows.container>
        </div>
    @endif
</x-rows.container>
